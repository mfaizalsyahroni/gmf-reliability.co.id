<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelController extends Controller
{
    public function aosExcel(Request $request)
    {
        $aosController = new AOSController();
        $data = $aosController->getAosRawData($request);

        $period = $data['period'];
        $operator = $data['operator'];
        $aircraftType = $data['aircraftType'];
        $rawMonthly = $data['rawMonthly'];
        $last12 = $data['last12'];
        $periodDt = Carbon::parse($period);

        // Style
        $headerFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E4EAF0']];
        $last12Fill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E8F5']];
        $evenFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FBFF']];
        $oddFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']];
        $allBorders = ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]];
        $CENTER = Alignment::HORIZONTAL_CENTER;
        $LEFT = Alignment::HORIZONTAL_LEFT;
        $RIGHT = Alignment::HORIZONTAL_RIGHT;
        $VCENTER = Alignment::VERTICAL_CENTER;


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('AOS Report');

        // Title
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'Aircraft Operations Summary');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
            'alignment' => ['horizontal' => $CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Subtitle
        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', "Operator: {$operator}   |   Aircraft Type: {$aircraftType}   |   Period: " . $periodDt->format('F Y'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'name' => 'Arial'],
            'alignment' => ['horizontal' => $CENTER],
        ]);

        // Header row
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

        // Metrics definition [label, key, format, isHhMm]
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
            'hhMm' => '@',
        ];

        $aosCtrl = new AOSController();

        foreach ($metrics as $rowIdx => [$label, $key, $fmt, $isHhMm]) {
            $r = 5 + $rowIdx;
            $rowFill = $rowIdx % 2 === 0 ? $evenFill : $oddFill;
            $sheet->getRowDimension($r)->setRowHeight(14);

            $sheet->setCellValue("A{$r}", $label);
            $sheet->getStyle("A{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'name' => 'Arial'],
                'fill' => $rowFill,
                'borders' => $allBorders,
                'alignment' => ['horizontal' => $LEFT, 'vertical' => $VCENTER],
            ]);

            for ($i = 0; $i < 12; $i++) {
                $keyYm = Carbon::parse($period)->subMonths(11 - $i)->format('Y-m');
                $v = $rawMonthly[$keyYm][$key] ?? 0;
                $col = Coordinate::stringFromColumnIndex(2 + $i);
                $cell = "{$col}{$r}";

                $isHhMm
                    ? $sheet->setCellValueExplicit($cell, $aosCtrl->convertDecimalToHoursMinutes($v), DataType::TYPE_STRING)
                    : $sheet->setCellValue($cell, $v);

                $sheet->getStyle($cell)->applyFromArray([
                    'font' => ['size' => 9, 'name' => 'Arial'],
                    'fill' => $rowFill,
                    'borders' => $allBorders,
                    'alignment' => ['horizontal' => $RIGHT, 'vertical' => $VCENTER],
                    'numberFormat' => ['formatCode' => $numFormats[$fmt]],
                ]);
            }

            $lv = $last12[$key] ?? 0;
            $isHhMm
                ? $sheet->setCellValueExplicit("N{$r}", $aosCtrl->convertDecimalToHoursMinutes($lv), DataType::TYPE_STRING)
                : $sheet->setCellValue("N{$r}", $lv);

            $sheet->getStyle("N{$r}")->applyFromArray([
                'font' => ['size' => 9, 'name' => 'Arial'],
                'fill' => $last12Fill,
                'borders' => $allBorders,
                'alignment' => ['horizontal' => $RIGHT, 'vertical' => $VCENTER],
                'numberFormat' => ['formatCode' => $numFormats[$fmt]],
            ]);
        }

        // Column widths & freeze
        $sheet->getColumnDimension('A')->setWidth(40);
        for ($col = 2; $col <= 14; $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setWidth(9);
        }
        $sheet->freezePane('B5');

        // Stream download
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