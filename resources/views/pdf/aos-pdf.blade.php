<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>AOS PDF</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        .style1 {
            text-align: center;
        }
        .style2 {
            font-size: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div>
        <table>
            <thead>
                <tr>
                    <th colspan="14" class="style2" style="border-bottom: none;">AIRCRAFT OPERATION SUMMARY</th>
                </tr>
                <tr>
                    <th colspan="14" class="style2" style="border-top: none; border-bottom: none;">
                        <img src="{{ public_path('images/plane.png') }}" style="height: 20px;">
                        {{ $operator }}</th>
                </tr>
                <tr>
                    <th colspan="14" class="style2" style="border-top: none;">{{ $aircraftType }}</th>
                </tr>   
                <tr>
                    {{-- <th></th> --}}
                    <th colspan="14">{{ $year-1 }}-{{ $year }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Inisialisasi total untuk setiap metrik
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
                    $totalAverageDuration = 0;
                    $totalRatePer100TakeOff = 0;
                    $totalTechnicalIncidentTotal= 0;
                    $totalTechnicalIncidentRate=0;
                    $totalTechnicalCancellationTotal=0;
                    $totalDispatchReliability=0;
                @endphp
                <tr>
                    <td>Monthly Period in a Year</td>
                    @for ($i = 11; $i >= 0; $i--)
                        <td class="style1">{{ substr(\Carbon\Carbon::parse($period)->subMonth($i)->format('F'), 0, 3) }}</td>
                    @endfor
                    <td class="style1">LAST 12 MTHS</td>
                </tr>
                <tr>
                    <td>A/C in Fleet</td>
                    @for ($i = 11; $i >= 0; $i--)
                        @php
                        $acInFleet = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['acInFleet'];
                            $totalAcInFleet += $acInFleet;
                        @endphp
                        <td class="style1">{{ $acInFleet }}</td>
                    @endfor
                    <td class="style1">{{ $totalAcInFleet / 12 }}</td>
                </tr>
                <tr>
                    <td>A/C in Service</td>
                    @for ($i = 11; $i >= 0; $i--)
                        @php
                            $acInService = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['acInService'];
                            $totalAcInService += $acInService;
                        @endphp
                        <td class="style1">{{ number_format($acInService, 2) }}</td>
                    @endfor
                    <td class="style1">{{ number_format($totalAcInService / 12, 2) }}</td>
                </tr>
                <tr>
                    <td>A/C Days in Service</td>
                    @for ($i = 11; $i >= 0; $i--)
                        @php
                            $daysInService = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['daysInService'];
                            $totalDaysInService += $daysInService;
                        @endphp
                        <td class="style1">{{ $daysInService }}</td>
                    @endfor
                    <td class="style1">{{ round($totalDaysInService) }}</td>
                </tr>
                <tr>
                    <td>Flying Hours - Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                        @php
                            $flyingHoursTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['flyingHoursTotal'];
                            $totalFlyingHoursTotal += $flyingHoursTotal;
                        @endphp
                        <td class="style1">{{ round($flyingHoursTotal) }}</td>
                    @endfor
                    <td class="style1">{{ round($totalFlyingHoursTotal) }}</td>
                </tr>
                <tr>
                    <td>Revenue Flying Hours</td>
                    @for ($i = 11; $i >= 0; $i--)
                        @php
                            $revenueFlyingHours = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueFlyingHours'];
                            $totalRevenueFlyingHours += $revenueFlyingHours;
                        @endphp
                        <td class="style1">{{ round($revenueFlyingHours) }}</td>
                    @endfor
                    <td class="style1">{{ round($totalRevenueFlyingHours) }}</td>
                </tr>
                <tr>
                    <td>Take Off - Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $takeOffTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['takeOffTotal'];
                                $totalTakeOffTotal += $takeOffTotal;
                            @endphp
                            <td class="style1">{{ $takeOffTotal }}</td>
                        @endfor
                        <td class="style1">{{ round($totalTakeOffTotal) }}</td>
                </tr>
                <tr>
                    <td>Revenue Take Off</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $revenueTakeOff = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueTakeOff'];
                                $totalRevenueTakeOff += $revenueTakeOff;
                            @endphp
                            <td class="style1">{{ $revenueTakeOff }}</td>
                        @endfor
                        <td class="style1">{{ round($totalRevenueTakeOff) }}</td>
                </tr>
                <tr>
                    <td>Flight Hours per Take Off - Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $flightHoursPerTakeOffTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['flightHoursPerTakeOffTotal'];
                            @endphp
                            <td class="style1">{{ $flightHoursPerTakeOffTotal }}</td>
                        @endfor
                        <td class="style1">{{ $avgFlightHoursPerTakeOffTotal }}</td>
                </tr>
                <tr>
                    <td>Revenue Flight Hours per Take Off</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $revenueFlightHoursPerTakeOff = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueFlightHoursPerTakeOff'];
                            @endphp
                            <td class="style1">{{ $revenueFlightHoursPerTakeOff }}</td>
                        @endfor
                        <td class="style1">{{ $avgRevenueFlightHoursPerTakeOff }}</td>
                </tr>
                <tr>
                    <td>Daily Utilization - Flying Hours Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $dailyUtilizationFlyingHoursTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['dailyUtilizationFlyingHoursTotal'];
                            @endphp
                            <td class="style1">{{ $dailyUtilizationFlyingHoursTotal }}</td>
                        @endfor
                        <td class="style1">{{ $avgDailyUtilizationFlyingHoursTotal }}</td>
                </tr>
                <tr>
                    <td>Revenue Daily Utilization - Flying Hours Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $revenueDailyUtilizationFlyingHoursTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueDailyUtilizationFlyingHoursTotal'];
                            @endphp
                            <td class="style1">{{ $revenueDailyUtilizationFlyingHoursTotal }}</td>
                        @endfor
                        <td class="style1">{{ $avgRevenueDailyUtilizationFlyingHoursTotal }}</td>
                </tr>
                <tr>
                    <td>Daily Utilization - Take Off Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $dailyUtilizationTakeOffTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['dailyUtilizationTakeOffTotal'];
                                $totalDailyUtilizationTakeOffTotal += is_numeric($dailyUtilizationTakeOffTotal) ? $dailyUtilizationTakeOffTotal : 0;
                            @endphp
                            <td class="style1">{{ number_format($dailyUtilizationTakeOffTotal, 2) }}</td>
                        @endfor
                        <td class="style1">{{ number_format($totalDailyUtilizationTakeOffTotal / 12, 2) }}</td>
                </tr>
                <tr>
                    <td>Revenue Daily Utilization - Take Off Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $revenueDailyUtilizationTakeOffTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueDailyUtilizationTakeOffTotal'];
                                $totalRevenueDailyUtilizationTakeOffTotal += is_numeric($revenueDailyUtilizationTakeOffTotal) ? $revenueDailyUtilizationTakeOffTotal : 0;
                            @endphp
                            <td class="style1">{{ number_format($revenueDailyUtilizationTakeOffTotal, 2)}}</td>
                        @endfor
                        <td class="style1">{{ number_format($totalRevenueDailyUtilizationTakeOffTotal / 12, 2) }}</td>
                </tr>
                <tr>
                    <td>Technical Delay - Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                        @php
                            $technicalDelayTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['technicalDelayTotal'];
                            $totalTechnicalDelayTotal += is_numeric($technicalDelayTotal) ? $technicalDelayTotal:0;
                        @endphp
                        <td class="style1">{{ round($technicalDelayTotal) }}</td>
                    @endfor
                    <td class="style1">{{ round($totalTechnicalDelayTotal) }}</td>
                </tr>
                <tr>
                    <td>Total Duration</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $totalDuration = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['totalDuration'];
                            @endphp
                            <td class="style1">{{ $totalDuration }}</td>
                        @endfor
                        <td class="style1">{{ $avgTotalDuration }}</td>
                </tr>
                <tr>
                    <td>Average Duration</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $averageDuration = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['averageDuration']
                            @endphp
                            <td class="style1">{{ $averageDuration }}</td>
                        @endfor
                        <td class="style1">{{ $avgAverageDuration }}</td>
                </tr>
                <tr>
                    <td>Rate / 100 Take Off</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $ratePer100TakeOff = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['ratePer100TakeOff'];
                                $totalRatePer100TakeOff += is_numeric($ratePer100TakeOff) ? $ratePer100TakeOff:0;
                            @endphp
                            <td class="style1">{{ number_format($ratePer100TakeOff, 2) }}</td>
                        @endfor
                        <td class="style1">{{ number_format($totalRatePer100TakeOff / 12, 2) }}</td>
                </tr>
                <tr>
                    <td>Technical Incident - Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $technicalIncidentTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['technicalIncidentTotal'];
                                $totalTechnicalIncidentTotal += is_numeric($technicalIncidentTotal) ? $technicalIncidentTotal:0;
                            @endphp
                            <td class="style1">{{ round($technicalIncidentTotal) }}</td>
                        @endfor
                        <td class="style1">{{ round($totalTechnicalIncidentTotal / 12) }}</td>
                </tr>
                <tr>
                    <td>Technical Incident Rate / 100 FC</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $technicalIncidentRate = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['technicalIncidentRate'] ?? 0; // Use null coalescing operator
                                $totalTechnicalIncidentRate += is_numeric($technicalIncidentRate) ? $technicalIncidentRate : 0; // Ensure the value is numeric
                            @endphp
                            <td class="style1">{{ $technicalIncidentRate == 0 ? '0' : number_format($technicalIncidentRate, 3) }}</td>
                        @endfor
                        <td class="style1">{{ number_format($totalTechnicalIncidentRate / 12, 2) }}</td>
                </tr>
                <tr>
                    <td>Technical Cancellation - Total</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $technicalCancellationTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['technicalCancellationTotal'] ?? 0; // Use null coalescing operator
                                $totalTechnicalCancellationTotal += is_numeric($technicalCancellationTotal) ? $technicalCancellationTotal : 0; // Ensure the value is numeric
                            @endphp
                            <td class="style1">{{ round($technicalCancellationTotal) }}</td> <!-- Display rounded total for the month -->
                        @endfor
                        <td class="style1">{{ round($totalTechnicalCancellationTotal) }}</td>
                </tr>
                <tr>
                    <td>Dispatch Reliability (%)</td>
                    @for ($i = 11; $i >= 0; $i--)
                            @php
                                $dispatchReliability = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['dispatchReliability'] ?? 0; // Use null coalescing operator
                                $totalDispatchReliability += is_numeric($dispatchReliability) ? $dispatchReliability : 0; // Ensure the value is numeric
                            @endphp
                            <td class="style1">{{ number_format($dispatchReliability, 2) }}%</td> <!-- Display formatted reliability for the month -->
                        @endfor
                        <td class="style1">{{ number_format($totalDispatchReliability / 12, 2) }}%</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>