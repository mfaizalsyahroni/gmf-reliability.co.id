<?php

namespace App\Http\Controllers;

use App\Models\TblMasterac;
use App\Models\TblMonthlyfhfc;
use App\Models\Mcdrnew;
use App\Models\TblSdr;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AOSController extends Controller
{

    public function create()
    {
        return view('report.aos-content');
    }

    // HELPER: Decimal → HH:MM
    public function convertDecimalToHoursMinutes($decimalHours): string
    {
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);
        return sprintf('%d : %02d', $hours, $minutes);
    }

    // PRIVATE CORE: 5 bulk query + kalkulasi loop
    // Dipakai oleh aosStore, getAosReportData, getAosRawData
    private function buildAosData(string $aircraftType, string $operator, string $period): array
    {
        $date = Carbon::parse($period);
        $startDate = $date->copy()->subMonths(11)->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // QUERY 1: AC In Fleet — nilai tetap sama tiap bulan, cukup query sekali
        $acInFleet = TblMasterac::where('Active', '1')
            ->where('ACType', $aircraftType)
            ->where('Operator', $operator)
            ->count();

        // QUERY 2: FHFC — semua kolom agregat 12 bulan sekaligus, group by bulan
        $fhfcData = TblMonthlyfhfc::where('Actype', $aircraftType)
            ->whereBetween('MonthEval', [$startDate, $endDate])
            ->selectRaw("
                DATE_FORMAT(MonthEval, '%Y-%m')                                   AS period_key,
                SUM(AvaiDays)                                                      AS daysInService,
                SUM(RevFHHours + (RevFHMin/60) + NoRevFHHours + (NoRevFHMin/60))  AS flyingHoursTotal,
                SUM(RevFHHours + (RevFHMin/60))                                    AS revenueFlyingHours,
                SUM(RevFC + NoRevFC)                                               AS takeOffTotal,
                SUM(RevFC)                                                         AS revenueTakeOff
            ")
            ->groupByRaw("DATE_FORMAT(MonthEval, '%Y-%m')")
            ->get()
            ->keyBy('period_key');

        // QUERY 3: MCDR filter DCP = 'D' — Technical Delay + Total Duration per bulan
        $mcdrDelayData = Mcdrnew::where('ACType', $aircraftType)
            ->whereBetween('DateEvent', [$startDate, $endDate])
            ->where('DCP', 'LIKE', '%D%')
            ->selectRaw("
                DATE_FORMAT(DateEvent, '%Y-%m') AS period_key,
                COUNT(*)                         AS technicalDelayTotal,
                SUM(HoursTek + (MinTek/60))      AS totalDuration
            ")
            ->groupByRaw("DATE_FORMAT(DateEvent, '%Y-%m')")
            ->get()
            ->keyBy('period_key');

        // QUERY 4: MCDR filter DCP = 'C' — Technical Cancellation per bulan
        $mcdrCancelData = Mcdrnew::where('ACType', $aircraftType)
            ->whereBetween('DateEvent', [$startDate, $endDate])
            ->where('DCP', 'LIKE', '%C%')
            ->selectRaw("
                DATE_FORMAT(DateEvent, '%Y-%m') AS period_key,
                COUNT(*)                         AS technicalCancellationTotal
            ")
            ->groupByRaw("DATE_FORMAT(DateEvent, '%Y-%m')")
            ->get()
            ->keyBy('period_key');

        // QUERY 5: SDR — Technical Incident per bulan
        $sdrData = TblSdr::where('ACType', $aircraftType)
            ->whereBetween('DateOccur', [$startDate, $endDate])
            ->selectRaw("
                DATE_FORMAT(DateOccur, '%Y-%m') AS period_key,
                COUNT(*)                         AS technicalIncidentTotal
            ")
            ->groupByRaw("DATE_FORMAT(DateOccur, '%Y-%m')")
            ->get()
            ->keyBy('period_key');

        // Inisialisasi output dan akumulator
        $reportData = []; // formatted HH:MM — untuk view & PDF
        $rawMonthly = []; // decimal mentah — untuk Excel

        $totals = [
            'acInServiceSum' => 0,
            'daysInServiceSum' => 0,
            'flyingHoursTotalSum' => 0,
            'revenueFlyingHoursSum' => 0,
            'takeOffTotalSum' => 0,
            'revenueTakeOffSum' => 0,
            'flightHoursPerTakeOffTotal' => 0,
            'revenueFlightHoursPerTakeOff' => 0,
            'dailyUtilizationFlyingHoursTotal' => 0,
            'revenueDailyUtilizationFlyingHoursTotal' => 0,
            'dailyUtilizationTakeOffTotal' => 0,
            'revenueDailyUtilizationTakeOffTotal' => 0,
            'technicalDelayTotal' => 0,
            'totalDuration' => 0,
            'ratePer100TakeOff' => 0,
            'technicalIncidentTotal' => 0,
            'technicalIncidentRate' => 0,
            'technicalCancellationTotal' => 0,
            'dispatchReliability' => 0,
        ];

        // LOOP — kalkulasi saja, tidak ada query di sini
        for ($i = 11; $i >= 0; $i--) {
            $current = $date->copy()->subMonths($i);
            $key = $current->format('Y-m');

            // Ambil dari collection, null jika bulan kosong
            $fhfc = $fhfcData[$key] ?? null;
            $delay = $mcdrDelayData[$key] ?? null;
            $cancel = $mcdrCancelData[$key] ?? null;
            $sdr = $sdrData[$key] ?? null;

            // Ekstrak nilai mentah dengan cast tipe eksplisit
            $daysInService = (float) ($fhfc?->daysInService ?? 0);
            $flyingHoursTotal = (float) ($fhfc?->flyingHoursTotal ?? 0);
            $revenueFlyingHours = (float) ($fhfc?->revenueFlyingHours ?? 0);
            $takeOffTotal = (float) ($fhfc?->takeOffTotal ?? 0);
            $revenueTakeOff = (float) ($fhfc?->revenueTakeOff ?? 0);
            $technicalDelayTotal = (int) ($delay?->technicalDelayTotal ?? 0);
            $totalDuration = (float) ($delay?->totalDuration ?? 0);
            $technicalCancellationTotal = (int) ($cancel?->technicalCancellationTotal ?? 0);
            $technicalIncidentTotal = (int) ($sdr?->technicalIncidentTotal ?? 0);

            // Gunakan Carbon daysInMonth, bukan cal_days_in_month()
            $daysInMonth = $current->daysInMonth;

            // Kalkulasi turunan
            $acInService = $daysInMonth > 0 ? $daysInService / $daysInMonth : 0;
            $flightHoursPerTakeOffTotal = $takeOffTotal > 0 ? $flyingHoursTotal / $takeOffTotal : 0;
            $revenueFlightHoursPerTakeOff = $revenueTakeOff > 0 ? $revenueFlyingHours / $revenueTakeOff : 0;
            $dailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $flyingHoursTotal / $daysInService : 0;
            $revenueDailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $revenueFlyingHours / $daysInService : 0;
            $dailyUtilizationTakeOffTotal = $daysInService > 0 ? $takeOffTotal / $daysInService : 0;
            $revenueDailyUtilizationTakeOffTotal = $daysInService > 0 ? $revenueTakeOff / $daysInService : 0;
            $averageDuration = $technicalDelayTotal > 0 ? $totalDuration / $technicalDelayTotal : 0;
            $ratePer100TakeOff = $revenueTakeOff > 0 ? ($technicalDelayTotal * 100) / $revenueTakeOff : 0;
            $technicalIncidentRate = $revenueTakeOff > 0 ? ($technicalIncidentTotal * 100) / $revenueTakeOff : 0;
            $dispatchReliability = $revenueTakeOff > 0
                ? (($revenueTakeOff - $technicalDelayTotal - $technicalCancellationTotal) / $revenueTakeOff) * 100
                : 0;

            // Simpan formatted (HH:MM) untuk view & PDF
            $reportData[$key] = [
                'acInFleet' => $acInFleet,
                'acInService' => $acInService,
                'daysInService' => $daysInService,
                'flyingHoursTotal' => $flyingHoursTotal,
                'revenueFlyingHours' => $revenueFlyingHours,
                'takeOffTotal' => $takeOffTotal,
                'revenueTakeOff' => $revenueTakeOff,
                'flightHoursPerTakeOffTotal' => $this->convertDecimalToHoursMinutes($flightHoursPerTakeOffTotal),
                'revenueFlightHoursPerTakeOff' => $this->convertDecimalToHoursMinutes($revenueFlightHoursPerTakeOff),
                'dailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($dailyUtilizationFlyingHoursTotal),
                'revenueDailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($revenueDailyUtilizationFlyingHoursTotal),
                'dailyUtilizationTakeOffTotal' => $dailyUtilizationTakeOffTotal,
                'revenueDailyUtilizationTakeOffTotal' => $revenueDailyUtilizationTakeOffTotal,
                'technicalDelayTotal' => $technicalDelayTotal,
                'totalDuration' => $this->convertDecimalToHoursMinutes($totalDuration),
                'averageDuration' => $this->convertDecimalToHoursMinutes($averageDuration),
                'ratePer100TakeOff' => $ratePer100TakeOff,
                'technicalIncidentTotal' => $technicalIncidentTotal,
                'technicalIncidentRate' => $technicalIncidentRate,
                'technicalCancellationTotal' => $technicalCancellationTotal,
                'dispatchReliability' => $dispatchReliability,
            ];

            // Simpan decimal mentah untuk Excel
            $rawMonthly[$key] = [
                'acInFleet' => $acInFleet,
                'acInService' => $acInService,
                'daysInService' => $daysInService,
                'flyingHoursTotal' => $flyingHoursTotal,
                'revenueFlyingHours' => $revenueFlyingHours,
                'takeOffTotal' => $takeOffTotal,
                'revenueTakeOff' => $revenueTakeOff,
                'flightHoursPerTakeOffTotal' => $flightHoursPerTakeOffTotal,
                'revenueFlightHoursPerTakeOff' => $revenueFlightHoursPerTakeOff,
                'dailyUtilizationFlyingHoursTotal' => $dailyUtilizationFlyingHoursTotal,
                'revenueDailyUtilizationFlyingHoursTotal' => $revenueDailyUtilizationFlyingHoursTotal,
                'dailyUtilizationTakeOffTotal' => $dailyUtilizationTakeOffTotal,
                'revenueDailyUtilizationTakeOffTotal' => $revenueDailyUtilizationTakeOffTotal,
                'technicalDelayTotal' => $technicalDelayTotal,
                'totalDuration' => $totalDuration,
                'averageDuration' => $averageDuration,
                'ratePer100TakeOff' => $ratePer100TakeOff,
                'technicalIncidentTotal' => $technicalIncidentTotal,
                'technicalIncidentRate' => $technicalIncidentRate,
                'technicalCancellationTotal' => $technicalCancellationTotal,
                'dispatchReliability' => $dispatchReliability,
            ];

            // Akumulasi untuk Last 12 Months
            $totals['acInServiceSum'] += $acInService;
            $totals['daysInServiceSum'] += $daysInService;
            $totals['flyingHoursTotalSum'] += $flyingHoursTotal;
            $totals['revenueFlyingHoursSum'] += $revenueFlyingHours;
            $totals['takeOffTotalSum'] += $takeOffTotal;
            $totals['revenueTakeOffSum'] += $revenueTakeOff;
            $totals['flightHoursPerTakeOffTotal'] += $flightHoursPerTakeOffTotal;
            $totals['revenueFlightHoursPerTakeOff'] += $revenueFlightHoursPerTakeOff;
            $totals['dailyUtilizationFlyingHoursTotal'] += $dailyUtilizationFlyingHoursTotal;
            $totals['revenueDailyUtilizationFlyingHoursTotal'] += $revenueDailyUtilizationFlyingHoursTotal;
            $totals['dailyUtilizationTakeOffTotal'] += $dailyUtilizationTakeOffTotal;
            $totals['revenueDailyUtilizationTakeOffTotal'] += $revenueDailyUtilizationTakeOffTotal;
            $totals['technicalDelayTotal'] += $technicalDelayTotal;
            $totals['totalDuration'] += $totalDuration;
            $totals['ratePer100TakeOff'] += $ratePer100TakeOff;
            $totals['technicalIncidentTotal'] += $technicalIncidentTotal;
            $totals['technicalIncidentRate'] += $technicalIncidentRate;
            $totals['technicalCancellationTotal'] += $technicalCancellationTotal;
            $totals['dispatchReliability'] += $dispatchReliability;
        }

        // Average 12 bulan — formatted HH:MM
        $avgs = [
            'avgFlightHoursPerTakeOffTotal' => $this->convertDecimalToHoursMinutes($totals['flightHoursPerTakeOffTotal'] / 12),
            'avgRevenueFlightHoursPerTakeOff' => $this->convertDecimalToHoursMinutes($totals['revenueFlightHoursPerTakeOff'] / 12),
            'avgDailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($totals['dailyUtilizationFlyingHoursTotal'] / 12),
            'avgRevenueDailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($totals['revenueDailyUtilizationFlyingHoursTotal'] / 12),
            'avgTotalDuration' => $this->convertDecimalToHoursMinutes($totals['totalDuration'] / 12),
            'avgAverageDuration' => $totals['technicalDelayTotal'] > 0
                ? $this->convertDecimalToHoursMinutes($totals['totalDuration'] / $totals['technicalDelayTotal'])
                : '0 : 00',
        ];

        // Last 12 Months summary — decimal untuk Excel
        $last12 = [
            'acInFleet' => $acInFleet,
            'acInService' => $totals['acInServiceSum'] / 12,
            'daysInService' => $totals['daysInServiceSum'],
            'flyingHoursTotal' => $totals['flyingHoursTotalSum'],
            'revenueFlyingHours' => $totals['revenueFlyingHoursSum'],
            'takeOffTotal' => $totals['takeOffTotalSum'],
            'revenueTakeOff' => $totals['revenueTakeOffSum'],
            'flightHoursPerTakeOffTotal' => $totals['flightHoursPerTakeOffTotal'] / 12,
            'revenueFlightHoursPerTakeOff' => $totals['revenueFlightHoursPerTakeOff'] / 12,
            'dailyUtilizationFlyingHoursTotal' => $totals['dailyUtilizationFlyingHoursTotal'] / 12,
            'revenueDailyUtilizationFlyingHoursTotal' => $totals['revenueDailyUtilizationFlyingHoursTotal'] / 12,
            'dailyUtilizationTakeOffTotal' => $totals['dailyUtilizationTakeOffTotal'] / 12,
            'revenueDailyUtilizationTakeOffTotal' => $totals['revenueDailyUtilizationTakeOffTotal'] / 12,
            'technicalDelayTotal' => $totals['technicalDelayTotal'],
            'totalDuration' => $totals['totalDuration'],
            'averageDuration' => $totals['technicalDelayTotal'] > 0
                ? $totals['totalDuration'] / $totals['technicalDelayTotal'] : 0,
            'ratePer100TakeOff' => $totals['ratePer100TakeOff'] / 12,
            'technicalIncidentTotal' => $totals['technicalIncidentTotal'] / 12,
            'technicalIncidentRate' => $totals['technicalIncidentRate'] / 12,
            'technicalCancellationTotal' => $totals['technicalCancellationTotal'],
            'dispatchReliability' => $totals['dispatchReliability'] / 12,
        ];

        $months = collect(range(11, 0))->map(function ($i) use ($date) {
            return $date->copy()->subMonths($i);
        });

        $rows = [

            [
                'label' => 'A/C In Fleet',
                'field' => 'acInFleet',
                'format' => 'number',
                'summary' => $acInFleet,
            ],

            [
                'label' => 'A/C In Service',
                'field' => 'acInService',
                'format' => 'decimal2',
                'summary' => $last12['acInService'],
            ],

            [
                'label' => 'A/C Days In Service',
                'field' => 'daysInService',
                'format' => 'round',
                'summary' => $last12['daysInService'],
            ],

            [
                'label' => 'Flying Hours - Total',
                'field' => 'flyingHoursTotal',
                'format' => 'round',
                'summary' => $last12['flyingHoursTotal'],
            ],

            [
                'label' => 'Revenue Flying Hours',
                'field' => 'revenueFlyingHours',
                'format' => 'round',
                'summary' => $last12['revenueFlyingHours'],
            ],

            [
                'label' => 'Take Off - Total',
                'field' => 'takeOffTotal',
                'format' => 'round',
                'summary' => $last12['takeOffTotal'],
            ],

            [
                'label' => 'Revenue Take Off',
                'field' => 'revenueTakeOff',
                'format' => 'round',
                'summary' => $last12['revenueTakeOff'],
            ],

            [
                'label' => 'Flight Hours per Take Off - Total',
                'field' => 'flightHoursPerTakeOffTotal',
                'format' => 'string',
                'summary' => $avgs['avgFlightHoursPerTakeOffTotal'],
            ],

            [
                'label' => 'Revenue Flight Hours per Take Off',
                'field' => 'revenueFlightHoursPerTakeOff',
                'format' => 'string',
                'summary' => $avgs['avgRevenueFlightHoursPerTakeOff'],
            ],

            [
                'label' => 'Daily Utilization - Flying Hours Total',
                'field' => 'dailyUtilizationFlyingHoursTotal',
                'format' => 'string',
                'summary' => $avgs['avgDailyUtilizationFlyingHoursTotal'],
            ],

            [
                'label' => 'Revenue Daily Utilization - Flying Hours Total',
                'field' => 'revenueDailyUtilizationFlyingHoursTotal',
                'format' => 'string',
                'summary' => $avgs['avgRevenueDailyUtilizationFlyingHoursTotal'],
            ],

            [
                'label' => 'Daily Utilization - Take Off Total',
                'field' => 'dailyUtilizationTakeOffTotal',
                'format' => 'decimal2',
                'summary' => $last12['dailyUtilizationTakeOffTotal'],
            ],

            [
                'label' => 'Revenue Daily Utilization - Take Off Total',
                'field' => 'revenueDailyUtilizationTakeOffTotal',
                'format' => 'decimal2',
                'summary' => $last12['revenueDailyUtilizationTakeOffTotal'],
            ],

            [
                'label' => 'Technical Delay - Total',
                'field' => 'technicalDelayTotal',
                'format' => 'round',
                'summary' => $last12['technicalDelayTotal'],
            ],

            [
                'label' => 'Total Duration',
                'field' => 'totalDuration',
                'format' => 'string',
                'summary' => $avgs['avgTotalDuration'],
            ],

            [
                'label' => 'Average Duration',
                'field' => 'averageDuration',
                'format' => 'string',
                'summary' => $avgs['avgAverageDuration'],
            ],

            [
                'label' => 'Rate / 100 Take Off',
                'field' => 'ratePer100TakeOff',
                'format' => 'decimal2',
                'summary' => $last12['ratePer100TakeOff'],
            ],

            [
                'label' => 'Technical Incident - Total',
                'field' => 'technicalIncidentTotal',
                'format' => 'round',
                'summary' => $last12['technicalIncidentTotal'],
            ],

            [
                'label' => 'Technical Incident Rate / 100 FC',
                'field' => 'technicalIncidentRate',
                'format' => 'decimal3',
                'summary' => $last12['technicalIncidentRate'],
            ],

            [
                'label' => 'Technical Cancellation - Total',
                'field' => 'technicalCancellationTotal',
                'format' => 'round',
                'summary' => $last12['technicalCancellationTotal'],
            ],

            [
                'label' => 'Dispatch Reliability (%)',
                'field' => 'dispatchReliability',
                'format' => 'percent',
                'summary' => $last12['dispatchReliability'],
            ],
        ];

        return compact('reportData', 'rawMonthly', 'avgs', 'last12', 'acInFleet', 'rows', 'months');
    }

    // HELPER PRIVATE: Dropdown options (operator, ACType, period)
    private function getDropdownOptions(): array
    {
        $operators = TblMasterac::select('Operator')->distinct()->get();

        $aircraftTypes = TblMasterac::select('ACType')->distinct()->get();

        $periods = TblMonthlyfhfc::select('MonthEval')
            ->distinct()
            ->orderByDesc('MonthEval')
            ->get()
            ->map(fn($item) => [
                'formatted' => Carbon::parse($item->MonthEval)->format('Y-m'),
                'original' => $item->MonthEval,
            ]);


        return compact('operators', 'aircraftTypes', 'periods');
    }

    // INDEX — tampilkan form kosong dengan dropdown
    public function aosIndex(Request $request)
    {
        return view('report.aos-content', $this->getDropdownOptions());
    }

    // AJAX — ambil ACType berdasarkan operator
    public function getAircraftTypes(Request $request)
    {
        $aircraftTypes = TblMasterac::where('Operator', $request->operator)
            ->select('ACType')
            ->distinct()
            ->get();

        return response()->json($aircraftTypes);
    }

    // STORE — proses form, tampilkan tabel di view
    public function aosStore(Request $request)
    {
        $request->validate([
            'period' => 'required',
            'operator' => 'required',
            'aircraft_type' => 'required',
        ]);

        $data = $this->buildAosData(
            $request->aircraft_type,
            $request->operator,
            $request->period
        );

        $date = Carbon::parse($request->period);
        $month = $date->format('m');
        $year = $date->format('Y');

        return view('report.aos-content', array_merge(
            $this->getDropdownOptions(),
            $data['avgs'],
            [
                'reportData' => $data['reportData'],
                'rows' => $data['rows'],
                'months' => $data['months'],
                'period' => $request->period,
                'operator' => $request->operator,
                'aircraftType' => $request->aircraft_type,
                'month' => $month,
                'year' => $year,
            ]
        ));
    }

    // EXPORT PDF — kembalikan array data ke PDF controller
    public function getAosReportData(Request $request): array
    {
        $request->validate([
            'period' => 'required',
            'operator' => 'required',
            'aircraft_type' => 'required',
        ]);

        $data = $this->buildAosData(
            $request->aircraft_type,
            $request->operator,
            $request->period
        );

        return array_merge($data['avgs'], [
            'reportData' => $data['reportData'],
            'period' => $request->period,
            'operator' => $request->operator,
            'aircraftType' => $request->aircraft_type,
            'selectedMonth' => Carbon::parse($request->period)->format('m'),
            'selectedYear' => Carbon::parse($request->period)->format('Y'),
        ]);
    }

    // EXPORT EXCEL — kembalikan raw decimal array ke Excel controller
    public function getAosRawData(Request $request): array
    {
        $request->validate([
            'period' => 'required',
            'operator' => 'required',
            'aircraft_type' => 'required',
        ]);

        $data = $this->buildAosData(
            $request->aircraft_type,
            $request->operator,
            $request->period
        );

        return [
            'period' => $request->period,
            'operator' => $request->operator,
            'aircraftType' => $request->aircraft_type,
            'rawMonthly' => $data['rawMonthly'],
            'last12' => $data['last12'],
        ];
    }
}