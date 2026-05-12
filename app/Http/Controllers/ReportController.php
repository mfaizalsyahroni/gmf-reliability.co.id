<?php

namespace App\Http\Controllers;

use App\Models\TblMasterac;
use App\Models\TblMonthlyfhfc;
use App\Models\Mcdrnew;
use App\Models\TblMasterAta;
use App\Models\TblAlertLevel;
use App\Models\TblPirepSwift;
use App\Models\TblSdr;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AOSExport;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends Controller
{



    // public function aosIndex(Request $request)
    // {
    //     $operators = TblMasterac::select('Operator')->distinct()->get();

    //     $aircraftTypes = TblMasterac::select('ACType')->distinct()->get();

    //     $periods = TblMonthlyfhfc::select('MonthEval')->distinct()->orderByDesc('MonthEval')->get()->map(function ($item) {
    //         return [
    //             'formatted' => Carbon::parse($item->MonthEval)->format('Y-m'),
    //             'original' => $item->MonthEval
    //         ];
    //     });

    //     if ($request->ajax()) {
    //         return view('report.aos-content', compact('aircraftTypes', 'operators', 'periods'));
    //     }

    //     return view('report.aos-content', compact('aircraftTypes', 'operators', 'periods'));
    // }

    // Controller — return HTML fragment, bukan JSON
    // public function getAircraftTypes(Request $request)
    // {
    //     $types = Aircraft::where('operator', $request->operator)->get();

    //     // Return partial HTML
    //     return view('partials.aircraft-options', compact('types'));
    // }
    // public function getAircraftTypes(Request $request)
    // {
    //     $operator = $request->input('operator');

    //     if (!$operator) {
    //         return response()->json([], 400); // Kembalikan error jika operator tidak ada
    //     }

    //     // Query data ACType berdasarkan operator
    //     $aircraftTypes = TblMasterac::where('Operator', $operator)
    //         ->select('ACType')
    //         ->distinct()
    //         ->get();

    //     return response()->json($aircraftTypes);
    // }

    // public function aosStore(Request $request)
    // {
    //     // Validate input
    //     $request->validate([
    //         'period' => 'required',
    //         'operator' => 'required',
    //         'aircraft_type' => 'required',

    //     ]);

    //     $aircraftType = $request->aircraft_type;
    //     $period = $request->period; // Format: YYYY-MM
    //     $operator = $request->input('operator');

    //     // Initialize an array to hold report data for each month
    //     $reportData = [];
    //     $totalFlightHoursPerTakeOffTotal = 0;
    //     $totalRevenueFlightHoursPerTakeOff = 0;
    //     $totalDailyUtilizationFlyingHoursTotal = 0;
    //     $totalRevenueDailyUtilizationFlyingHoursTotal = 0;
    //     $totalTotalDuration = 0;
    //     $totalAverageDuration = 0;

    //     // Loop through the last 12 months
    //     for ($i = 11; $i >= 0; $i--) {
    //         $currentPeriod = \Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m');
    //         $month = date('m', strtotime($currentPeriod));
    //         $year = date('Y', strtotime($currentPeriod));

    //         // 1. A/C In Fleet
    //         $acInFleet = TblMasterac::where('Active', '1')
    //             ->where('ACType', $aircraftType)
    //             ->count();

    //         // 2. A/C Days In Service
    //         $daysInService = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->sum('AvaiDays');

    //         // Calculate the number of days in the month
    //         $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    //         // A/C in Service
    //         $acInService = $daysInMonth > 0 ? $daysInService / $daysInMonth : 0;

    //         // 3. Flying Hours - Total
    //         $flyingHoursTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->selectRaw('SUM(RevFHHours + (RevFHMin / 60) + NoRevFHHours + (NoRevFHMin / 60)) as total')
    //             ->first()->total;

    //         // 4. Revenue Flying Hours
    //         $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->selectRaw('SUM(RevFHHours + (RevFHMin / 60)) as revenue')
    //             ->first()->revenue;

    //         // 5. Take Off - Total
    //         $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->selectRaw('SUM(RevFC + NoRevFC) as total')
    //             ->first()->total;

    //         // 6. Revenue Take Off
    //         $revenueTakeOff = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->sum('RevFC');

    //         // 7. Flight Hours per Take Off - Total
    //         $flightHoursPerTakeOffTotal = $takeOffTotal > 0 ? $flyingHoursTotal / $takeOffTotal : 0;

    //         // 8. Revenue Flight Hours per Take Off
    //         $revenueFlightHoursPerTakeOff = $revenueTakeOff > 0 ? $revenueFlyingHours / $revenueTakeOff : 0;

    //         // 9. Daily Utilization - Flying Hours Total
    //         $dailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $flyingHoursTotal / $daysInService : 0;

    //         // 10. Revenue Daily Utilization - Flying Hours Total
    //         $revenueDailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $revenueFlyingHours / $daysInService : 0;

    //         // 11. Daily Utilization - Take Off Total
    //         $dailyUtilizationTakeOffTotal = $daysInService > 0 ? $takeOffTotal / $daysInService : 0;

    //         // 12. Revenue Daily Utilization - Take Off Total
    //         $revenueDailyUtilizationTakeOffTotal = $daysInService > 0 ? $revenueTakeOff / $daysInService : 0;

    //         // 13. Technical Delay - Total
    //         $technicalDelayTotal = Mcdrnew::where('ACType', $aircraftType)
    //             ->whereMonth('DateEvent', '=', $month)
    //             ->whereYear('DateEvent', '=', $year)
    //             ->where('DCP', 'LIKE', '%D%')
    //             ->count();

    //         // 14. Total Duration
    //         $totalDuration = Mcdrnew::where('ACType', $aircraftType)
    //             ->whereMonth('DateEvent', '=', $month)
    //             ->whereYear('DateEvent', '=', $year)
    //             ->where('DCP', 'LIKE', '%D%')
    //             ->selectRaw('SUM(HoursTek + (MinTek / 60)) as total_duration')
    //             ->first()->total_duration;

    //         // 15. Average Duration
    //         $averageDuration = $technicalDelayTotal > 0 ? $totalDuration / $technicalDelayTotal : 0;

    //         // 16. Rate / 100 Take Off
    //         $ratePer100TakeOff = $revenueTakeOff > 0 ? ($technicalDelayTotal * 100) / $revenueTakeOff : 0;

    //         // Technical Incident - Total
    //         $technicalIncidentTotal = TblSdr::where('ACType', $aircraftType)
    //             ->whereMonth('DateOccur', '=', $month)
    //             ->whereYear('DateOccur', '=', $year)
    //             ->count();

    //         // Technical Incident Rate /100 FC
    //         $technicalIncidentRate = $revenueTakeOff > 0 ? ($technicalIncidentTotal * 100) / $revenueTakeOff : 0;

    //         // 17. Technical Cancellation - Total
    //         $technicalCancellationTotal = Mcdrnew::where('ACType', $aircraftType)
    //             ->whereMonth('DateEvent', '=', $month)
    //             ->whereYear('DateEvent', '=', $year)
    //             ->where('DCP', 'LIKE', '%C%')
    //             ->count();

    //         // 18. Dispatch Reliability (%)
    //         $dispatchReliability = $revenueTakeOff > 0 ?
    //             (($revenueTakeOff - $technicalDelayTotal - $technicalCancellationTotal) / $revenueTakeOff) * 100 : 0;

    //         // Store the metrics for the current month in the report data array
    //         $reportData[$currentPeriod] = [
    //             'acInFleet' => $acInFleet,
    //             'acInService' => $acInService,
    //             'daysInService' => $daysInService,
    //             'flyingHoursTotal' => $flyingHoursTotal,
    //             'revenueFlyingHours' => $revenueFlyingHours,
    //             'takeOffTotal' => $takeOffTotal,
    //             'revenueTakeOff' => $revenueTakeOff,
    //             'flightHoursPerTakeOffTotal' => $this->convertDecimalToHoursMinutes($flightHoursPerTakeOffTotal),
    //             'revenueFlightHoursPerTakeOff' => $this->convertDecimalToHoursMinutes($revenueFlightHoursPerTakeOff),
    //             'dailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($dailyUtilizationFlyingHoursTotal),
    //             'revenueDailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($revenueDailyUtilizationFlyingHoursTotal),
    //             'dailyUtilizationTakeOffTotal' => $dailyUtilizationTakeOffTotal,
    //             'revenueDailyUtilizationTakeOffTotal' => $revenueDailyUtilizationTakeOffTotal,
    //             'technicalDelayTotal' => $technicalDelayTotal,
    //             'totalDuration' => $this->convertDecimalToHoursMinutes($totalDuration),
    //             'averageDuration' => $this->convertDecimalToHoursMinutes($averageDuration),
    //             'ratePer100TakeOff' => $ratePer100TakeOff,
    //             'technicalIncidentTotal' => $technicalIncidentTotal,
    //             'technicalIncidentRate' => $technicalIncidentRate,
    //             'technicalCancellationTotal' => $technicalCancellationTotal,
    //             'dispatchReliability' => $dispatchReliability,
    //         ];

    //         // Mengonversi ke format desimal untuk penjumlahan
    //         $totalFlightHoursPerTakeOffTotal += $flightHoursPerTakeOffTotal;
    //         $totalRevenueFlightHoursPerTakeOff += $revenueFlightHoursPerTakeOff;
    //         $totalDailyUtilizationFlyingHoursTotal += $dailyUtilizationFlyingHoursTotal;
    //         $totalRevenueDailyUtilizationFlyingHoursTotal += $revenueDailyUtilizationFlyingHoursTotal;
    //         $totalTotalDuration += $totalDuration;
    //         $totalAverageDuration += $averageDuration;
    //     }

    //     // Menghitung rata-rata 12 bulan and konversi ke format (HH:MM)
    //     $averageFlightHoursPerTakeOffTotal = $totalFlightHoursPerTakeOffTotal / 12;
    //     $avgFlightHoursPerTakeOffTotal = $this->convertDecimalToHoursMinutes($averageFlightHoursPerTakeOffTotal);

    //     $averageRevenueFlightHoursPerTakeOff = $totalRevenueFlightHoursPerTakeOff / 12;
    //     $avgRevenueFlightHoursPerTakeOff = $this->convertDecimalToHoursMinutes($averageRevenueFlightHoursPerTakeOff);

    //     $averageDailyUtilizationFlyingHoursTotal = $totalDailyUtilizationFlyingHoursTotal / 12;
    //     $avgDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($averageDailyUtilizationFlyingHoursTotal);

    //     $averageRevenueDailyUtilizationFlyingHoursTotal = $totalRevenueDailyUtilizationFlyingHoursTotal / 12;
    //     $avgRevenueDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($averageRevenueDailyUtilizationFlyingHoursTotal);

    //     $averageTotalDuration = $totalTotalDuration;
    //     $avgTotalDuration = $this->convertDecimalToHoursMinutes($averageTotalDuration);

    //     $averageAverageDuration = $totalAverageDuration / 12;
    //     $avgAverageDuration = $this->convertDecimalToHoursMinutes($averageAverageDuration);

    //     $operators = TblMasterac::select('Operator')->distinct()->get();
    //     $aircraftTypes = TblMasterac::select('ACType')->distinct()->get();
    //     $periods = TblMonthlyfhfc::select('MonthEval')->distinct()->orderByDesc('MonthEval')->get()
    //         ->map(fn($i) => [
    //             'formatted' => Carbon::parse($i->MonthEval)->format('Y-m'),
    //             'original' => $i->MonthEval
    //         ]);



    //     // return view('report.aos-result', compact(
    //     return view('report.aos-content', compact(
    //         'reportData',
    //         'period',
    //         'aircraftType',
    //         'operator',
    //         'month',
    //         'year',
    //         'avgFlightHoursPerTakeOffTotal',
    //         'avgRevenueFlightHoursPerTakeOff',
    //         'avgDailyUtilizationFlyingHoursTotal',
    //         'avgRevenueDailyUtilizationFlyingHoursTotal',
    //         'avgTotalDuration',
    //         'avgAverageDuration',
    //         'operators',
    //         'aircraftTypes',
    //         'periods'
    //     ));
    // }


    // Untuk convert format menjadi (HH : MM)
    // private function convertDecimalToHoursMinutes($decimalHours)
    // {
    //     $hours = floor($decimalHours);
    //     $minutes = round(($decimalHours - $hours) * 60);
    //     return sprintf('%d : %02d', $hours, $minutes);
    // }

    // EXPORT AOS TO PDF FORMAT
    // public function aosPdf(Request $request)
    // {
    //     // Validasi input
    //     $request->validate([
    //         'period' => 'required',
    //         'operator' => 'required',
    //         'aircraft_type' => 'required',
    //     ]);


    //     // Ambil data yang sama seperti di metode aosStore
    //     $aircraftType = $request->aircraft_type;
    //     $period = $request->period;
    //     $operator = $request->operator;

    //     // ... (logika untuk menghitung reportData)
    //     // Initialize an array to hold report data for each month
    //     $reportData = [];
    //     $totalFlightHoursPerTakeOffTotal = 0;
    //     $totalRevenueFlightHoursPerTakeOff = 0;
    //     $totalDailyUtilizationFlyingHoursTotal = 0;
    //     $totalRevenueDailyUtilizationFlyingHoursTotal = 0;
    //     $totalTotalDuration = 0;
    //     $totalAverageDuration = 0;

    //     // Loop through the last 12 months
    //     for ($i = 11; $i >= 0; $i--) {
    //         $currentPeriod = \Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m');
    //         $month = date('m', strtotime($currentPeriod));
    //         $year = date('Y', strtotime($currentPeriod));

    //         // 1. A/C In Fleet
    //         $acInFleet = TblMasterac::where('Active', '1')
    //             ->where('ACType', $aircraftType)
    //             ->count();

    //         // 2. A/C Days In Service
    //         $daysInService = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->sum('AvaiDays');

    //         // Calculate the number of days in the month
    //         $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    //         // A/C in Service
    //         $acInService = $daysInMonth > 0 ? $daysInService / $daysInMonth : 0;

    //         // 3. Flying Hours - Total
    //         $flyingHoursTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->selectRaw('SUM(RevFHHours + (RevFHMin / 60) + NoRevFHHours + (NoRevFHMin / 60)) as total')
    //             ->first()->total;

    //         // 4. Revenue Flying Hours
    //         $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->selectRaw('SUM(RevFHHours + (RevFHMin / 60)) as revenue')
    //             ->first()->revenue;

    //         // 5. Take Off - Total
    //         $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->selectRaw('SUM(RevFC + NoRevFC) as total')
    //             ->first()->total;

    //         // 6. Revenue Take Off
    //         $revenueTakeOff = TblMonthlyfhfc::where('Actype', $aircraftType)
    //             ->whereMonth('MonthEval', $month)
    //             ->whereYear('MonthEval', $year)
    //             ->sum('RevFC');

    //         // 7. Flight Hours per Take Off - Total
    //         $flightHoursPerTakeOffTotal = $takeOffTotal > 0 ? $flyingHoursTotal / $takeOffTotal : 0;

    //         // 8. Revenue Flight Hours per Take Off
    //         $revenueFlightHoursPerTakeOff = $revenueTakeOff > 0 ? $revenueFlyingHours / $revenueTakeOff : 0;

    //         // 9. Daily Utilization - Flying Hours Total
    //         $dailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $flyingHoursTotal / $daysInService : 0;

    //         // 10. Revenue Daily Utilization - Flying Hours Total
    //         $revenueDailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $revenueFlyingHours / $daysInService : 0;

    //         // 11. Daily Utilization - Take Off Total
    //         $dailyUtilizationTakeOffTotal = $daysInService > 0 ? $takeOffTotal / $daysInService : 0;

    //         // 12. Revenue Daily Utilization - Take Off Total
    //         $revenueDailyUtilizationTakeOffTotal = $daysInService > 0 ? $revenueTakeOff / $daysInService : 0;

    //         // 13. Technical Delay - Total
    //         $technicalDelayTotal = Mcdrnew::where('ACType', $aircraftType)
    //             ->whereMonth('DateEvent', '=', $month)
    //             ->whereYear('DateEvent', '=', $year)
    //             ->where('DCP', 'LIKE', '%D%')
    //             ->count();

    //         // 14. Total Duration
    //         $totalDuration = Mcdrnew::where('ACType', $aircraftType)
    //             ->whereMonth('DateEvent', '=', $month)
    //             ->whereYear('DateEvent', '=', $year)
    //             ->where('DCP', 'LIKE', '%D%')
    //             ->selectRaw('SUM(HoursTek + (MinTek / 60)) as total_duration')
    //             ->first()->total_duration;

    //         // 15. Average Duration
    //         $averageDuration = $technicalDelayTotal > 0 ? $totalDuration / $technicalDelayTotal : 0;

    //         // 16. Rate / 100 Take Off
    //         $ratePer100TakeOff = $revenueTakeOff > 0 ? ($technicalDelayTotal * 100) / $revenueTakeOff : 0;

    //         // Technical Incident - Total
    //         $technicalIncidentTotal = TblSdr::where('ACType', $aircraftType)
    //             ->whereMonth('DateOccur', '=', $month)
    //             ->whereYear('DateOccur', '=', $year)
    //             ->count();

    //         // Technical Incident Rate /100 FC
    //         $technicalIncidentRate = $revenueTakeOff > 0 ? ($technicalIncidentTotal * 100) / $revenueTakeOff : 0;

    //         // 17. Technical Cancellation - Total
    //         $technicalCancellationTotal = Mcdrnew::where('ACType', $aircraftType)
    //             ->whereMonth('DateEvent', '=', $month)
    //             ->whereYear('DateEvent', '=', $year)
    //             ->where('DCP', 'LIKE', '%C%')
    //             ->count();

    //         // 18. Dispatch Reliability (%)
    //         $dispatchReliability = $revenueTakeOff > 0 ?
    //             (($revenueTakeOff - $technicalDelayTotal - $technicalCancellationTotal) / $revenueTakeOff) * 100 : 0;

    //         // Store the metrics for the current month in the report data array
    //         $reportData[$currentPeriod] = [
    //             'acInFleet' => $acInFleet,
    //             'acInService' => $acInService,
    //             'daysInService' => $daysInService,
    //             'flyingHoursTotal' => $flyingHoursTotal,
    //             'revenueFlyingHours' => $revenueFlyingHours,
    //             'takeOffTotal' => $takeOffTotal,
    //             'revenueTakeOff' => $revenueTakeOff,
    //             'flightHoursPerTakeOffTotal' => $this->convertDecimalToHoursMinutes($flightHoursPerTakeOffTotal),
    //             'revenueFlightHoursPerTakeOff' => $this->convertDecimalToHoursMinutes($revenueFlightHoursPerTakeOff),
    //             'dailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($dailyUtilizationFlyingHoursTotal),
    //             'revenueDailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($revenueDailyUtilizationFlyingHoursTotal),
    //             'dailyUtilizationTakeOffTotal' => $dailyUtilizationTakeOffTotal,
    //             'revenueDailyUtilizationTakeOffTotal' => $revenueDailyUtilizationTakeOffTotal,
    //             'technicalDelayTotal' => $technicalDelayTotal,
    //             'totalDuration' => $this->convertDecimalToHoursMinutes($totalDuration),
    //             'averageDuration' => $this->convertDecimalToHoursMinutes($averageDuration),
    //             'ratePer100TakeOff' => $ratePer100TakeOff,
    //             'technicalIncidentTotal' => $technicalIncidentTotal,
    //             'technicalIncidentRate' => $technicalIncidentRate,
    //             'technicalCancellationTotal' => $technicalCancellationTotal,
    //             'dispatchReliability' => $dispatchReliability,
    //         ];

    //         // Mengonversi ke format desimal untuk penjumlahan
    //         $totalFlightHoursPerTakeOffTotal += $flightHoursPerTakeOffTotal;
    //         $totalRevenueFlightHoursPerTakeOff += $revenueFlightHoursPerTakeOff;
    //         $totalDailyUtilizationFlyingHoursTotal += $dailyUtilizationFlyingHoursTotal;
    //         $totalRevenueDailyUtilizationFlyingHoursTotal += $revenueDailyUtilizationFlyingHoursTotal;
    //         $totalTotalDuration += $totalDuration;
    //         $totalAverageDuration += $averageDuration;
    //     }

    //     // Menghitung rata-rata 12 bulan and konversi ke format (HH:MM)
    //     $averageFlightHoursPerTakeOffTotal = $totalFlightHoursPerTakeOffTotal / 12;
    //     $avgFlightHoursPerTakeOffTotal = $this->convertDecimalToHoursMinutes($averageFlightHoursPerTakeOffTotal);

    //     $averageRevenueFlightHoursPerTakeOff = $totalRevenueFlightHoursPerTakeOff / 12;
    //     $avgRevenueFlightHoursPerTakeOff = $this->convertDecimalToHoursMinutes($averageRevenueFlightHoursPerTakeOff);

    //     $averageDailyUtilizationFlyingHoursTotal = $totalDailyUtilizationFlyingHoursTotal / 12;
    //     $avgDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($averageDailyUtilizationFlyingHoursTotal);

    //     $averageRevenueDailyUtilizationFlyingHoursTotal = $totalRevenueDailyUtilizationFlyingHoursTotal / 12;
    //     $avgRevenueDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($averageRevenueDailyUtilizationFlyingHoursTotal);

    //     $averageTotalDuration = $totalTotalDuration / 12;
    //     $avgTotalDuration = $this->convertDecimalToHoursMinutes($averageTotalDuration);

    //     $averageAverageDuration = $totalAverageDuration / 12;
    //     $avgAverageDuration = $this->convertDecimalToHoursMinutes($averageAverageDuration);

    //     // Kembalikan PDF
    //     $pdf = PDF::loadView('pdf.aos-pdf', compact(
    //         'reportData',
    //         'period',
    //         'operator',
    //         'aircraftType',
    //         'month',
    //         'year',
    //         'avgFlightHoursPerTakeOffTotal',
    //         'avgRevenueFlightHoursPerTakeOff',
    //         'avgDailyUtilizationFlyingHoursTotal',
    //         'avgRevenueDailyUtilizationFlyingHoursTotal',
    //         'avgTotalDuration',
    //         'avgAverageDuration'
    //     ));

    //     $pdf->setPaper('A4', 'landscape');

    //     return $pdf->download('AOS-Report-' . $year . '-' . $month . '.pdf');
    // }


    //     public function exportExcel(Request $request)
//     {
//         // Validasi input
//         $request->validate([
//             'period' => 'required|date_format:Y-m',
//             'aircraft_type' => 'required|string',
//         ]);

    //         $aircraftType = $request->aircraft_type;
//         $period = $request->period; // Format: YYYY-MM

    //         // Ambil data laporan
//         $reportData = $this->getReportData($period, $aircraftType, $request);

    //         logger($reportData);

    //         if (empty($reportData)) {
//             return response()->json(['message' => 'No data found for the specified period and aircraft type.'], 404);
//         }

    //         // Pastikan reportData adalah array dan konversi ke Collection
//         $reportDataCollection = collect($reportData);

    //     // Buat dan kembalikan file Excel
//     return Excel::download(new AOSExport($reportDataCollection, $aircraftType, date('Y', strtotime($period))), 'AOS_Report_' . $period . '.xlsx');
// }

    //     private function getReportData($period, $aircraftType, $request)
//     {
//         // // Validasi input
//         // $request->validate([
//         //     'period' => 'required',
//         //     'aircraft_type' => 'required',
//         // ]);


    //         // // Ambil data yang sama seperti di metode aosStore
//         // $aircraftType = $request->aircraft_type;
//         // $period = $request->period;

    //         // ... (logika untuk menghitung reportData)
//         // Initialize an array to hold report data for each month
//         $reportData = [];
//         $totalFlightHoursPerTakeOffTotal = 0;
//         $totalRevenueFlightHoursPerTakeOff = 0;
//         $totalDailyUtilizationFlyingHoursTotal = 0;
//         $totalRevenueDailyUtilizationFlyingHoursTotal = 0;
//         $totalTotalDuration=0;
//         $totalAverageDuration=0;

    //         // Loop through the last 12 months
//         for ($i = 11; $i >= 0; $i--) {
//             $currentPeriod = \Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m');
//             $month = date('m', strtotime($currentPeriod));
//             $year = date('Y', strtotime($currentPeriod));

    //             // 1. A/C In Fleet
//             $acInFleet = TblMasterac::where('Active', '1')
//                 ->where('ACType', $aircraftType)
//                 ->count();

    //             // 2. A/C Days In Service
//             $daysInService = TblMonthlyfhfc::where('Actype', $aircraftType)
//                 ->whereMonth('MonthEval', $month)
//                 ->whereYear('MonthEval', $year)
//                 ->sum('AvaiDays');

    //             // Calculate the number of days in the month
//             $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    //             // A/C in Service
//             $acInService = $daysInMonth > 0 ? $daysInService / $daysInMonth : 0;

    //             // 3. Flying Hours - Total
//             $flyingHoursTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
//                 ->whereMonth('MonthEval', $month)
//                 ->whereYear('MonthEval', $year)
//                 ->selectRaw('SUM(RevFHHours + (RevFHMin / 60) + NoRevFHHours + (NoRevFHMin / 60)) as total')
//                 ->first()->total;

    //             // 4. Revenue Flying Hours
//             $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
//                 ->whereMonth('MonthEval', $month)
//                 ->whereYear('MonthEval', $year)
//                 ->selectRaw('SUM(RevFHHours + (RevFHMin / 60)) as revenue')
//                 ->first()->revenue;

    //             // 5. Take Off - Total
//             $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
//                 ->whereMonth('MonthEval', $month)
//                 ->whereYear('MonthEval', $year)
//                 ->selectRaw('SUM(RevFC + NoRevFC) as total')
//                 ->first()->total;

    //             // 6. Revenue Take Off
//             $revenueTakeOff = TblMonthlyfhfc::where('Actype', $aircraftType)
//                 ->whereMonth('MonthEval', $month)
//                 ->whereYear('MonthEval', $year)
//                 ->sum('RevFC');

    //             // 7. Flight Hours per Take Off - Total
//             $flightHoursPerTakeOffTotal = $takeOffTotal > 0 ? $flyingHoursTotal / $takeOffTotal : 0;

    //             // 8. Revenue Flight Hours per Take Off
//             $revenueFlightHoursPerTakeOff = $revenueTakeOff > 0 ? $revenueFlyingHours / $revenueTakeOff : 0;

    //             // 9. Daily Utilization - Flying Hours Total
//             $dailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $flyingHoursTotal / $daysInService : 0;

    //             // 10. Revenue Daily Utilization - Flying Hours Total
//             $revenueDailyUtilizationFlyingHoursTotal = $daysInService > 0 ? $revenueFlyingHours / $daysInService : 0;

    //             // 11. Daily Utilization - Take Off Total
//             $dailyUtilizationTakeOffTotal = $daysInService > 0 ? $takeOffTotal / $daysInService : 0;

    //             // 12. Revenue Daily Utilization - Take Off Total
//             $revenueDailyUtilizationTakeOffTotal = $daysInService > 0 ? $revenueTakeOff / $daysInService : 0;

    //             // 13. Technical Delay - Total
//             $technicalDelayTotal = Mcdrnew::where('ACType', $aircraftType)
//                 ->whereMonth('DateEvent', '=', $month)
//                 ->whereYear('DateEvent', '=', $year)
//                 ->where('DCP', 'LIKE', '%D%')
//                 ->count();

    //             // 14. Total Duration
//             $totalDuration = Mcdrnew::where('ACType', $aircraftType)
//                 ->whereMonth('DateEvent', '=', $month)
//                 ->whereYear('DateEvent', '=', $year)
//                 ->where('DCP', 'LIKE', '%D%')
//                 ->selectRaw('SUM(HoursTek + (MinTek / 60)) as total_duration')
//                 ->first()->total_duration;

    //             // 15. Average Duration
//             $averageDuration = $technicalDelayTotal > 0 ? $totalDuration / $technicalDelayTotal : 0;

    //             // 16. Rate / 100 Take Off
//             $ratePer100TakeOff = $revenueTakeOff > 0 ? ($technicalDelayTotal * 100) / $revenueTakeOff : 0;

    //             // Technical Incident - Total
//             $technicalIncidentTotal = TblSdr::where('ACType', $aircraftType)
//                 ->whereMonth('DateOccur', '=', $month)
//                 ->whereYear('DateOccur', '=', $year)
//                 ->count();

    //             // Technical Incident Rate /100 FC
//             $technicalIncidentRate = $revenueTakeOff > 0 ? ($technicalIncidentTotal * 100) / $revenueTakeOff : 0;

    //             // 17. Technical Cancellation - Total
//             $technicalCancellationTotal = Mcdrnew::where('ACType', $aircraftType)
//                 ->whereMonth('DateEvent', '=', $month)
//                 ->whereYear('DateEvent', '=', $year)
//                 ->where('DCP', 'LIKE', '%C%')
//                 ->count();

    //             // 18. Dispatch Reliability (%)
//             $dispatchReliability = $revenueTakeOff > 0 ? 
//                 (($revenueTakeOff - $technicalDelayTotal - $technicalCancellationTotal) / $revenueTakeOff) * 100 : 0;

    //             // Store the metrics for the current month in the report data array
//             $reportData[$currentPeriod] = [
//                 'acInFleet' => $acInFleet,
//                 'acInService' => $acInService,
//                 'daysInService' => $daysInService,
//                 'flyingHoursTotal' => $flyingHoursTotal,
//                 'revenueFlyingHours' => $revenueFlyingHours,
//                 'takeOffTotal' => $takeOffTotal,
//                 'revenueTakeOff' => $revenueTakeOff,
//                 'flightHoursPerTakeOffTotal' => $this->convertDecimalToHoursMinutes($flightHoursPerTakeOffTotal),
//                 'revenueFlightHoursPerTakeOff' => $this->convertDecimalToHoursMinutes($revenueFlightHoursPerTakeOff),
//                 'dailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($dailyUtilizationFlyingHoursTotal),
//                 'revenueDailyUtilizationFlyingHoursTotal' => $this->convertDecimalToHoursMinutes($revenueDailyUtilizationFlyingHoursTotal),
//                 'dailyUtilizationTakeOffTotal' => $dailyUtilizationTakeOffTotal,
//                 'revenueDailyUtilizationTakeOffTotal' => $revenueDailyUtilizationTakeOffTotal,
//                 'technicalDelayTotal' => $technicalDelayTotal,
//                 'totalDuration' => $this->convertDecimalToHoursMinutes($totalDuration),
//                 'averageDuration' => $this->convertDecimalToHoursMinutes($averageDuration),
//                 'ratePer100TakeOff' => $ratePer100TakeOff,
//                 'technicalIncidentTotal' => $technicalIncidentTotal,
//                 'technicalIncidentRate' => $technicalIncidentRate,
//                 'technicalCancellationTotal' => $technicalCancellationTotal,
//                 'dispatchReliability' => $dispatchReliability,
//             ];

    //             // Mengonversi ke format desimal untuk penjumlahan
//             $totalFlightHoursPerTakeOffTotal += $flightHoursPerTakeOffTotal;
//             $totalRevenueFlightHoursPerTakeOff += $revenueFlightHoursPerTakeOff;
//             $totalDailyUtilizationFlyingHoursTotal += $dailyUtilizationFlyingHoursTotal;
//             $totalRevenueDailyUtilizationFlyingHoursTotal += $revenueDailyUtilizationFlyingHoursTotal;
//             $totalTotalDuration += $totalDuration;
//             $totalAverageDuration += $averageDuration;
//         }

    //         // Menghitung rata-rata 12 bulan and konversi ke format (HH:MM)
//         $averageFlightHoursPerTakeOffTotal = $totalFlightHoursPerTakeOffTotal / 12;
//         $avgFlightHoursPerTakeOffTotal = $this->convertDecimalToHoursMinutes($averageFlightHoursPerTakeOffTotal);

    //         $averageRevenueFlightHoursPerTakeOff = $totalRevenueFlightHoursPerTakeOff / 12;
//         $avgRevenueFlightHoursPerTakeOff = $this->convertDecimalToHoursMinutes($averageRevenueFlightHoursPerTakeOff);

    //         $averageDailyUtilizationFlyingHoursTotal = $totalDailyUtilizationFlyingHoursTotal / 12;
//         $avgDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes($averageDailyUtilizationFlyingHoursTotal);

    //         $averageRevenueDailyUtilizationFlyingHoursTotal =  $totalRevenueDailyUtilizationFlyingHoursTotal / 12;
//         $avgRevenueDailyUtilizationFlyingHoursTotal = $this->convertDecimalToHoursMinutes( $averageRevenueDailyUtilizationFlyingHoursTotal);

    //         $averageTotalDuration = $totalTotalDuration / 12;
//         $avgTotalDuration = $this->convertDecimalToHoursMinutes($averageTotalDuration);

    //         $averageAverageDuration = $totalAverageDuration / 12;
//         $avgAverageDuration = $this->convertDecimalToHoursMinutes($averageAverageDuration);

    //         // Pastikan Anda mengembalikan $reportData di akhir fungsi
//     return $reportData; // Pastikan ini ada
// }



    //Controller Button Pilot Report
    public function pilotIndex(Request $request)
    {
        $aircraftTypes = TblPirepSwift::select('ACTYPE')->distinct()->whereNotNull('ACTYPE')->where('ACTYPE', '!=', '')->where('ACTYPE', '!=', 'default')->get();

        $operators = TblMasterac::select('Operator')->distinct()->whereNotNull('Operator')->where('Operator', '!=', '')->get();

        $periods = TblMonthlyfhfc::select('MonthEval')->distinct()->orderByDesc('MonthEval')->get()->map(function ($item) {
            return [
                'formatted' => Carbon::parse($item->MonthEval)->format('Y-m'),
                'original' => $item->MonthEval
            ];
        });

        if ($request->ajax()) {
            return view('report.pilot-content', compact('aircraftTypes', 'periods', 'operators'));
        }

        return view('report.pilot-content', compact('aircraftTypes', 'periods', 'operators'));
    }

    public function pilotStore(Request $request)
    {
        // Validate input
        $request->validate([
            'period' => 'required',
            'aircraft_type' => 'required',
        ]);

        $aircraftType = $request->aircraft_type;
        $period = $request->period; // Format: YYYY-MM

        // Mendapatkan bulan dan tahun dari periode
        $month = date('m', strtotime($period));
        $year = date('Y', strtotime($period));

        // Fungsi untuk menghitung total flying hours
        $getFlyingHours = function ($aircraftType, $period) {
            return TblMonthlyfhfc::where('Actype', $aircraftType)
                ->where('MonthEval', $period)
                ->selectRaw('SUM(RevFHHours + (RevFHMin / 60) + NoRevFHHours + (NoRevFHMin / 60)) as total')
                ->first()->total ?? 0; // Menggunakan null coalescing operator
        };

        // Hitung flying hours untuk periode sekarang dan sebelumnya
        $flyingHoursTotal = $getFlyingHours($aircraftType, $period);
        $flyingHoursBefore = $getFlyingHours($aircraftType, \Carbon\Carbon::parse($period)->subMonth(1));
        $flyingHours2Before = $getFlyingHours($aircraftType, \Carbon\Carbon::parse($period)->subMonth(2));
        $fh3Last = $flyingHoursTotal + $flyingHoursBefore + $flyingHours2Before;
        $fh12Last = 0;
        for ($i = 0; $i <= 11; $i++) {
            $periodBefore = \Carbon\Carbon::parse($period)->subMonth($i);
            $fh12Last += $getFlyingHours($aircraftType, $periodBefore);
        }

        // Daftar ID yang ingin dikecualikan
        $excludedIds = [5, 11, 12, 58, 70];
        // Ambil semua data dari tbl_master_ata kecuali yang ada di $excludedIds
        $tblAta = TblMasterAta::whereNotIn('ATA', $excludedIds)->get();


        // ~~~~~ {{ PILOT REPORT }} ~~~~~
        // Fungsi untuk menghitung PIREP
        $getPirepCount = function ($aircraftType, $month, $year) {
            return TblPirepSwift::where('ACTYPE', $aircraftType)
                ->whereMonth('DATE', $month)
                ->whereYear('DATE', $year)
                ->where('PirepMarep', 'Pirep')
                ->whereIn('ATA', ['21'])
                ->count();
        };
        // Hitung PIREP untuk periode sekarang dan sebelumnya
        $pirepCount = $getPirepCount($aircraftType, $month, $year);
        $pirepCountBefore = $getPirepCount($aircraftType, \Carbon\Carbon::parse($period)->subMonth(1)->month, $year);
        $pirepCountTwoMonthsAgo = $getPirepCount($aircraftType, \Carbon\Carbon::parse($period)->subMonths(2)->month, $year);
        $pirep3Month = $pirepCount + $pirepCountBefore + $pirepCountTwoMonthsAgo;
        $pirep12Month = 0;
        for ($i = 0; $i < 12; $i++) {
            $month = \Carbon\Carbon::parse($period)->subMonths($i)->month;
            $year = \Carbon\Carbon::parse($period)->subMonths($i)->year;
            $pirep12Month += $getPirepCount($aircraftType, $month, $year);
        }
        // ~~~ PIREP RATE PERIOD ~~~
        $pirepRate = $pirepCount * 1000 / ($flyingHoursTotal ?: 1); // Menghindari pembagian dengan nol
        $pirep1Rate = $pirepCountBefore * 1000 / ($flyingHoursBefore ?: 1);
        $pirep2Rate = $pirepCountTwoMonthsAgo * 1000 / ($flyingHours2Before ?: 1);
        $pirepRate3Month = ($pirepRate + $pirep1Rate + $pirep2Rate) / 3;
        $pirepRate12Month = $pirep12Month * 1000 / ($fh12Last ?: 1);
        // PIREP ALERT LEVEL
        $pirepAlertLevel = TblAlertLevel::where('actype', $aircraftType)
            ->where('ata', '21')
            ->where('type', 'ALP')
            ->where(function ($query) use ($period) {
                $query->whereBetween('startmonth', [$period, $period])
                    ->orWhereBetween('endmonth', [$period, $period])
                    ->orWhere(function ($query) use ($period) {
                        $query->where('startmonth', '<=', $period)
                            ->where('endmonth', '>=', $period);
                    });
            })
            ->pluck('alertlevel')
            ->first();
        // ~~~ PIREP ALERT STATUS ~~~
        $pirepAlertStatus = '';
        if ($pirepRate > $pirepAlertLevel && $pirep1Rate > $pirepAlertLevel && $pirep2Rate > $pirepAlertLevel) {
            $pirepAlertStatus = 'RED-3';
        } elseif ($pirepRate > $pirepAlertLevel && $pirep1Rate > $pirepAlertLevel) {
            $pirepAlertStatus = 'RED-2';
        } elseif ($pirepRate > $pirepAlertLevel) {
            $pirepAlertStatus = 'RED-1';
        }
        // ~~~ PIREP TREND ~~~
        $pirepTrend = '';
        if ($pirep1Rate > $pirep2Rate && $pirep1Rate < $pirepRate) {
            $pirepTrend = 'UP';
        } elseif ($pirep1Rate < $pirep2Rate && $pirep1Rate > $pirepRate) {
            $pirepTrend = 'DOWN';
        }

        // ~~~ PIREP ~~~
        // $pirepData = [];
        // foreach ($tblAta as $item) {
        //     $pirepData = $item->ATA;

        //     $pirepCount = $getPirepCount($aircraftType, $month, $year, $pirepData);
        //     $pirepCountBefore = $getPirepCount($aircraftType, \Carbon\Carbon::parse($period)->subMonth(1)->month, $year, $pirepData);
        //     $pirepCountTwoMonthsAgo = $getPirepCount($aircraftType, \Carbon\Carbon::parse($period)->subMonths(2)->month, $year, $pirepData);

        //     $pirep3Month = $pirepCount + $pirepCountBefore + $pirepCountTwoMonthsAgo;
        //     $pirep12Month = 0;
        //     for ($i = 0; $i < 12; $i++) {
        //         $month = \Carbon\Carbon::parse($period)->subMonths($i)->month;
        //         $year = \Carbon\Carbon::parse($period)->subMonths($i)->year;
        //         $pirep12Month += $getPirepCount($aircraftType, $month, $year, $pirepData);
        //     }

        //     $pirepRate = $pirepCount * 1000 / ($flyingHoursTotal ?: 1);
        //     $pirep1Rate = $pirepCountBefore * 1000 / ($flyingHoursBefore ?: 1);
        //     $pirep2Rate = $pirepCountTwoMonthsAgo * 1000 / ($flyingHours2Before ?: 1);
        //     $pirepRate3Month = ($pirepRate + $pirep1Rate + $pirep2Rate) / 3;
        //     $pirepRate12Month = $pirep12Month * 1000 / ($fh12Last ?: 1);

        //     $pirepData[$item->ATA] = [
        //         'countTwoMonthsAgo' => $pirepCountTwoMonthsAgo,
        //         'countBefore' => $pirepCountBefore,
        //         'count' => $pirepCount,
        //         'threeMonth' => $pirep3Month,
        //         'twelveMonth' => $pirep12Month,
        //         'rate2' => $pirep2Rate,
        //         'rate1' => $pirep1Rate,
        //         'rate' => $pirepRate,
        //         'rate3Month' => $pirepRate3Month,
        //         'alertLevel' => $pirepAlertLevel, // Anda mungkin ingin menghitung ini juga untuk setiap ATA
        //         'alertStatus' => $pirepAlertStatus, // Anda mungkin ingin menghitung ini juga untuk setiap ATA
        //         'trend' => $pirepTrend, // Anda mungkin ingin menghitung ini juga untuk setiap ATA
        //     ];
        // }

        // ~~~~~ {{ Maintenance Report }} ~~~~~
        // Fungsi untuk menghitung MAREP
        $getMarepCount = function ($aircraftType, $month, $year) {
            return TblPirepSwift::where('ACTYPE', $aircraftType)
                ->whereMonth('DATE', $month)
                ->whereYear('DATE', $year)
                ->where('PirepMarep', 'Marep')
                ->where('ATA', ['21'])
                ->count();
        };
        // // Hitung MAREP untuk periode sekarang dan sebelumnya
        $marepCount = $getMarepCount($aircraftType, $month, $year);
        $marepCountBefore = $getMarepCount($aircraftType, \Carbon\Carbon::parse($period)->subMonth(1)->month, $year);
        $marepCountTwoMonthsAgo = $getMarepCount($aircraftType, \Carbon\Carbon::parse($period)->subMonths(2)->month, $year);
        $marep3Month = $marepCount + $marepCountBefore + $marepCountTwoMonthsAgo;
        $marep12Month = '0';
        for ($i = 0; $i < 12; $i++) {
            $month = \Carbon\Carbon::parse($period)->subMonths($i)->month;
            $year = \Carbon\Carbon::parse($period)->subMonths($i)->year;
            $marep12Month += $getMarepCount($aircraftType, $month, $year);
        }
        // ~~~ MAREP RATE PERIOD ~~~
        $marepRate = $marepCount * 1000 / ($flyingHoursTotal ?: 1);
        $marep1Rate = $marepCountBefore * 1000 / ($flyingHoursBefore ?: 1);
        $marep2Rate = $marepCountTwoMonthsAgo * 1000 / ($flyingHours2Before ?: 1);
        $marepRate3Month = ($marepRate + $marep1Rate + $marep2Rate) / 3;
        $marepRate12Month = $marep12Month * 1000 / ($fh12Last ?: 1);
        // ~~~ MAREP ALERT LEVEL ~~~
        $marepAlertLevel = TblAlertLevel::where('actype', $aircraftType)
            ->where('ata', '21')
            ->where('type', 'ALM')
            ->where(function ($query) use ($period) {
                $query->whereBetween('startmonth', [$period, $period])
                    ->orWhereBetween('endmonth', [$period, $period])
                    ->orWhere(function ($query) use ($period) {
                        $query->where('startmonth', '<=', $period)
                            ->where('endmonth', '>=', $period);
                    });
            })
            ->pluck('alertlevel')
            ->first();
        // ~~~ MAREP ALERT STATUS ~~~
        $marepAlertStatus = '';
        if ($marepRate > $marepAlertLevel && $marep1Rate > $marepAlertLevel && $marep2Rate > $marepAlertLevel) {
            $marepAlertStatus = 'RED-3';
        } elseif ($marepRate > $marepAlertLevel && $marep1Rate > $marepAlertLevel) {
            $marepAlertStatus = 'RED-2';
        } elseif ($marepRate > $marepAlertLevel) {
            $marepAlertStatus = 'RED-1';
        }
        // ~~~ MAREP TREND ~~~
        $marepTrend = '';
        if ($marep1Rate > $marep2Rate && $marep1Rate < $marepRate) {
            $marepTrend = 'UP';
        } elseif ($marep1Rate < $marep2Rate && $marep1Rate > $marepRate) {
            $marepTrend = 'DOWN';
        }


        // ~~~~~ {{ Technical Delay }} ~~~~~
        // ~~~ COUNTING TECHNICAL DELAY ~~~
        $getDelayCount = function ($aircraftType, $month, $year) {
            return Mcdrnew::where('ACtype', $aircraftType)
                ->whereMonth('DateEvent', $month)
                ->whereYear('DateEvent', $year)
                ->where('DCP', 'D')
                ->where('ATAtdm', '21')
                ->count();
        };
        $delayCount = $getDelayCount($aircraftType, $month, $year);
        $delayCountBefore = $getDelayCount($aircraftType, \Carbon\Carbon::parse($period)->subMonth(1)->month, $year);
        $delayCountTwoMonthsAgo = $getDelayCount($aircraftType, \Carbon\Carbon::parse($period)->subMonths(2)->month, $year);
        $delay3Month = $delayCount + $delayCountBefore + $delayCountTwoMonthsAgo;
        $delay12Month = '0';
        for ($i = 0; $i < 12; $i++) {
            $month = \Carbon\Carbon::parse($period)->subMonths($i)->month;
            $year = \Carbon\Carbon::parse($period)->subMonths($i)->year;
            $delay12Month += $getDelayCount($aircraftType, $month, $year);
        }


        // ~~~ TECHNICAL DELAY RATE ~~~
        $delayRate = $delayCount * 1000 / ($flyingHoursTotal ?: 1);
        $delay1Rate = $delayCountBefore * 1000 / ($flyingHoursBefore ?: 1);
        $delay2Rate = $delayCountTwoMonthsAgo * 1000 / ($flyingHours2Before ?: 1);
        $delayRate3Month = ($delayRate + $delay1Rate + $delay2Rate) / 3;
        $delayRate12Month = $delay12Month * 1000 / ($fh12Last ?: 1);
        // ~~~ TECHNICAL DELAY ALERT LEVEL ~~~
        $delayAlertLevel = TblAlertLevel::where('actype', $aircraftType)
            ->where('ata', '21')
            ->where('type', 'ALD')
            ->where(function ($query) use ($period) {
                $query->whereBetween('startmonth', [$period, $period])
                    ->orWhereBetween('endmonth', [$period, $period])
                    ->orWhere(function ($query) use ($period) {
                        $query->where('startmonth', '<=', $period)
                            ->where('endmonth', '>=', $period);
                    });
            })
            ->pluck('alertlevel')
            ->first();
        // ~~~ TECHNICAL DELAY ALERT STATUS ~~~
        $delayAlertStatus = '';
        if ($delayRate > $delayAlertLevel && $delay1Rate > $delayAlertLevel && $delay2Rate > $delayAlertLevel) {
            $delayAlertStatus = 'RED-3';
        } elseif ($delayRate > $delayAlertLevel && $delay1Rate > $delayAlertLevel) {
            $delayAlertStatus = 'RED-2';
        } elseif ($delayRate > $delayAlertLevel) {
            $delayAlertStatus = 'RED-1';
        }
        // ~~~ TECHNICAL DELAY TREND ~~~
        $delayTrend = '';
        if ($delay1Rate > $delay2Rate && $delay1Rate < $delayRate) {
            $delayTrend = 'UP';
        } elseif ($delay1Rate < $delay2Rate && $delay1Rate > $delayRate) {
            $delayTrend = 'DOWN';
        }



        // Mengirimkan semua variabel yang diperlukan ke view
        return view('report.pilot-result', [
            'flyingHoursTotal' => $flyingHoursTotal,
            'flyingHoursBefore' => $flyingHoursBefore,
            'flyingHours2Before' => $flyingHours2Before,
            'fh3Last' => $fh3Last,
            'fh12Last' => $fh12Last,
            'aircraftType' => $aircraftType,
            'tblAta' => $tblAta,
            'month' => $month,
            'period' => $period,
            // 'pirepData' => $pirepData,
            'pirepCount' => $pirepCount, // AWAL PILOT REPORT
            'pirepCountBefore' => $pirepCountBefore,
            'pirepCountTwoMonthsAgo' => $pirepCountTwoMonthsAgo,
            'pirep3Month' => $pirep3Month,
            'pirep12Month' => $pirep12Month,
            'pirepRate' => $pirepRate,
            'pirep1Rate' => $pirep1Rate,
            'pirep2Rate' => $pirep2Rate,
            'pirepRate3Month' => $pirepRate3Month,
            'pirepRate12Month' => $pirepRate12Month,
            'pirepAlertLevel' => $pirepAlertLevel,
            'pirepAlertStatus' => $pirepAlertStatus,
            'pirepTrend' => $pirepTrend,
            'marepCount' => $marepCount, // AWAL MAINTENANCE REPORT
            'marepCountBefore' => $marepCountBefore,
            'marepCountTwoMonthsAgo' => $marepCountTwoMonthsAgo,
            'marep3Month' => $marep3Month,
            'marep12Month' => $marep12Month,
            'marepRate' => $marepRate,
            'marep1Rate' => $marep1Rate,
            'marep2Rate' => $marep2Rate,
            'marepRate3Month' => $marepRate3Month,
            'marepRate12Month' => $marepRate12Month,
            'marepAlertLevel' => $marepAlertLevel,
            'marepAlertStatus' => $marepAlertStatus,
            'marepTrend' => $marepTrend,
            'delayCount' => $delayCount, // AWAL TECHNICAL DELAY
            'delayCountBefore' => $delayCountBefore,
            'delayCountTwoMonthsAgo' => $delayCountTwoMonthsAgo,
            'delay3Month' => $delay3Month,
            'delay12Month' => $delay12Month,
            'delayRate' => $delayRate,
            'delay1Rate' => $delay1Rate,
            'delay2Rate' => $delay2Rate,
            'delayRate3Month' => $delayRate3Month,
            'delayRate12Month' => $delayRate12Month,
            'delayAlertLevel' => $delayAlertLevel,
            'delayAlertStatus' => $delayAlertStatus,
            'delayTrend' => $delayTrend
        ]);
    }
}
