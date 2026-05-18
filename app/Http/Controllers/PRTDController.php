<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mcdrnew;
use App\Models\TblMasterac;
use App\Models\TblMonthlyfhfc;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PRTDController extends Controller
{

    public function create()
    {
        return view('report.prtd-content');
    }
    // ── PRIVATE HELPER: Dropdown options ─────────────────────────────────────
    private function getDropdownOptions(): array
    {
        $operators = TblMasterac::select('Operator')
            ->distinct()
            ->whereNotNull('Operator')
            ->orderBy('Operator')
            ->get();

        $aircraftTypes = TblMasterac::select('ACType')
            ->distinct()
            ->whereNotNull('ACType')
            ->orderBy('ACType')
            ->get();

        $periods = Mcdrnew::selectRaw("DATE_FORMAT(DateEvent, '%Y-%m') as original")
            ->distinct()
            ->orderByDesc('original')
            ->get()
            ->map(function ($r) {

                $original = is_array($r)
                    ? $r['original']
                    : $r->original;

                return [
                    'original' => $original,
                    'formatted' => Carbon::createFromFormat('Y-m', (string) $original)
                        ->format('F Y'),
                ];
            });
        return compact('operators', 'aircraftTypes', 'periods');
    }

    // ── PRIVATE CORE: Bulk query + loop kalkulasi (ikuti pola AOS) ────────────
    private function buildPrtdData(string $aircraftType, string $operator, string $period): array
    {
        $date = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $startDate = $date->copy()->subMonths(11)->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // ── BULK QUERY 1: Total events + delay per bulan ──────────────────────
        // Hitung langsung di DB, bukan ambil semua row lalu sum di PHP
        // $delayData = Mcdrnew::where('ACtype', $aircraftType)
        //     ->whereBetween('DateEvent', [$startDate, $endDate])
        //     ->selectRaw("
        //                     DATE_FORMAT(DateEvent, '%Y-%m')   AS period_key,
        //                     COUNT(*)                           AS total_events,
        //                     SUM(HoursTot * 60 + MinTot)       AS total_delay_min
        //                 ")
        //     ->groupByRaw("DATE_FORMAT(DateEvent, '%Y-%m')")
        //     ->get()
        //     ->keyBy('period_key');

        // ── BULK QUERY 2: Reported defect by pilot (Problem not null) ─────────
        // $pirepData = Mcdrnew::where('ACtype', $aircraftType)
        //     ->whereBetween('DateEvent', [$startDate, $endDate])
        //     ->whereNotNull('Problem')
        //     ->where('Problem', '!=', '')
        //     ->selectRaw("
        //                     DATE_FORMAT(DateEvent, '%Y-%m') AS period_key,
        //                     COUNT(*)                         AS pirep_count
        //                 ")
        //     ->groupByRaw("DATE_FORMAT(DateEvent, '%Y-%m')")
        //     ->get()
        //     ->keyBy('period_key');

        // ── BULK QUERY 3: Cause of delay (RootCause not null) ─────────────────
        // $rootCauseData = Mcdrnew::where('ACtype', $aircraftType)
        //     ->whereBetween('DateEvent', [$startDate, $endDate])
        //     ->whereNotNull('RootCause')
        //     ->where('RootCause', '!=', '')
        //     ->selectRaw("
        //                         DATE_FORMAT(DateEvent, '%Y-%m') AS period_key,
        //                         COUNT(*)                         AS root_cause_count
        //                     ")
        //     ->groupByRaw("DATE_FORMAT(DateEvent, '%Y-%m')")
        //     ->get()
        //     ->keyBy('period_key');

        // ── BULK QUERY 4: Maintenance action (Maintenance_Action not null) ─────
        // $maintData = Mcdrnew::where('ACtype', $aircraftType)
        //     ->whereBetween('DateEvent', [$startDate, $endDate])
        //     ->whereNotNull('Maintenance_Action')
        //     ->where('Maintenance_Action', '!=', '')
        //     ->selectRaw("
        //                     DATE_FORMAT(DateEvent, '%Y-%m') AS period_key,
        //                     COUNT(*)                         AS maintenance_action_count
        //                 ")
        //     ->groupByRaw("DATE_FORMAT(DateEvent, '%Y-%m')")
        //     ->get()
        //     ->keyBy('period_key');

        $allData = Mcdrnew::where('ACtype', $aircraftType)
            ->whereBetween('DateEvent', [$startDate, $endDate])
            ->selectRaw("
                    DATE_FORMAT(DateEvent, '%Y-%m')                              AS period_key,
                    COUNT(*)                                                      AS total_events,
                    SUM(HoursTot * 60 + MinTot)                                  AS total_delay_min,
                    SUM(CASE WHEN Problem IS NOT NULL
                                AND Problem != '' THEN 1 ELSE 0 END)                AS pirep_count,
                    SUM(CASE WHEN RootCause IS NOT NULL
                                AND RootCause != '' THEN 1 ELSE 0 END)              AS root_cause_count,
                    SUM(CASE WHEN Maintenance_Action IS NOT NULL
                                AND Maintenance_Action != '' THEN 1 ELSE 0 END)     AS maintenance_action_count
                ")
            ->groupByRaw("DATE_FORMAT(DateEvent, '%Y-%m')")
            ->get()
            ->keyBy('period_key');

        // ── BULK QUERY 5: Flying hours dari TblMonthlyfhfc (untuk rate) ───────
        $fhfcData = TblMonthlyfhfc::where('Actype', $aircraftType)
            ->whereBetween('MonthEval', [$startDate, $endDate])
            ->selectRaw("
                            DATE_FORMAT(MonthEval, '%Y-%m')        AS period_key,
                            SUM(RevFC + NoRevFC)                    AS takeOffTotal,
                            SUM(RevFHHours + (RevFHMin/60)
                                + NoRevFHHours + (NoRevFHMin/60))     AS flyingHoursTotal
                        ")
            ->groupByRaw("DATE_FORMAT(MonthEval, '%Y-%m')")
            ->get()
            ->keyBy('period_key');

        // ── LOOP — kalkulasi saja, tidak ada query di sini ────────────────────
        $months = collect(CarbonPeriod::create($startDate, '1 month', $endDate))
            ->map(fn($d) => Carbon::instance($d));

        $reportData = [];
        $totals = [
            'total_events' => 0,
            'pirep_count' => 0,
            'total_delay_min' => 0,
            'root_cause_count' => 0,
            'maintenance_action_count' => 0,
            'takeOffTotal' => 0,
        ];

        foreach ($months as $m) {
            $key = $m->format('Y-m');

            // Ambil dari collection — 0 jika bulan kosong
            $rowMetrics = $allData[$key] ?? null;
            $fhfc = $fhfcData[$key] ?? null;

            $totalEvents = (int) ($rowMetrics?->total_events ?? 0);
            $totalDelayMin = (float) ($rowMetrics?->total_delay_min ?? 0);
            $pirepCount = (int) ($rowMetrics?->pirep_count ?? 0);
            $rootCauseCount = (int) ($rowMetrics?->root_cause_count ?? 0);
            $maintCount = (int) ($rowMetrics?->maintenance_action_count ?? 0);
            $takeOffTotal = (float) ($fhfc?->takeOffTotal ?? 0);

            $totalDelayHrs = $totalDelayMin / 60;
            $avgDelayMin = $totalEvents > 0 ? round($totalDelayMin / $totalEvents, 2) : 0;

            // Rate per 100 take off (bonus metric seperti AOS)
            $delayRatePer100 = $takeOffTotal > 0
                ? round(($totalEvents / $takeOffTotal) * 100, 2)
                : 0;

            $reportData[$key] = [
                'total_events' => $totalEvents,
                'pirep_count' => $pirepCount,
                'total_delay_hours' => $totalDelayHrs,
                'avg_delay_min' => $avgDelayMin,
                'root_cause_count' => $rootCauseCount,
                'maintenance_action_count' => $maintCount,
                'delay_rate_per100' => $delayRatePer100,
            ];

            // Akumulasi Last 12 MTHS
            $totals['total_events'] += $totalEvents;
            $totals['pirep_count'] += $pirepCount;
            $totals['total_delay_min'] += $totalDelayMin;
            $totals['root_cause_count'] += $rootCauseCount;
            $totals['maintenance_action_count'] += $maintCount;
            $totals['takeOffTotal'] += $takeOffTotal;
        }

        // ── Summary Last 12 MTHS ──────────────────────────────────────────────
        $totalRecords = $totals['total_events'];
        $totalDelayHrs = $totals['total_delay_min'] / 60;
        $avgDelayMin = $totalRecords > 0
            ? round($totals['total_delay_min'] / $totalRecords, 2)
            : 0;
        $delayRate12 = $totals['takeOffTotal'] > 0
            ? round(($totalRecords / $totals['takeOffTotal']) * 100, 2)
            : 0;

        // ── Rows definition ───────────────────────────────────────────────────
        $rows = [
            [
                'label' => 'Total PRTD Events (Flight No.)',
                'field' => 'total_events',
                'format' => 'round',
                'summary' => $totalRecords,
            ],
            [
                'label' => 'Reported Defect by Pilot',
                'field' => 'pirep_count',
                'format' => 'round',
                'summary' => $totals['pirep_count'],
            ],
            [
                'label' => 'Total Delay Time (HH:MM)',
                'field' => 'total_delay_hours',
                'format' => 'hhmm',
                'summary' => $totalDelayHrs,
            ],
            [
                'label' => 'Average Delay per Event (min)',
                'field' => 'avg_delay_min',
                'format' => 'decimal2',
                'summary' => $avgDelayMin,
            ],
            [
                'label' => 'Events with Cause of Delay',
                'field' => 'root_cause_count',
                'format' => 'round',
                'summary' => $totals['root_cause_count'],
            ],
            [
                'label' => 'Events with Maintenance Action',
                'field' => 'maintenance_action_count',
                'format' => 'round',
                'summary' => $totals['maintenance_action_count'],
            ],
            [
                'label' => 'Delay Rate / 100 Take Off',
                'field' => 'delay_rate_per100',
                'format' => 'decimal2',
                'summary' => $delayRate12,
            ],
        ];

        return compact('reportData', 'rows', 'months');
    }

    // ── INDEX: form kosong ────────────────────────────────────────────────────
    public function index()
    {
        return view('report.prtd-content', $this->getDropdownOptions());
    }

    // ── AJAX: ACType by operator ──────────────────────────────────────────────
    public function getAircraftTypes(Request $request)
    {
        $aircraftTypes = TblMasterac::where('Operator', $request->operator)
            ->select('ACType')
            ->distinct()
            ->whereNotNull('ACType')
            ->where('ACType', '!=', '')
            ->orderBy('ACType')
            ->get();

        return response()->json($aircraftTypes);
    }

    // ── STORE: proses filter → tampilkan tabel ────────────────────────────────
    public function prtdStore(Request $request)
    {
        $request->validate([
            'period' => 'required',
            'operator' => 'required',
            'aircraft_type' => 'required',
        ]);

        $data = $this->buildPrtdData(
            $request->aircraft_type,
            $request->operator,
            $request->period
        );

        $date = Carbon::createFromFormat('Y-m', $request->period);

        return view('report.prtd-content', array_merge(
            $this->getDropdownOptions(),
            [
                'reportData' => $data['reportData'],
                'rows' => $data['rows'],
                'months' => $data['months'],
                'period' => $request->period,
                'operator' => $request->operator,
                'aircraftType' => $request->aircraft_type,
                'month' => $date->format('m'),
                'year' => $date->format('Y'),
            ]
        ));
    }
}