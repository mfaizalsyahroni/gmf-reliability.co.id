<?php

namespace App\Http\Controllers;

use App\Models\TblMasterac;
use App\Models\TblMonthlyfhfc;
use App\Models\Mcdrnew;
use App\Models\TblMasterAta;
use App\Models\TblAlertLevel;
use App\Models\TblPirepSwift;
use App\Models\TblSdr;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AosExport;





class ExcelAosController extends Controller
{

    public function aosIndex()
    {
        $operators = TblMasterac::select('Operator')->distinct()->get();

        // Ambil data untuk dropdown
        $aircraftTypes = TblMasterac::select('ACType')->distinct()->get();

        // Ambil dan format data periode
        $periods = TblMonthlyfhfc::select('MonthEval')->distinct()->orderByDesc('MonthEval')->get()->map(function ($item) {
            return [
                'formatted' => Carbon::parse($item->MonthEval)->format('Y-m'), // Format menjadi yyyy-mm
                'original' => $item->MonthEval
            ];
        });

        return view('report.aos-content', compact('aircraftTypes', 'operators', 'periods'));
    }

    public function getAircraftTypes(Request $request)
    {
        $operator = $request->input('operator');

        if (!$operator) {
            return response()->json([], 400); // Kembalikan error jika operator tidak ada
        }

        // Query data ACType berdasarkan operator
        $aircraftTypes = TblMasterac::where('Operator', $operator)
            ->select('ACType')
            ->distinct()
            ->get();

        return response()->json($aircraftTypes);
    }

    public function aosStore(Request $request)
    {
        // Validate input
        $request->validate([
            'period' => 'required',
            'aircraft_type' => 'required',
        ]);

        $aircraftType = $request->aircraft_type;
        $period = $request->period; // Format: YYYY-MM

        // Initialize an array to hold report data for each month
        $reportData = [];
        $totalFlightHoursPerTakeOffTotal = 0;
        $totalRevenueFlightHoursPerTakeOff = 0;
        $totalDailyUtilizationFlyingHoursTotal = 0;
        $totalRevenueDailyUtilizationFlyingHoursTotal = 0;
        $totalTotalDuration = 0;
        $totalAverageDuration = 0;

        // Loop through the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $currentPeriod = \Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m');
            $month = date('m', strtotime($currentPeriod));
            $year = date('Y', strtotime($currentPeriod));

            // 1. A/C In Fleet
            $acInFleet = TblMasterac::where('Active', '1')
                ->where('ACType', $aircraftType)
                ->count();

            // 2. A/C Days In Service
            $daysInService = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->sum('AvaiDays');

            // Calculate the number of days in the month
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            // A/C in Service
            $acInService = $daysInMonth > 0 ? $daysInService / $daysInMonth : 0;

            // 3. Flying Hours - Total
            $flyingHoursTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours + (RevFHMin / 60) + NoRevFHHours + (NoRevFHMin / 60)) as total')
                ->first()->total;

            // 4. Revenue Flying Hours
            $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours + (RevFHMin / 60)) as revenue')
                ->first()->revenue;

            // 5. Take Off - Total
            $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFC + NoRevFC) as total')
                ->first()->total;

            // 6. Revenue Take Off
            $revenueTakeOff = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->sum('RevFC');

            // 7. Flight Hours per Take Off - Total
            $flightHoursPerTakeOffTotal = $takeOffTotal > 0 ? $flyingHoursTotal / $takeOffTotal : 0;

            // 8. Revenue Flight Hours per Take Off
            $revenueFlightHoursPerTakeOff = $revenueTakeOff > 0 ? $revenueFlyingHours / $revenueTakeOff : 0;

            // 9. Daily Utilization - Flying Hours Total
            $dailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $flyingHoursTotal / $daysInService : 0;

            // 10. Revenue Daily Utilization - Flying Hours Total
            $revenueDailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $revenueFlyingHours / $daysInService : 0;

            // 11. Daily Utilization - Take Off Total
            $dailyUtilizationTakeOffTotal = $daysInService > 0 ? $takeOffTotal / $daysInService : 0;

            // 12. Revenue Daily Utilization - Take Off Total
            $revenueDailyUtilizationTakeOffTotal = $daysInService > 0 ? $revenueTakeOff / $daysInService : 0;

            // 13. Technical Delay - Total
            $technicalDelayTotal = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', '=', $month)
                ->whereYear('DateEvent', '=', $year)
                ->where('DCP', 'LIKE', '%D%')
                ->count();

            // 14. Total Duration
            $totalDuration = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', '=', $month)
                ->whereYear('DateEvent', '=', $year)
                ->where('DCP', 'LIKE', '%D%')
                ->selectRaw('SUM(HoursTek + (MinTek / 60)) as total_duration')
                ->first()->total_duration;

            // 15. Average Duration
            $averageDuration = $technicalDelayTotal > 0 ? $totalDuration / $technicalDelayTotal : 0;

            // 16. Rate / 100 Take Off
            $ratePer100TakeOff = $revenueTakeOff > 0 ? ($technicalDelayTotal * 100) / $revenueTakeOff : 0;

            // Technical Incident - Total
            $technicalIncidentTotal = TblSdr::where('ACType', $aircraftType)
                ->whereMonth('DateOccur', '=', $month)
                ->whereYear('DateOccur', '=', $year)
                ->count();

            // Technical Incident Rate /100 FC
            $technicalIncidentRate = $revenueTakeOff > 0 ? ($technicalIncidentTotal * 100) / $revenueTakeOff : 0;

            // 17. Technical Cancellation - Total
            $technicalCancellationTotal = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', '=', $month)
                ->whereYear('DateEvent', '=', $year)
                ->where('DCP', 'LIKE', '%C%')
                ->count();

            // 18. Dispatch Reliability (%)
            $dispatchReliability = $revenueTakeOff > 0 ?
                (($revenueTakeOff - $technicalDelayTotal - $technicalCancellationTotal) / $revenueTakeOff) * 100 : 0;

            // Store the metrics for the current month in the report data array
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

            // Mengonversi ke format desimal untuk penjumlahan
            $totalFlightHoursPerTakeOffTotal += $flightHoursPerTakeOffTotal;
            $totalRevenueFlightHoursPerTakeOff += $revenueFlightHoursPerTakeOff;
            $totalDailyUtilizationFlyingHoursTotal += $dailyUtilizationFlyingHoursTotal;
            $totalRevenueDailyUtilizationFlyingHoursTotal += $revenueDailyUtilizationFlyingHoursTotal;
            $totalTotalDuration += $totalDuration;
            $totalAverageDuration += $averageDuration;
        }

        // Menghitung rata-rata 12 bulan and konversi ke format (HH:MM)
        $averageFlightHoursPerTakeOffTotal = $totalFlightHoursPerTakeOffTotal / 12;
        $avgFlightHoursPerTakeOffTotal = $this->convertDecimalToHoursMinutes($averageFlightHoursPerTakeOffTotal);

        $averageRevenueFlightHoursPerTakeOff = $totalRevenueFlightHoursPerTakeOff / 12;
        $avgRevenueFlightHoursPerTakeOff = $this->convertDecimalToHoursMinutes($averageRevenueFlightHoursPerTakeOff);

        $averageDailyUtilizationFlyingHoursTotal = $totalDailyUtilizationFlyingHoursTotal / 12;
        $avgDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($averageDailyUtilizationFlyingHoursTotal);

        $averageRevenueDailyUtilizationFlyingHoursTotal =  $totalRevenueDailyUtilizationFlyingHoursTotal / 12;
        $avgRevenueDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($averageRevenueDailyUtilizationFlyingHoursTotal);

        $averageTotalDuration = $totalTotalDuration;
        $avgTotalDuration = $this->convertDecimalToHoursMinutes($averageTotalDuration);

        $averageAverageDuration = $totalAverageDuration / 12;
        $avgAverageDuration = $this->convertDecimalToHoursMinutes($averageAverageDuration);

        // Kembalikan view dengan data laporan
        return view('report.aos-result', compact(
            'reportData',
            'period',
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
    }

    // Untuk convert format menjadi (HH : MM)
    private function convertDecimalToHoursMinutes($decimalHours)
    {
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);
        return sprintf('%d : %02d', $hours, $minutes);
    }





    // EXPORT AOS TO EXCEL FORMAT
    public function exportExcel(Request $request)
    {
        // Validate input
        $request->validate([
            'period' => 'required',
            'aircraft_type' => 'required',
        ]);

        // Get the same data as in aosStore
        $aircraftType = $request->aircraft_type;
        $period = $request->period;

        // Initialize an array to hold report data for each month
        $reportData = [];
        $totalFlightHoursPerTakeOffTotal = 0;
        $totalRevenueFlightHoursPerTakeOff = 0;
        $totalDailyUtilizationFlyingHoursTotal = 0;
        $totalRevenueDailyUtilizationFlyingHoursTotal = 0;
        $totalTotalDuration = 0;
        $totalAverageDuration = 0;

        // Loop through the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $currentPeriod = \Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m');
            $month = date('m', strtotime($currentPeriod));
            $year = date('Y', strtotime($currentPeriod));

            // 1. A/C In Fleet
            $acInFleet = TblMasterac::where('Active', '1')
                ->where('ACType', $aircraftType)
                ->count();

            // 2. A/C Days In Service
            $daysInService = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->sum('AvaiDays');

            // Calculate the number of days in the month
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            // A/C in Service
            $acInService = $daysInMonth > 0 ? $daysInService / $daysInMonth : 0;

            // 3. Flying Hours - Total
            $flyingHoursTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours + (RevFHMin / 60) + NoRevFHHours + (NoRevFHMin / 60)) as total')
                ->first()->total;

            // 4. Revenue Flying Hours
            $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours + (RevFHMin / 60)) as revenue')
                ->first()->revenue;

            // 5. Take Off - Total
            $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFC + NoRevFC) as total')
                ->first()->total;

            // 6. Revenue Take Off
            $revenueTakeOff = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)
                ->whereYear('MonthEval', $year)
                ->sum('RevFC');

            // 7. Flight Hours per Take Off - Total
            $flightHoursPerTakeOffTotal = $takeOffTotal > 0 ? $flyingHoursTotal / $takeOffTotal : 0;

            // 8. Revenue Flight Hours per Take Off
            $revenueFlightHoursPerTakeOff = $revenueTakeOff > 0 ? $revenueFlyingHours / $revenueTakeOff : 0;

            // 9. Daily Utilization - Flying Hours Total
            $dailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $flyingHoursTotal / $daysInService : 0;

            // 10. Revenue Daily Utilization - Flying Hours Total
            $revenueDailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $revenueFlyingHours / $daysInService : 0;

            // 11. Daily Utilization - Take Off Total
            $dailyUtilizationTakeOffTotal = $daysInService > 0 ? $takeOffTotal / $daysInService : 0;

            // 12. Revenue Daily Utilization - Take Off Total
            $revenueDailyUtilizationTakeOffTotal = $daysInService > 0 ? $revenueTakeOff / $daysInService : 0;

            // 13. Technical Delay - Total
            $technicalDelayTotal = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', '=', $month)
                ->whereYear('DateEvent', '=', $year)
                ->where('DCP', 'LIKE', '%D%')
                ->count();

            // 14. Total Duration
            $totalDuration = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', '=', $month)
                ->whereYear('DateEvent', '=', $year)
                ->where('DCP', 'LIKE', '%D%')
                ->selectRaw('SUM(HoursTek + (MinTek / 60)) as total_duration')
                ->first()->total_duration;

            // 15. Average Duration
            $averageDuration = $technicalDelayTotal > 0 ? $totalDuration / $technicalDelayTotal : 0;

            // 16. Rate / 100 Take Off
            $ratePer100TakeOff = $revenueTakeOff > 0 ? ($technicalDelayTotal * 100) / $revenueTakeOff : 0;

            // Technical Incident - Total
            $technicalIncidentTotal = TblSdr::where('ACType', $aircraftType)
                ->whereMonth('DateOccur', '=', $month)
                ->whereYear('DateOccur', '=', $year)
                ->count();

            // Technical Incident Rate /100 FC
            $technicalIncidentRate = $revenueTakeOff > 0 ? ($technicalIncidentTotal * 100) / $revenueTakeOff : 0;

            // 17. Technical Cancellation - Total
            $technicalCancellationTotal = Mcdrnew::where('ACType', $aircraftType)
                ->whereMonth('DateEvent', '=', $month)
                ->whereYear('DateEvent', '=', $year)
                ->where('DCP', 'LIKE', '%C%')
                ->count();

            // 18. Dispatch Reliability (%)
            $dispatchReliability = $revenueTakeOff > 0 ?
                (($revenueTakeOff - $technicalDelayTotal - $technicalCancellationTotal) / $revenueTakeOff) * 100 : 0;

            // Store the metrics for the current month in the report data array
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

            // Convert to decimal format for summation
            $totalFlightHoursPerTakeOffTotal += $flightHoursPerTakeOffTotal;
            $totalRevenueFlightHoursPerTakeOff += $revenueFlightHoursPerTakeOff;
            $totalDailyUtilizationFlyingHoursTotal += $dailyUtilizationFlyingHoursTotal;
            $totalRevenueDailyUtilizationFlyingHoursTotal += $revenueDailyUtilizationFlyingHoursTotal;
            $totalTotalDuration += $totalDuration;
            $totalAverageDuration += $averageDuration;
        }

        // Calculate averages for 12 months and convert to (HH:MM) format
        $averageFlightHoursPerTakeOffTotal = $totalFlightHoursPerTakeOffTotal / 12;
        $avgFlightHoursPerTakeOffTotal = $this->convertDecimalToHoursMinutes($averageFlightHoursPerTakeOffTotal);

        $averageRevenueFlightHoursPerTakeOff = $totalRevenueFlightHoursPerTakeOff / 12;
        $avgRevenueFlightHoursPerTakeOff = $this->convertDecimalToHoursMinutes($averageRevenueFlightHoursPerTakeOff);

        $averageDailyUtilizationFlyingHoursTotal = $totalDailyUtilizationFlyingHoursTotal / 12;
        $avgDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($averageDailyUtilizationFlyingHoursTotal);

        $averageRevenueDailyUtilizationFlyingHoursTotal =  $totalRevenueDailyUtilizationFlyingHoursTotal / 12;
        $avgRevenueDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($averageRevenueDailyUtilizationFlyingHoursTotal);

        $averageTotalDuration = $totalTotalDuration / 12;
        $avgTotalDuration = $this->convertDecimalToHoursMinutes($averageTotalDuration);

        $averageAverageDuration = $totalAverageDuration / 12;
        $avgAverageDuration = $this->convertDecimalToHoursMinutes($averageAverageDuration);


        // Kembalikan view dengan data laporan
        return view('report.aos-result', compact(
            'reportData',
            'period',
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

        // Return the Excel file
        return Excel::download(new AOSExport($reportData), 'AOS-Report-' . $period . '.xlsx');
    }
}
