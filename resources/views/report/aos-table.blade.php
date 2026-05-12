    <style>
        .aos-table th,
        .aos-table td {
            padding: 3px 6px !important;
            font-size: 11px !important;
            white-space: nowrap;
        }
        .aos-table thead th {
            background-color: #e4eaf0 !important;
            color: #333 !important;
        }
    </style>

<div class="table-responsive">
    <p class="text-center">Data Aircraft Operation Summary  {{ $operator }}  {{ $aircraftType }}  {{ $month }}-{{ $year }}</p>

    <div class="flow-root">
        <div class="aos-table">
                <x-table.index>
                    <x-table.thead>
                        <tr>
                            <x-table.th>Metrics</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->subMonth($i)->format('F'), 0, 3) }}</x-table.th>
                            @endfor
                            <x-table.th>Last 12 MTHS</x-table.th>
                        </tr>
                    </x-table.thead>
                    <x-table.tbody>
                        @php
                            $totalAcInFleet = 0;
                            $totalAcInService = 0;
                            $totalDaysInService = 0;
                            $totalFlyingHoursTotal = 0;
                            $totalRevenueFlyingHours = 0;
                            $totalTakeOffTotal = 0;
                            $totalRevenueTakeOff = 0;
                            $totalDailyUtilizationTakeOffTotal = 0;
                            $totalRevenueDailyUtilizationTakeOffTotal = 0;
                            $totalTechnicalDelayTotal = 0;
                            $totalRatePer100TakeOff = 0;
                            $totalTechnicalIncidentTotal = 0;
                            $totalTechnicalIncidentRate = 0;
                            $totalTechnicalCancellationTotal = 0;
                            $totalDispatchReliability = 0;
                        @endphp

                        {{-- A/C In Fleet --}}
                        <tr>
                            <x-table.th class="text-left">A/C In Fleet</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $acInFleet = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['acInFleet'];
                                    $totalAcInFleet += $acInFleet;
                                @endphp
                                <x-table.td>{{ $acInFleet }}</x-table.td>
                            @endfor
                            <x-table.td>{{ $totalAcInFleet / 12 }}</x-table.td>
                        </tr>

                        {{-- A/C In Service --}}
                        <tr>
                            <x-table.th class="text-left">A/C In Service</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $acInService = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['acInService'];
                                    $totalAcInService += $acInService;
                                @endphp
                                <x-table.td>{{ number_format($acInService, 2) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ number_format($totalAcInService / 12, 2) }}</x-table.td>
                        </tr>

                        {{-- A/C Days In Service --}}
                        <tr>
                            <x-table.th class="text-left">A/C Days In Service</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $daysInService = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['daysInService'];
                                    $totalDaysInService += $daysInService;
                                @endphp
                                <x-table.td>{{ $daysInService }}</x-table.td>
                            @endfor
                            <x-table.td>{{ round($totalDaysInService) }}</x-table.td>
                        </tr>

                        {{-- Flying Hours Total --}}
                        <tr>
                            <x-table.th class="text-left">Flying Hours - Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $flyingHoursTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['flyingHoursTotal'];
                                    $totalFlyingHoursTotal += $flyingHoursTotal;
                                @endphp
                                <x-table.td>{{ round($flyingHoursTotal) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ round($totalFlyingHoursTotal) }}</x-table.td>
                        </tr>

                        {{-- Revenue Flying Hours --}}
                        <tr>
                            <x-table.th class="text-left">Revenue Flying Hours</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $revenueFlyingHours = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueFlyingHours'];
                                    $totalRevenueFlyingHours += $revenueFlyingHours;
                                @endphp
                                <x-table.td>{{ round($revenueFlyingHours) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ round($totalRevenueFlyingHours) }}</x-table.td>
                        </tr>

                        {{-- Take Off Total --}}
                        <tr>
                            <x-table.th class="text-left">Take Off - Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $takeOffTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['takeOffTotal'];
                                    $totalTakeOffTotal += $takeOffTotal;
                                @endphp
                                <x-table.td>{{ $takeOffTotal }}</x-table.td>
                            @endfor
                            <x-table.td>{{ round($totalTakeOffTotal) }}</x-table.td>
                        </tr>

                        {{-- Revenue Take Off --}}
                        <tr>
                            <x-table.th class="text-left">Revenue Take Off</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $revenueTakeOff = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueTakeOff'];
                                    $totalRevenueTakeOff += $revenueTakeOff;
                                @endphp
                                <x-table.td>{{ $revenueTakeOff }}</x-table.td>
                            @endfor
                            <x-table.td>{{ round($totalRevenueTakeOff) }}</x-table.td>
                        </tr>

                        {{-- Flight Hours per Take Off --}}
                        <tr>
                            <x-table.th class="text-left">Flight Hours per Take Off - Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                <x-table.td>{{ $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['flightHoursPerTakeOffTotal'] }}</x-table.td>
                            @endfor
                            <x-table.td>{{ $avgFlightHoursPerTakeOffTotal }}</x-table.td>
                        </tr>

                        {{-- Revenue Flight Hours per Take Off --}}
                        <tr>
                            <x-table.th class="text-left">Revenue Flight Hours per Take Off</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                <x-table.td>{{ $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueFlightHoursPerTakeOff'] }}</x-table.td>
                            @endfor
                            <x-table.td>{{ $avgRevenueFlightHoursPerTakeOff }}</x-table.td>
                        </tr>

                        {{-- Daily Utilization Flying Hours --}}
                        <tr>
                            <x-table.th class="text-left">Daily Utilization - Flying Hours Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                <x-table.td>{{ $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['dailyUtilizationFlyingHoursTotal'] }}</x-table.td>
                            @endfor
                            <x-table.td>{{ $avgDailyUtilizationFlyingHoursTotal }}</x-table.td>
                        </tr>

                        {{-- Revenue Daily Utilization Flying Hours --}}
                        <tr>
                            <x-table.th class="text-left">Revenue Daily Utilization - Flying Hours Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                <x-table.td>{{ $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueDailyUtilizationFlyingHoursTotal'] }}</x-table.td>
                            @endfor
                            <x-table.td>{{ $avgRevenueDailyUtilizationFlyingHoursTotal }}</x-table.td>
                        </tr>

                        {{-- Daily Utilization Take Off --}}
                        <tr>
                            <x-table.th class="text-left">Daily Utilization - Take Off Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $dailyUtilizationTakeOffTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['dailyUtilizationTakeOffTotal'];
                                    $totalDailyUtilizationTakeOffTotal += is_numeric($dailyUtilizationTakeOffTotal) ? $dailyUtilizationTakeOffTotal : 0;
                                @endphp
                                <x-table.td>{{ number_format($dailyUtilizationTakeOffTotal, 2) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ number_format($totalDailyUtilizationTakeOffTotal / 12, 2) }}</x-table.td>
                        </tr>

                        {{-- Revenue Daily Utilization Take Off --}}
                        <tr>
                            <x-table.th class="text-left">Revenue Daily Utilization - Take Off Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $revenueDailyUtilizationTakeOffTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['revenueDailyUtilizationTakeOffTotal'];
                                    $totalRevenueDailyUtilizationTakeOffTotal += is_numeric($revenueDailyUtilizationTakeOffTotal) ? $revenueDailyUtilizationTakeOffTotal : 0;
                                @endphp
                                <x-table.td>{{ number_format($revenueDailyUtilizationTakeOffTotal, 2) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ number_format($totalRevenueDailyUtilizationTakeOffTotal / 12, 2) }}</x-table.td>
                        </tr>

                        {{-- Technical Delay --}}
                        <tr>
                            <x-table.th class="text-left">Technical Delay - Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $technicalDelayTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['technicalDelayTotal'];
                                    $totalTechnicalDelayTotal += is_numeric($technicalDelayTotal) ? $technicalDelayTotal : 0;
                                @endphp
                                <x-table.td>{{ round($technicalDelayTotal) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ round($totalTechnicalDelayTotal) }}</x-table.td>
                        </tr>

                        {{-- Total Duration --}}
                        <tr>
                            <x-table.th class="text-left">Total Duration</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                <x-table.td>{{ $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['totalDuration'] }}</x-table.td>
                            @endfor
                            <x-table.td>{{ $avgTotalDuration }}</x-table.td>
                        </tr>

                        {{-- Average Duration --}}
                        <tr>
                            <x-table.th class="text-left">Average Duration</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                <x-table.td>{{ $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['averageDuration'] }}</x-table.td>
                            @endfor
                            <x-table.td>{{ $avgAverageDuration }}</x-table.td>
                        </tr>

                        {{-- Rate / 100 Take Off --}}
                        <tr>
                            <x-table.th class="text-left">Rate / 100 Take Off</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $ratePer100TakeOff = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['ratePer100TakeOff'];
                                    $totalRatePer100TakeOff += is_numeric($ratePer100TakeOff) ? $ratePer100TakeOff : 0;
                                @endphp
                                <x-table.td>{{ number_format($ratePer100TakeOff, 2) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ number_format($totalRatePer100TakeOff / 12, 2) }}</x-table.td>
                        </tr>

                        {{-- Technical Incident Total --}}
                        <tr>
                            <x-table.th class="text-left">Technical Incident - Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $technicalIncidentTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['technicalIncidentTotal'];
                                    $totalTechnicalIncidentTotal += is_numeric($technicalIncidentTotal) ? $technicalIncidentTotal : 0;
                                @endphp
                                <x-table.td>{{ round($technicalIncidentTotal) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ round($totalTechnicalIncidentTotal / 12) }}</x-table.td>
                        </tr>

                        {{-- Technical Incident Rate --}}
                        <tr>
                            <x-table.th class="text-left">- Technical Incident Rate / 100 FC</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $technicalIncidentRate = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['technicalIncidentRate'] ?? 0;
                                    $totalTechnicalIncidentRate += is_numeric($technicalIncidentRate) ? $technicalIncidentRate : 0;
                                @endphp
                                <x-table.td>{{ $technicalIncidentRate == 0 ? '0' : number_format($technicalIncidentRate, 3) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ number_format($totalTechnicalIncidentRate / 12, 2) }}</x-table.td>
                        </tr>

                        {{-- Technical Cancellation --}}
                        <tr>
                            <x-table.th class="text-left">Technical Cancellation - Total</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $technicalCancellationTotal = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['technicalCancellationTotal'] ?? 0;
                                    $totalTechnicalCancellationTotal += is_numeric($technicalCancellationTotal) ? $technicalCancellationTotal : 0;
                                @endphp
                                <x-table.td>{{ round($technicalCancellationTotal) }}</x-table.td>
                            @endfor
                            <x-table.td>{{ round($totalTechnicalCancellationTotal) }}</x-table.td>
                        </tr>

                        {{-- Dispatch Reliability --}}
                        <tr>
                            <x-table.th class="text-left">Dispatch Reliability (%)</x-table.th>
                            @for ($i = 11; $i >= 0; $i--)
                                @php
                                    $dispatchReliability = $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')]['dispatchReliability'] ?? 0;
                                    $totalDispatchReliability += is_numeric($dispatchReliability) ? $dispatchReliability : 0;
                                @endphp
                                <x-table.td>{{ number_format($dispatchReliability, 2) }}%</x-table.td>
                            @endfor
                            <x-table.td>{{ number_format($totalDispatchReliability / 12, 2) }}%</x-table.td>
                        </tr>

                    </x-table.tbody>
                </x-table.index>
        </div>
    </div>
</div>