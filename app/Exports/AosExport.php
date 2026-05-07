<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AOSExport implements FromCollection, WithHeadings
{
    protected $reportData;

    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        $data = [];
        foreach ($this->reportData as $period => $metrics) {
            $data[] = [
                'Monthly Period of in a Year' => $period,
                'A/C In Fleet' => $metrics['acInFleet'],
                'A/C In Service' => $metrics['acInService'],
                'Days In Service' => $metrics['daysInService'],
                'Flying Hours Total' => $metrics['flyingHoursTotal'],
                'Revenue Flying Hours' => $metrics['revenueFlyingHours'],
                'Take Off Total' => $metrics['takeOffTotal'],
                'Revenue Take Off' => $metrics['revenueTakeOff'],
                'Flight Hours per Take Off' => $metrics['flightHoursPerTakeOffTotal'],
                'Revenue Flight Hours per Take Off' => $metrics['revenueFlightHoursPerTakeOff'],
                'Daily Utilization Flying Hours' => $metrics['dailyUtilizationFlyingHoursTotal'],
                'Revenue Daily Utilization Flying Hours' => $metrics['revenueDailyUtilizationFlyingHoursTotal'],
                'Technical Delay Total' => $metrics['technicalDelayTotal'],
                'Total Duration' => $metrics['totalDuration'],
                'Average Duration' => $metrics['averageDuration'],
                'Rate / 100 Take Off' => $metrics['ratePer100TakeOff'],
                'Technical Incident - Total' => $metrics['technicalIncidentTotal'],
                'Technical Incident Rate / 100 FC' => $metrics['technicalIncidentRate'],
                'Technical Cancellation - Total' => $metrics['technicalCancellationTotal'],
                'Dispatch Reliability' => $metrics['dispatchReliability'],
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Monthly Period of in a Year',
            'A/C In Fleet',
            'A/C In Service',
            'Days In Service',
            'Flying Hours Total',
            'Revenue Flying Hours',
            'Take Off Total',
            'Revenue Take Off',
            'Flight Hours per Take Off',
            'Revenue Flight Hours per Take Off',
            'Daily Utilization Flying Hours',
            'Revenue Daily Utilization Flying Hours',
            'Technical Delay Total',
            'Total Duration',
            'Average Duration',
            'Rate / 100 Take Off',
            'Technical Incident - Total',
            'Technical Incident Rate / 100 FC',
            'Technical Cancellation - Total',
            'Dispatch Reliability',
        ];
    }
}