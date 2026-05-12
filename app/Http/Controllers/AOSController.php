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
    // ─────────────────────────────────────────────
    // Helper Decimal → HH : MM
    // ─────────────────────────────────────────────
    private function convertDecimalToHoursMinutes($decimalHours): string
    {
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);

        return sprintf('%d : %02d', $hours, $minutes);
    }
    // ─────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────
    public function aosIndex(Request $request)
    {
        $operators = TblMasterac::select('Operator')
            ->distinct()
            ->get();

        $aircraftTypes = TblMasterac::select('ACType')
            ->distinct()
            ->get();

        $periods = TblMonthlyfhfc::select('MonthEval')
            ->distinct()
            ->orderByDesc('MonthEval')
            ->get()
            ->map(function ($item) {
                return [
                    'formatted' => Carbon::parse($item->MonthEval)->format('Y-m'),
                    'original' => $item->MonthEval
                ];
            });

        return view('report.aos-content', compact(
            'operators',
            'aircraftTypes',
            'periods'
        ));
    }

    // ─────────────────────────────────────────────
    // GET AIRCRAFT TYPE & Operator
    // ─────────────────────────────────────────────
    public function getAircraftTypes(Request $request)
    {
        $operator = $request->operator;

        $aircraftTypes = TblMasterac::where('Operator', $operator)
            ->select('ACType')
            ->distinct()
            ->get();

        return response()->json($aircraftTypes);
    }

    // ─────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────
    public function aosStore(Request $request)
    {
        $request->validate([
            'period' => 'required',
            'operator' => 'required',
            'aircraft_type' => 'required',
        ]);

        $aircraftType = $request->aircraft_type;
        $period = $request->period;
        $operator = $request->operator;

        $reportData = [];

        // TOTAL
        $totalAcInFleet = 0;
        $totalAcInService = 0;
        $totalDaysInService = 0;
        $totalFlyingHoursTotal = 0;
        $totalRevenueFlyingHours = 0;
        $totalTakeOffTotal = 0;
        $totalRevenueTakeOff = 0;
        $totalFlightHoursPerTakeOffTotal = 0;
        $totalRevenueFlightHoursPerTakeOff = 0;
        $totalDailyUtilizationFlyingHoursTotal = 0;
        $totalRevenueDailyUtilizationFlyingHoursTotal = 0;
        $totalDailyUtilizationTakeOffTotal = 0;
        $totalRevenueDailyUtilizationTakeOffTotal = 0;
        $totalTechnicalDelayTotal = 0;
        $totalTotalDuration = 0;
        $totalRatePer100TakeOff = 0;
        $totalTechnicalIncidentTotal = 0;
        $totalTechnicalIncidentRate = 0;
        $totalTechnicalCancellationTotal = 0;
        $totalDispatchReliability = 0;
        $totalTechnicalDelayCount = 0;

        for ($i = 11; $i >= 0; $i--) {

            $currentPeriod = Carbon::parse($period)
                ->subMonth($i)
                ->format('Y-m');

            $month = date('m', strtotime($currentPeriod));
            $year = date('Y', strtotime($currentPeriod));

            // ─────────────────────
            // AC IN FLEET
            // ─────────────────────
            $acInFleet = TblMasterac::where('Active', '1')
                ->where('ACType', $aircraftType)
                ->where('Operator', $operator)
                ->count();

            // ─────────────────────
            // DAYS IN SERVICE
            // tbl_monthlyfhfc TIDAK ADA Operator
            // ─────────────────────
            $daysInService = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->sum('AvaiDays');

            $daysInMonth = cal_days_in_month(
                CAL_GREGORIAN,
                $month,
                $year
            );

            $acInService = $daysInMonth > 0
                ? $daysInService / $daysInMonth
                : 0;

            // ─────────────────────
            // FLYING HOURS TOTAL
            // ─────────────────────
            $flyingHoursTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->selectRaw('
                    SUM(
                        RevFHHours +
                        (RevFHMin / 60) +
                        NoRevFHHours +
                        (NoRevFHMin / 60)
                    ) as total
                ')
                ->first()
                ->total ?? 0;

            // ─────────────────────
            // REVENUE FLYING HOURS
            // ─────────────────────
            $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->selectRaw('
                    SUM(
                        RevFHHours +
                        (RevFHMin / 60)
                    ) as revenue
                ')
                ->first()
                ->revenue ?? 0;

            // ─────────────────────
            // TAKE OFF TOTAL
            // ─────────────────────
            $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFC + NoRevFC) as total')
                ->first()
                ->total ?? 0;

            // ─────────────────────
            // REVENUE TAKE OFF
            // ─────────────────────
            $revenueTakeOff = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->sum('RevFC');

            // ─────────────────────
            // CALCULATION
            // ─────────────────────
            $flightHoursPerTakeOffTotal = $takeOffTotal > 0
                ? $flyingHoursTotal / $takeOffTotal
                : 0;

            $revenueFlightHoursPerTakeOff = $revenueTakeOff > 0
                ? $revenueFlyingHours / $revenueTakeOff
                : 0;

            $dailyUtilizationFlyingHoursTotal = $daysInService > 0
                ? $flyingHoursTotal / $daysInService
                : 0;

            $revenueDailyUtilizationFlyingHoursTotal = $daysInService > 0
                ? $revenueFlyingHours / $daysInService
                : 0;

            $dailyUtilizationTakeOffTotal = $daysInService > 0
                ? $takeOffTotal / $daysInService
                : 0;

            $revenueDailyUtilizationTakeOffTotal = $daysInService > 0
                ? $revenueTakeOff / $daysInService
                : 0;

            // ─────────────────────
            // TECHNICAL DELAY
            // HAPUS Operator jika tidak ada
            // ─────────────────────
            $technicalDelayTotal = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', $month)
                ->whereYear('DateEvent', $year)
                ->where('DCP', 'LIKE', '%D%')
                ->count();

            // ─────────────────────
            // TOTAL DURATION
            // ─────────────────────
            $totalDuration = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', $month)
                ->whereYear('DateEvent', $year)
                ->where('DCP', 'LIKE', '%D%')
                ->selectRaw('
                    SUM(
                        HoursTek +
                        (MinTek / 60)
                    ) as total_duration
                ')
                ->first()
                ->total_duration ?? 0;

            $averageDuration = $technicalDelayTotal > 0
                ? $totalDuration / $technicalDelayTotal
                : 0;

            $ratePer100TakeOff = $revenueTakeOff > 0
                ? ($technicalDelayTotal * 100) / $revenueTakeOff
                : 0;

            // ─────────────────────
            // TECHNICAL INCIDENT
            // ─────────────────────
            $technicalIncidentTotal = TblSdr::where('ACType', $aircraftType)
                ->whereMonth('DateOccur', $month)
                ->whereYear('DateOccur', $year)
                ->count();

            $technicalIncidentRate = $revenueTakeOff > 0
                ? ($technicalIncidentTotal * 100) / $revenueTakeOff
                : 0;

            // ─────────────────────
            // TECHNICAL CANCELLATION
            // ─────────────────────
            $technicalCancellationTotal = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', $month)
                ->whereYear('DateEvent', $year)
                ->where('DCP', 'LIKE', '%C%')
                ->count();

            // ─────────────────────
            // DISPATCH RELIABILITY
            // ─────────────────────
            $dispatchReliability = $revenueTakeOff > 0
                ? (
                    (
                        $revenueTakeOff -
                        $technicalDelayTotal -
                        $technicalCancellationTotal
                    ) / $revenueTakeOff
                ) * 100
                : 0;

            // ─────────────────────
            // REPORT DATA
            // ─────────────────────
            $reportData[$currentPeriod] = [

                'acInFleet' => $acInFleet,
                'acInService' => $acInService,
                'daysInService' => $daysInService,

                'flyingHoursTotal' => $flyingHoursTotal,
                'revenueFlyingHours' => $revenueFlyingHours,

                'takeOffTotal' => $takeOffTotal,
                'revenueTakeOff' => $revenueTakeOff,

                'flightHoursPerTakeOffTotal' =>
                    $this->convertDecimalToHoursMinutes(
                        $flightHoursPerTakeOffTotal
                    ),

                'revenueFlightHoursPerTakeOff' =>
                    $this->convertDecimalToHoursMinutes(
                        $revenueFlightHoursPerTakeOff
                    ),

                'dailyUtilizationFlyingHoursTotal' =>
                    $this->convertDecimalToHoursMinutes(
                        $dailyUtilizationFlyingHoursTotal
                    ),

                'revenueDailyUtilizationFlyingHoursTotal' =>
                    $this->convertDecimalToHoursMinutes(
                        $revenueDailyUtilizationFlyingHoursTotal
                    ),

                'dailyUtilizationTakeOffTotal' =>
                    $dailyUtilizationTakeOffTotal,

                'revenueDailyUtilizationTakeOffTotal' =>
                    $revenueDailyUtilizationTakeOffTotal,

                'technicalDelayTotal' =>
                    $technicalDelayTotal,

                'totalDuration' =>
                    $this->convertDecimalToHoursMinutes(
                        $totalDuration
                    ),

                'averageDuration' =>
                    $this->convertDecimalToHoursMinutes(
                        $averageDuration
                    ),

                'ratePer100TakeOff' =>
                    $ratePer100TakeOff,

                'technicalIncidentTotal' =>
                    $technicalIncidentTotal,

                'technicalIncidentRate' =>
                    $technicalIncidentRate,

                'technicalCancellationTotal' =>
                    $technicalCancellationTotal,

                'dispatchReliability' =>
                    $dispatchReliability,
            ];

            // ─────────────────────
            // ACCUMULATION
            // ─────────────────────
            $totalAcInFleet += $acInFleet;
            $totalAcInService += $acInService;
            $totalDaysInService += $daysInService;
            $totalFlyingHoursTotal += $flyingHoursTotal;
            $totalRevenueFlyingHours += $revenueFlyingHours;
            $totalTakeOffTotal += $takeOffTotal;
            $totalRevenueTakeOff += $revenueTakeOff;
            $totalFlightHoursPerTakeOffTotal += $flightHoursPerTakeOffTotal;
            $totalRevenueFlightHoursPerTakeOff += $revenueFlightHoursPerTakeOff;
            $totalDailyUtilizationFlyingHoursTotal += $dailyUtilizationFlyingHoursTotal;
            $totalRevenueDailyUtilizationFlyingHoursTotal += $revenueDailyUtilizationFlyingHoursTotal;
            $totalDailyUtilizationTakeOffTotal += $dailyUtilizationTakeOffTotal;
            $totalRevenueDailyUtilizationTakeOffTotal += $revenueDailyUtilizationTakeOffTotal;
            $totalTechnicalDelayTotal += $technicalDelayTotal;
            $totalTotalDuration += $totalDuration;
            $totalRatePer100TakeOff += $ratePer100TakeOff;
            $totalTechnicalIncidentTotal += $technicalIncidentTotal;
            $totalTechnicalIncidentRate += $technicalIncidentRate;
            $totalTechnicalCancellationTotal += $technicalCancellationTotal;
            $totalDispatchReliability += $dispatchReliability;
            $totalTechnicalDelayCount += $technicalDelayTotal;
        }

        // ─────────────────────
        // AVERAGE
        // ─────────────────────
        $avgFlightHoursPerTakeOffTotal =
            $this->convertDecimalToHoursMinutes(
                $totalFlightHoursPerTakeOffTotal / 12
            );

        $avgRevenueFlightHoursPerTakeOff =
            $this->convertDecimalToHoursMinutes(
                $totalRevenueFlightHoursPerTakeOff / 12
            );

        $avgDailyUtilizationFlyingHoursTotal =
            $this->convertDecimalToHoursMinutes(
                $totalDailyUtilizationFlyingHoursTotal / 12
            );

        $avgRevenueDailyUtilizationFlyingHoursTotal =
            $this->convertDecimalToHoursMinutes(
                $totalRevenueDailyUtilizationFlyingHoursTotal / 12
            );

        $avgTotalDuration =
            $this->convertDecimalToHoursMinutes(
                $totalTotalDuration / 12
            );

        $avgAverageDuration =
            $totalTechnicalDelayCount > 0
            ? $this->convertDecimalToHoursMinutes(
                $totalTotalDuration / $totalTechnicalDelayCount
            )
            : '0 : 00';

        // ─────────────────────
        // DROPDOWN
        // ─────────────────────
        $operators = TblMasterac::select('Operator')
            ->distinct()
            ->get();

        $aircraftTypes = TblMasterac::select('ACType')
            ->distinct()
            ->get();

        $periods = TblMonthlyfhfc::select('MonthEval')
            ->distinct()
            ->orderByDesc('MonthEval')
            ->get()
            ->map(function ($item) {
                return [
                    'formatted' => Carbon::parse($item->MonthEval)->format('Y-m'),
                    'original' => $item->MonthEval
                ];
            });

        return view('report.aos-content', compact(

            'reportData',

            'period',
            'operator',
            'aircraftType',

            'month',
            'year',

            'avgFlightHoursPerTakeOffTotal',
            'avgRevenueFlightHoursPerTakeOff',
            'avgDailyUtilizationFlyingHoursTotal',
            'avgRevenueDailyUtilizationFlyingHoursTotal',
            'avgTotalDuration',
            'avgAverageDuration',

            'operators',
            'aircraftTypes',
            'periods'
        ));
    }


    // ─────────────────────────────────────────────
// GET AOS REPORT DATA
// ─────────────────────────────────────────────
    // ─── EXPORT PDF ───
    public function getAosReportData(Request $request)
    {
        $request->validate([
            'period' => 'required',
            'operator' => 'required',
            'aircraft_type' => 'required',
        ]);

        $aircraftType = $request->aircraft_type;
        $period = $request->period;
        $operator = $request->operator;

        $reportData = [];
        $totalFlightHoursPerTakeOffTotal = 0;
        $totalRevenueFlightHoursPerTakeOff = 0;
        $totalDailyUtilizationFlyingHoursTotal = 0;
        $totalRevenueDailyUtilizationFlyingHoursTotal = 0;
        $totalTotalDuration = 0;
        $totalAverageDuration = 0;

        for ($i = 11; $i >= 0; $i--) {
            $currentPeriod = Carbon::parse($period)->subMonth($i)->format('Y-m');
            $month = date('m', strtotime($currentPeriod));
            $year = date('Y', strtotime($currentPeriod));

            $acInFleet = TblMasterac::where('Active', '1')->where('ACType', $aircraftType)->count();

            $daysInService = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->sum('AvaiDays');

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $acInService = $daysInMonth > 0 ? $daysInService / $daysInMonth : 0;

            $flyingHoursTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours + (RevFHMin / 60) + NoRevFHHours + (NoRevFHMin / 60)) as total')
                ->first()->total ?? 0;

            $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours + (RevFHMin / 60)) as revenue')
                ->first()->revenue ?? 0;

            $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFC + NoRevFC) as total')
                ->first()->total ?? 0;

            $revenueTakeOff = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->sum('RevFC');

            $flightHoursPerTakeOffTotal = $takeOffTotal > 0 ? $flyingHoursTotal / $takeOffTotal : 0;
            $revenueFlightHoursPerTakeOff = $revenueTakeOff > 0 ? $revenueFlyingHours / $revenueTakeOff : 0;
            $dailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $flyingHoursTotal / $daysInService : 0;
            $revenueDailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $revenueFlyingHours / $daysInService : 0;
            $dailyUtilizationTakeOffTotal = $daysInService > 0 ? $takeOffTotal / $daysInService : 0;
            $revenueDailyUtilizationTakeOffTotal = $daysInService > 0 ? $revenueTakeOff / $daysInService : 0;

            $technicalDelayTotal = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', $month)->whereYear('DateEvent', $year)
                ->where('DCP', 'LIKE', '%D%')->count();

            $totalDuration = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', $month)->whereYear('DateEvent', $year)
                ->where('DCP', 'LIKE', '%D%')
                ->selectRaw('SUM(HoursTek + (MinTek / 60)) as total_duration')
                ->first()->total_duration ?? 0;

            $averageDuration = $technicalDelayTotal > 0 ? $totalDuration / $technicalDelayTotal : 0;
            $ratePer100TakeOff = $revenueTakeOff > 0 ? ($technicalDelayTotal * 100) / $revenueTakeOff : 0;

            $technicalIncidentTotal = TblSdr::where('ACType', $aircraftType)
                ->whereMonth('DateOccur', $month)->whereYear('DateOccur', $year)->count();

            $technicalIncidentRate = $revenueTakeOff > 0 ? ($technicalIncidentTotal * 100) / $revenueTakeOff : 0;

            $technicalCancellationTotal = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', $month)->whereYear('DateEvent', $year)
                ->where('DCP', 'LIKE', '%C%')->count();

            $dispatchReliability = $revenueTakeOff > 0
                ? (($revenueTakeOff - $technicalDelayTotal - $technicalCancellationTotal) / $revenueTakeOff) * 100
                : 0;

            $reportData[$currentPeriod] = [
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

            $totalFlightHoursPerTakeOffTotal += $flightHoursPerTakeOffTotal;
            $totalRevenueFlightHoursPerTakeOff += $revenueFlightHoursPerTakeOff;
            $totalDailyUtilizationFlyingHoursTotal += $dailyUtilizationFlyingHoursTotal;
            $totalRevenueDailyUtilizationFlyingHoursTotal += $revenueDailyUtilizationFlyingHoursTotal;
            $totalTotalDuration += $totalDuration;
            $totalAverageDuration += $averageDuration;
        }

        $avgFlightHoursPerTakeOffTotal = $this->convertDecimalToHoursMinutes($totalFlightHoursPerTakeOffTotal / 12);
        $avgRevenueFlightHoursPerTakeOff = $this->convertDecimalToHoursMinutes($totalRevenueFlightHoursPerTakeOff / 12);
        $avgDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($totalDailyUtilizationFlyingHoursTotal / 12);
        $avgRevenueDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($totalRevenueDailyUtilizationFlyingHoursTotal / 12);
        $avgTotalDuration = $this->convertDecimalToHoursMinutes($totalTotalDuration / 12);
        $avgAverageDuration = $this->convertDecimalToHoursMinutes($totalAverageDuration / 12);

        $selectedMonth = Carbon::parse($period)->format('m');
        $selectedYear = Carbon::parse($period)->format('Y');

        return compact(
            'reportData',
            'period',
            'operator',
            'aircraftType',
            'selectedMonth',
            'selectedYear',
            'avgFlightHoursPerTakeOffTotal',
            'avgRevenueFlightHoursPerTakeOff',
            'avgDailyUtilizationFlyingHoursTotal',
            'avgRevenueDailyUtilizationFlyingHoursTotal',
            'avgTotalDuration',
            'avgAverageDuration'
        );
    }
}