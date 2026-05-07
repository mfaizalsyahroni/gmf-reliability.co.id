{{-- aos-result.blade.php Hasil Display AOS --}}

<x-app-layout>
    {{-- <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style> --}}
@section('content')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-S...HASH..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <div class=" mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <p class="py-2">Data Aircraft Operation Summary Type: {{ $aircraftType }} pada
                {{ $month }}-{{ $year }}</p>
            <div class="flex space-x-1">
                <form action="{{ route('report.aos.export.pdf') }}" method="POST">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="aircraft_type" value="{{ $aircraftType }}">
                    <button type="submit"
                        class="block-rounded-md bg-white border border-gray-400 text-black px-3 py-1 cursor-pointer flex flex-col items-center">
                        <img src="{{ asset('images/pdf.png') }}" style="width: 16px; height: 16px; margin-bottom: 2px;">
                        PDF
                    </button>
                </form>
                <form action="{{ route('report.aos.export.excel') }}" method="post">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period ?? '' }}">
                    <input type="hidden" name="aircraft_type" value="{{ $aircraftType ?? '' }}">
                    <button type="submit"
                        class="block-rounded-md bg-white border border-gray-400 text-black px-2 py-1 cursor-pointer flex flex-col items-center">
                        <img src="{{ asset('images/excel.png') }}"
                            style="width: 16px; height: 16px; margin-bottom: 2px;">
                        Excel
                    </button>
                </form>
            </div>
        </div>



        <div class="mt-3 flow-root">
            <!-- Ganti pertahun -->
            <x-table.index>
                <x-table.thead>
                    <tr>
                        <x-table.th class="bg-secondary text-dark">Metrics</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->subMonth($i)->format('F'), 0, 3) }}</x-table.th>
                        @endfor
                        <x-table.th class="bg-secondary text-dark">Last 12 MTHS</x-table.th>
                    </tr>
                </x-table.thead>
                <x-table.tbody>
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
                        $totalTechnicalIncidentTotal = 0;
                        $totalTechnicalIncidentRate = 0;
                        $totalTechnicalCancellationTotal = 0;
                        $totalDispatchReliability = 0;
                    @endphp
                    <tr>
                        <x-table.th class="text-left">A/C In Fleet</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $acInFleet =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'acInFleet'
                                    ];
                                $totalAcInFleet += $acInFleet;
                            @endphp
                            <x-table.td>{{ $acInFleet }}</x-table.td>
                        @endfor
                        <x-table.td>{{ $totalAcInFleet / 12 }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">A/C In Service</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $acInService =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'acInService'
                                    ];
                                $totalAcInService += $acInService;
                            @endphp
                            <x-table.td>{{ number_format($acInService, 2) }}</x-table.td>
                        @endfor
                        <x-table.td>{{ number_format($totalAcInService / 12, 2) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">A/C Days In Service</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $daysInService =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'daysInService'
                                    ];
                                $totalDaysInService += $daysInService;
                            @endphp
                            <x-table.td>{{ $daysInService }}</x-table.td>
                        @endfor
                        <x-table.td>{{ round($totalDaysInService) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Flying Hours - Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $flyingHoursTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'flyingHoursTotal'
                                    ];
                                $totalFlyingHoursTotal += $flyingHoursTotal;
                            @endphp
                            <x-table.td>{{ round($flyingHoursTotal) }}</x-table.td>
                        @endfor
                        <x-table.td>{{ round($totalFlyingHoursTotal) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Revenue Flying Hours</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $revenueFlyingHours =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'revenueFlyingHours'
                                    ];
                                $totalRevenueFlyingHours += $revenueFlyingHours;
                            @endphp
                            <x-table.td>{{ round($revenueFlyingHours) }}</x-table.td>
                        @endfor
                        <x-table.td>{{ round($totalRevenueFlyingHours) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Take Off - Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $takeOffTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'takeOffTotal'
                                    ];
                                $totalTakeOffTotal += $takeOffTotal;
                            @endphp
                            <x-table.td>{{ $takeOffTotal }}</x-table.td>
                        @endfor
                        <x-table.td>{{ round($totalTakeOffTotal) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Revenue Take Off</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $revenueTakeOff =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'revenueTakeOff'
                                    ];
                                $totalRevenueTakeOff += $revenueTakeOff;
                            @endphp
                            <x-table.td>{{ $revenueTakeOff }}</x-table.td>
                        @endfor
                        <x-table.td>{{ round($totalRevenueTakeOff) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Flight Hours per Take Off - Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $flightHoursPerTakeOffTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'flightHoursPerTakeOffTotal'
                                    ];
                            @endphp
                            <x-table.td>{{ $flightHoursPerTakeOffTotal }}</x-table.td>
                        @endfor
                        <x-table.td>{{ $avgFlightHoursPerTakeOffTotal }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Revenue Flight Hours per Take Off</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $revenueFlightHoursPerTakeOff =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'revenueFlightHoursPerTakeOff'
                                    ];
                            @endphp
                            <x-table.td>{{ $revenueFlightHoursPerTakeOff }}</x-table.td>
                        @endfor
                        <x-table.td>{{ $avgRevenueFlightHoursPerTakeOff }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Daily Utilization - Flying Hours Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $dailyUtilizationFlyingHoursTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'dailyUtilizationFlyingHoursTotal'
                                    ];
                            @endphp
                            <x-table.td>{{ $dailyUtilizationFlyingHoursTotal }}</x-table.td>
                        @endfor
                        <x-table.td>{{ $avgDailyUtilizationFlyingHoursTotal }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Revenue Daily Utilization - Flying Hours Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $revenueDailyUtilizationFlyingHoursTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'revenueDailyUtilizationFlyingHoursTotal'
                                    ];
                            @endphp
                            <x-table.td>{{ $revenueDailyUtilizationFlyingHoursTotal }}</x-table.td>
                        @endfor
                        <x-table.td>{{ $avgRevenueDailyUtilizationFlyingHoursTotal }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Daily Utilization - Take Off Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $dailyUtilizationTakeOffTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'dailyUtilizationTakeOffTotal'
                                    ];
                                $totalDailyUtilizationTakeOffTotal += is_numeric($dailyUtilizationTakeOffTotal)
                                    ? $dailyUtilizationTakeOffTotal
                                    : 0;
                            @endphp
                            <x-table.td>{{ number_format($dailyUtilizationTakeOffTotal, 2) }}</x-table.td>
                        @endfor
                        <x-table.td>{{ number_format($totalDailyUtilizationTakeOffTotal / 12, 2) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Revenue Daily Utilization - Take Off Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $revenueDailyUtilizationTakeOffTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'revenueDailyUtilizationTakeOffTotal'
                                    ];
                                $totalRevenueDailyUtilizationTakeOffTotal += is_numeric(
                                    $revenueDailyUtilizationTakeOffTotal,
                                )
                                    ? $revenueDailyUtilizationTakeOffTotal
                                    : 0;
                            @endphp
                            <x-table.td>{{ number_format($revenueDailyUtilizationTakeOffTotal, 2) }}</x-table.td>
                        @endfor
                        <x-table.td>{{ number_format($totalRevenueDailyUtilizationTakeOffTotal / 12, 2) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Technical Delay - Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $technicalDelayTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'technicalDelayTotal'
                                    ];
                                $totalTechnicalDelayTotal += is_numeric($technicalDelayTotal)
                                    ? $technicalDelayTotal
                                    : 0;
                            @endphp
                            <x-table.td>{{ round($technicalDelayTotal) }}</x-table.td>
                        @endfor
                        <x-table.td>{{ round($totalTechnicalDelayTotal) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Total Duration</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $totalDuration =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'totalDuration'
                                    ];
                            @endphp
                            <x-table.td>{{ $totalDuration }}</x-table.td>
                        @endfor
                        <x-table.td>{{ $avgTotalDuration }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Average Duration</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $averageDuration =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'averageDuration'
                                    ];
                            @endphp
                            <x-table.td>{{ $averageDuration }}</x-table.td>
                        @endfor
                        <x-table.td>{{ $avgAverageDuration }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Rate / 100 Take Off</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $ratePer100TakeOff =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'ratePer100TakeOff'
                                    ];
                                $totalRatePer100TakeOff += is_numeric($ratePer100TakeOff) ? $ratePer100TakeOff : 0;
                            @endphp
                            <x-table.td>{{ number_format($ratePer100TakeOff, 2) }}</x-table.td>
                        @endfor
                        <x-table.td>{{ number_format($totalRatePer100TakeOff / 12, 2) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Technical Incident - Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $technicalIncidentTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'technicalIncidentTotal'
                                    ];
                                $totalTechnicalIncidentTotal += is_numeric($technicalIncidentTotal)
                                    ? $technicalIncidentTotal
                                    : 0;
                            @endphp
                            <x-table.td>{{ round($technicalIncidentTotal) }}</x-table.td>
                        @endfor
                        <x-table.td>{{ round($totalTechnicalIncidentTotal / 12) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">- Technical Incident Rate / 100 FC</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $technicalIncidentRate =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'technicalIncidentRate'
                                    ] ?? 0; // Use null coalescing operator
                                $totalTechnicalIncidentRate += is_numeric($technicalIncidentRate)
                                    ? $technicalIncidentRate
                                    : 0; // Ensure the value is numeric
                            @endphp
                            <x-table.td>{{ $technicalIncidentRate == 0 ? '0' : number_format($technicalIncidentRate, 3) }}</x-table.td>
                        @endfor
                        <x-table.td>{{ number_format($totalTechnicalIncidentRate / 12, 2) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Technical Cancellation - Total</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $technicalCancellationTotal =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'technicalCancellationTotal'
                                    ] ?? 0; // Use null coalescing operator
                                $totalTechnicalCancellationTotal += is_numeric($technicalCancellationTotal)
                                    ? $technicalCancellationTotal
                                    : 0; // Ensure the value is numeric
                            @endphp
                            <x-table.td>{{ round($technicalCancellationTotal) }}</x-table.td>
                            <!-- Display rounded total for the month -->
                        @endfor
                        <x-table.td>{{ round($totalTechnicalCancellationTotal) }}</x-table.td>
                    </tr>
                    <tr>
                        <x-table.th class="text-left">Dispatch Reliability (%)</x-table.th>
                        @for ($i = 11; $i >= 0; $i--)
                            @php
                                $dispatchReliability =
                                    $reportData[\Carbon\Carbon::parse($period)->subMonth($i)->format('Y-m')][
                                        'dispatchReliability'
                                    ] ?? 0; // Use null coalescing operator
                                $totalDispatchReliability += is_numeric($dispatchReliability)
                                    ? $dispatchReliability
                                    : 0; // Ensure the value is numeric
                            @endphp
                            <x-table.td>{{ number_format($dispatchReliability, 2) }}%</x-table.td>
                            <!-- Display formatted reliability for the month -->
                        @endfor
                        <x-table.td>{{ number_format($totalDispatchReliability / 12, 2) }}%</x-table.td>
                    </tr>
                </x-table.tbody>
            </x-table.index>
        </div>
    </div>

</x-app-layout>

@endsection
