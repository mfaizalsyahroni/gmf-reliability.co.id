<?php

namespace App\Http\Controllers;

use App\Models\TblMasterac;
use App\Models\TblMonthlyfhfc;
use App\Models\Mcdrnew;
use App\Models\TblSdr;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AOSController extends Controller
{
    // ─── HELPER: konversi desimal → "HH : MM" ───
    private function convertDecimalToHoursMinutes($decimalHours): string
    {
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);
        return sprintf('%d : %02d', $hours, $minutes);
    }

    // ─── INDEX ───
    public function aosIndex(Request $request)
    {
        $operators = TblMasterac::select('Operator')->distinct()->get();

        $aircraftTypes = TblMasterac::select('ACType')->distinct()->get();

        $periods = TblMonthlyfhfc::select('MonthEval')->distinct()->orderByDesc('MonthEval')->get()->map(function ($item) {
            return [
                'formatted' => Carbon::parse($item->MonthEval)->format('Y-m'),
                'original' => $item->MonthEval
            ];
        });

        return view('report.aos-content', compact('aircraftTypes', 'operators', 'periods'));
    }

    // ─── AJAX: ACType by Operator ───
    public function getAircraftTypes(Request $request)
    {
        $operator = $request->input('operator');

        if (!$operator) {
            return response()->json([], 400);
        }

        $aircraftTypes = TblMasterac::where('Operator', $operator)
            ->select('ACType')
            ->distinct()
            ->get();

        return response()->json($aircraftTypes);
    }

    // ─── STORE ───
    public function aosStore(Request $request)
    {
        $request->validate([
            'period' => 'required',
            'operator' => 'required',
            'aircraft_type' => 'required',
        ]);

        $aircraftType = $request->aircraft_type;
        $period = $request->period;
        $operator = $request->input('operator');

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
                ->first()->total;

            $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours + (RevFHMin / 60)) as revenue')
                ->first()->revenue;

            $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFC + NoRevFC) as total')
                ->first()->total;

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
                ->first()->total_duration;

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
        $avgTotalDuration = $this->convertDecimalToHoursMinutes($totalTotalDuration);
        $avgAverageDuration = $this->convertDecimalToHoursMinutes($totalAverageDuration / 12);

        $operators = TblMasterac::select('Operator')->distinct()->get();
        $aircraftTypes = TblMasterac::select('ACType')->distinct()->get();
        $periods = TblMonthlyfhfc::select('MonthEval')->distinct()->orderByDesc('MonthEval')->get()
            ->map(fn($i) => [
                'formatted' => Carbon::parse($i->MonthEval)->format('Y-m'),
                'original' => $i->MonthEval
            ]);

        if ($request->ajax()) {
            return view('report.aos-content', compact(
                'reportData',
                'period',
                'aircraftType',
                'operator',
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

        return view('report.aos-content', compact(
            'reportData',
            'period',
            'aircraftType',
            'operator',
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

    //{"Operator":"NAM"}

    // ─── EXPORT PDF ───
    public function aosPdf(Request $request)
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
                ->first()->total;

            $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours + (RevFHMin / 60)) as revenue')
                ->first()->revenue;

            $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFC + NoRevFC) as total')
                ->first()->total;

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
                ->first()->total_duration;

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

        $pdf = Pdf::loadView('pdf.aos-pdf', compact(
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
            'avgAverageDuration'
        ));

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('AOS-Report-' . $year . '-' . $month . '.pdf');
    }
}