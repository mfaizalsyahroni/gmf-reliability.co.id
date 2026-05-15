<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TblMasterac;
use App\Models\TblMonthlyfhfc;
use App\Models\Mcdrnew;
use App\Models\TblSdr;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelController extends Controller
{

    private function convertDecimalToHoursMinutes($decimalHours): string
    {
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);
        return sprintf('%d : %02d', $hours, $minutes);
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'period' => 'required',
            'operator' => 'required',
            'aircraft_type' => 'required',
        ]);

        $period = $request->period;
        $operator = $request->operator;
        $aircraftType = $request->aircraft_type;
        $periodDt = Carbon::parse($period);

        // ── Reuse exact same logic as aosStore ────────────────────────────────
        $reportData = [];

        $totalFlightHoursPerTakeOffTotal = 0;
        $totalRevenueFlightHoursPerTakeOff = 0;
        $totalDailyUtilizationFlyingHoursTotal = 0;
        $totalRevenueDailyUtilizationFlyingHoursTotal = 0;
        $totalTotalDuration = 0;
        $totalTechnicalDelayCount = 0;

        // raw numeric accumulators (untuk last-12 di excel)
        $rawTotals = [
            'acInFleet' => 0,
            'acInService' => 0,
            'daysInService' => 0,
            'flyingHoursTotal' => 0,
            'revenueFlyingHours' => 0,
            'takeOffTotal' => 0,
            'revenueTakeOff' => 0,
            'dailyUtilizationTakeOffTotal' => 0,
            'revenueDailyUtilizationTakeOffTotal' => 0,
            'technicalDelayTotal' => 0,
            'ratePer100TakeOff' => 0,
            'technicalIncidentTotal' => 0,
            'technicalIncidentRate' => 0,
            'technicalCancellationTotal' => 0,
            'dispatchReliability' => 0,
        ];

        // raw numeric per-month (untuk ditulis ke excel)
        $rawMonthly = [];

        for ($i = 11; $i >= 0; $i--) {
            $currentPeriod = Carbon::parse($period)->subMonth($i)->format('Y-m');
            $month = date('m', strtotime($currentPeriod));
            $year = date('Y', strtotime($currentPeriod));

            $acInFleet = TblMasterac::where('Active', '1')
                ->where('ACType', $aircraftType)
                ->where('Operator', $operator)
                ->count();

            $daysInService = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->sum('AvaiDays');

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $acInService = $daysInMonth > 0 ? $daysInService / $daysInMonth : 0;

            $flyingHoursTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours+(RevFHMin/60)+NoRevFHHours+(NoRevFHMin/60)) as total')
                ->first()->total ?? 0;

            $revenueFlyingHours = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFHHours+(RevFHMin/60)) as revenue')
                ->first()->revenue ?? 0;

            $takeOffTotal = TblMonthlyfhfc::where('Actype', $aircraftType)
                ->whereMonth('MonthEval', $month)->whereYear('MonthEval', $year)
                ->selectRaw('SUM(RevFC+NoRevFC) as total')
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
                ->selectRaw('SUM(HoursTek+(MinTek/60)) as total_duration')
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

            // store raw numerics per month
            $rawMonthly[$currentPeriod] = [
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

            // accumulate
            $rawTotals['acInFleet'] += $acInFleet;
            $rawTotals['acInService'] += $acInService;
            $rawTotals['daysInService'] += $daysInService;
            $rawTotals['flyingHoursTotal'] += $flyingHoursTotal;
            $rawTotals['revenueFlyingHours'] += $revenueFlyingHours;
            $rawTotals['takeOffTotal'] += $takeOffTotal;
            $rawTotals['revenueTakeOff'] += $revenueTakeOff;
            $rawTotals['dailyUtilizationTakeOffTotal'] += $dailyUtilizationTakeOffTotal;
            $rawTotals['revenueDailyUtilizationTakeOffTotal'] += $revenueDailyUtilizationTakeOffTotal;
            $rawTotals['technicalDelayTotal'] += $technicalDelayTotal;
            $rawTotals['ratePer100TakeOff'] += $ratePer100TakeOff;
            $rawTotals['technicalIncidentTotal'] += $technicalIncidentTotal;
            $rawTotals['technicalIncidentRate'] += $technicalIncidentRate;
            $rawTotals['technicalCancellationTotal'] += $technicalCancellationTotal;
            $rawTotals['dispatchReliability'] += $dispatchReliability;

            $totalFlightHoursPerTakeOffTotal += $flightHoursPerTakeOffTotal;
            $totalRevenueFlightHoursPerTakeOff += $revenueFlightHoursPerTakeOff;
            $totalDailyUtilizationFlyingHoursTotal += $dailyUtilizationFlyingHoursTotal;
            $totalRevenueDailyUtilizationFlyingHoursTotal += $revenueDailyUtilizationFlyingHoursTotal;
            $totalTotalDuration += $totalDuration;
            $totalTechnicalDelayCount += $technicalDelayTotal;
        }

        // ── last-12 averages ──────────────────────────────────────────────────
        $last12 = [
            'acInFleet' => $rawTotals['acInFleet'] / 12,
            'acInService' => $rawTotals['acInService'] / 12,
            'daysInService' => $rawTotals['daysInService'],           // sum
            'flyingHoursTotal' => $rawTotals['flyingHoursTotal'],         // sum
            'revenueFlyingHours' => $rawTotals['revenueFlyingHours'],        // sum
            'takeOffTotal' => $rawTotals['takeOffTotal'],              // sum
            'revenueTakeOff' => $rawTotals['revenueTakeOff'],            // sum
            'flightHoursPerTakeOffTotal' => $totalFlightHoursPerTakeOffTotal / 12,
            'revenueFlightHoursPerTakeOff' => $totalRevenueFlightHoursPerTakeOff / 12,
            'dailyUtilizationFlyingHoursTotal' => $totalDailyUtilizationFlyingHoursTotal / 12,
            'revenueDailyUtilizationFlyingHoursTotal' => $totalRevenueDailyUtilizationFlyingHoursTotal / 12,
            'dailyUtilizationTakeOffTotal' => $rawTotals['dailyUtilizationTakeOffTotal'] / 12,
            'revenueDailyUtilizationTakeOffTotal' => $rawTotals['revenueDailyUtilizationTakeOffTotal'] / 12,
            'technicalDelayTotal' => $rawTotals['technicalDelayTotal'],      // sum
            'totalDuration' => $totalTotalDuration,                    // sum
            'averageDuration' => $totalTechnicalDelayCount > 0 ? $totalTotalDuration / $totalTechnicalDelayCount : 0,
            'ratePer100TakeOff' => $rawTotals['ratePer100TakeOff'] / 12,
            'technicalIncidentTotal' => $rawTotals['technicalIncidentTotal'] / 12,
            'technicalIncidentRate' => $rawTotals['technicalIncidentRate'] / 12,
            'technicalCancellationTotal' => $rawTotals['technicalCancellationTotal'],// sum
            'dispatchReliability' => $rawTotals['dispatchReliability'] / 12,
        ];

        // ── Build Excel ───────────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('AOS Report');

        $headerFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E4EAF0']];
        $last12Fill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E8F5']];
        $evenFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FBFF']];
        $oddFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']];
        $thinBorder = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']];
        $allBorders = ['allBorders' => $thinBorder];
        $CENTER = Alignment::HORIZONTAL_CENTER;
        $LEFT = Alignment::HORIZONTAL_LEFT;
        $RIGHT = Alignment::HORIZONTAL_RIGHT;
        $VCENTER = Alignment::VERTICAL_CENTER;

        // Title
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'Aircraft Operations Summary');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
            'alignment' => ['horizontal' => $CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', "Operator: {$operator}   |   Aircraft Type: {$aircraftType}   |   Period: " . $periodDt->format('F Y'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'name' => 'Arial'],
            'alignment' => ['horizontal' => $CENTER],
        ]);

        // Column headers row 4
        $sheet->getRowDimension(4)->setRowHeight(30);
        $sheet->setCellValue('A4', 'Metrics');

        for ($i = 0; $i < 12; $i++) {
            $col = Coordinate::stringFromColumnIndex(2 + $i);
            $sheet->setCellValue("{$col}4", Carbon::parse($period)->subMonths(11 - $i)->format('M'));
        }

        $sheet->setCellValue('N4', "Last 12\nMTHS");
        $sheet->getStyle('N4')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A4:N4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'name' => 'Arial'],
            'fill' => $headerFill,
            'borders' => $allBorders,
            'alignment' => ['horizontal' => $CENTER, 'vertical' => $VCENTER],
        ]);
        $sheet->getStyle('N4')->applyFromArray(['fill' => $last12Fill]);

        // Metric rows
        // [label, key, format, hh:mm?]
        // format: int | f2 | f3 | pct
        // hhMm: true = nilai sudah diconvert ke "HH : MM" string
        $metrics = [
            ['A/C In Fleet', 'acInFleet', 'int', false],
            ['A/C In Service', 'acInService', 'f2', false],
            ['A/C Days In Service', 'daysInService', 'int', false],
            ['Flying Hours - Total', 'flyingHoursTotal', 'int', false],
            ['Revenue Flying Hours', 'revenueFlyingHours', 'int', false],
            ['Take Off - Total', 'takeOffTotal', 'int', false],
            ['Revenue Take Off', 'revenueTakeOff', 'int', false],
            ['Flight Hours per Take Off - Total', 'flightHoursPerTakeOffTotal', 'hhMm', true],
            ['Revenue Flight Hours per Take Off', 'revenueFlightHoursPerTakeOff', 'hhMm', true],
            ['Daily Utilization - Flying Hours Total', 'dailyUtilizationFlyingHoursTotal', 'hhMm', true],
            ['Revenue Daily Utilization - Flying Hours', 'revenueDailyUtilizationFlyingHoursTotal', 'hhMm', true],
            ['Daily Utilization - Take Off Total', 'dailyUtilizationTakeOffTotal', 'f2', false],
            ['Revenue Daily Utilization - Take Off', 'revenueDailyUtilizationTakeOffTotal', 'f2', false],
            ['Technical Delay - Total', 'technicalDelayTotal', 'int', false],
            ['Total Duration', 'totalDuration', 'hhMm', true],
            ['Average Duration', 'averageDuration', 'hhMm', true],
            ['Rate / 100 Take Off', 'ratePer100TakeOff', 'f2', false],
            ['Technical Incident - Total', 'technicalIncidentTotal', 'int', false],
            ['  Technical Incident Rate / 100 FC', 'technicalIncidentRate', 'f3', false],
            ['Technical Cancellation - Total', 'technicalCancellationTotal', 'int', false],
            ['Dispatch Reliability (%)', 'dispatchReliability', 'pct', false],
        ];

        $numFormats = [
            'int' => '#,##0',
            'f2' => '#,##0.00',
            'f3' => '#,##0.000',
            'pct' => '#,##0.00"%"',
            'hhMm' => '@',   // text
        ];

        foreach ($metrics as $rowIdx => [$label, $key, $fmt, $isHhMm]) {
            $r = 5 + $rowIdx;
            $rowFill = $rowIdx % 2 === 0 ? $evenFill : $oddFill;

            $sheet->getRowDimension($r)->setRowHeight(14);

            // label
            $sheet->setCellValue("A{$r}", $label);
            $sheet->getStyle("A{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'name' => 'Arial'],
                'fill' => $rowFill,
                'borders' => $allBorders,
                'alignment' => ['horizontal' => $LEFT, 'vertical' => $VCENTER],
            ]);

            // monthly
            for ($i = 0; $i < 12; $i++) {
                $keyYm = Carbon::parse($period)->subMonths(11 - $i)->format('Y-m');
                $v = $rawMonthly[$keyYm][$key] ?? 0;
                $col = Coordinate::stringFromColumnIndex(2 + $i);
                $cell = "{$col}{$r}";

                if ($isHhMm) {
                    $sheet->setCellValueExplicit(
                        $cell,
                        $this->convertDecimalToHoursMinutes($v),
                        DataType::TYPE_STRING
                    );
                } else {
                    $sheet->setCellValue($cell, $v);
                }

                $sheet->getStyle($cell)->applyFromArray([
                    'font' => ['size' => 9, 'name' => 'Arial'],
                    'fill' => $rowFill,
                    'borders' => $allBorders,
                    'alignment' => ['horizontal' => $RIGHT, 'vertical' => $VCENTER],
                    'numberFormat' => ['formatCode' => $numFormats[$fmt]],
                ]);
            }

            // last 12
            $lv = $last12[$key] ?? 0;
            if ($isHhMm) {
                $sheet->setCellValueExplicit(
                    "N{$r}",
                    $this->convertDecimalToHoursMinutes($lv),
                    DataType::TYPE_STRING
                );
            } else {
                $sheet->setCellValue("N{$r}", $lv);
            }
            $sheet->getStyle("N{$r}")->applyFromArray([
                'font' => ['size' => 9, 'name' => 'Arial'],
                'fill' => $last12Fill,
                'borders' => $allBorders,
                'alignment' => ['horizontal' => $RIGHT, 'vertical' => $VCENTER],
                'numberFormat' => ['formatCode' => $numFormats[$fmt]],
            ]);
        }

        // column widths & freeze
        $sheet->getColumnDimension('A')->setWidth(40);
        for ($col = 2; $col <= 14; $col++) {
            $sheet->getColumnDimension(
                Coordinate::stringFromColumnIndex($col)
            )->setWidth(9);
        }
        $sheet->freezePane('B5');

        // ── Stream download ───────────────────────────────────────────────────
        $filename = 'AOS_' . $operator . '_' . $aircraftType . '_' . $period . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
