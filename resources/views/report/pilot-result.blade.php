<x-app-layout>
    <div class=" mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <p class="y-2">Pilot Report AC Type {{ $aircraftType }}, bulan {{ \Carbon\Carbon::parse($period)->format('F Y') }}</p>
            <div class="flex space-x-1">
                <form action="#" method="POST">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="aircraft_type" value="{{ $aircraftType }}">
                    <button type="submit" class="block rounded-md bg-gray-800 px-3 py-2 text-center text-sm text-white shadow-sm hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                        Export to PDF
                    </button>
                </form>
                <form action="#" method="POST">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="aircraft_type" value="{{ $aircraftType }}">
                    <button type="submit" class="block rounded-md bg-gray-800 px-3 py-2 text-center text-sm text-white shadow-sm hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                        Export to Excel
                    </button>
                </form> 
            </div>
        </div>

        {{-- Pilot Report --}}
        <div class=" flow-root px-4 bg-green-200">
            <h1 class="text-center mb-3 mt-3">Pilot Report</h1>
            <x-table.index>
                <x-table.thead>
                    <tr>
                        <x-table.th colspan="2">Total Flight Hours</x-table.th>
                        <x-table.th>{{ round($flyingHours2Before) }}</x-table.th>
                        <x-table.th>{{ round($flyingHoursBefore) }}</x-table.th>
                        <x-table.th>{{ round($flyingHoursTotal) }}</x-table.th>
                        <x-table.th>{{ round($fh3Last) }}</x-table.th>
                        <x-table.th>{{ round($fh12Last) }}</x-table.th>
                        <x-table.th colspan="8"></x-table.th>
                    </tr>
                    <tr>
                        <x-table.th colspan="2" rowspan="2">ATA CHAPTER</x-table.th>
                        <x-table.th rowspan="2">{{ substr(\Carbon\Carbon::parse($period)->subMonths(2)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th rowspan="2">{{ substr(\Carbon\Carbon::parse($period)->subMonth(1)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th rowspan="2">{{ substr(\Carbon\Carbon::parse($period)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>Last 3</x-table.th>
                        <x-table.th>Last 12</x-table.th>
                        <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->subMonths(2)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->subMonth(1)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>3 Months</x-table.th>
                        <x-table.th>12 Months</x-table.th>
                        <x-table.th>ALERT</x-table.th>
                        <x-table.th>ALERT</x-table.th>
                        <x-table.th rowspan="2">TREND</x-table.th>
                    </tr>
                    <tr>
                        <x-table.th>Months</x-table.th>
                        <x-table.th>Months</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>LEVEL</x-table.th>
                        <x-table.th>STATUS</x-table.th>
                    </tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($tblAta as $item)
                    <tr>
                        <x-table.th>{{ $item->ATA }}</x-table.th>
                        <x-table.th>{{ $item->ATA_DESC }}</x-table.th>
                        <x-table.td><a href="#">{{ $pirepCountTwoMonthsAgo }}</a></x-table.td>
                        <x-table.td><a href="#">{{ $pirepCountBefore }}</a></x-table.td>
                        <x-table.td><a href="#">{{ $pirepCount }}</a></x-table.td>
                        <x-table.td>{{ $pirep3Month }}</x-table.td>
                        <x-table.td>{{ $pirep12Month }}</x-table.td>
                        <x-table.td>{{ number_format($pirep2Rate,2) }}</x-table.td>
                        <x-table.td>{{ number_format($pirep1Rate,2) }}</x-table.td>
                        <x-table.td>{{ number_format($pirepRate,2) }}</x-table.td>
                        <x-table.td>{{ number_format($pirepRate3Month,2) }}</x-table.td>
                        <x-table.td>{{ number_format($pirepRate12Month, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($pirepAlertLevel,2) }}</x-table.td>
                        <x-table.td>{{ $pirepAlertStatus }}</x-table.td>
                        <x-table.td>{{ $pirepTrend }}</x-table.td>
                    </tr>  
                    @endforeach
                </x-table.tbody>
            </x-table.index>
        </div>

        {{-- Maintenance Report --}}
        {{-- <div class="mt-4 flow-root px-4 bg-green-200">
            <h1 class="text-center mb-3 mt-3">Maintenance Report</h1>
            <x-table.index>
                <x-table.thead>
                    <tr>
                        <x-table.th colspan="2">Total Flight Hours</x-table.th>
                        <x-table.th>{{ round($flyingHours2Before) }}</x-table.th>
                        <x-table.th>{{ round($flyingHoursBefore) }}</x-table.th>
                        <x-table.th>{{ round($flyingHoursTotal) }}</x-table.th>
                        <x-table.th>{{ round($fh3Last) }}</x-table.th>
                        <x-table.th>{{ round($fh12Last) }}</x-table.th>
                        <x-table.th colspan="8"></x-table.th>
                    </tr>
                    <tr>
                        <x-table.th colspan="2" rowspan="2">ATA CHAPTER</x-table.th>
                        <x-table.th rowspan="2">{{ substr(\Carbon\Carbon::parse($period)->subMonths(2)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th rowspan="2">{{ substr(\Carbon\Carbon::parse($period)->subMonth(1)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th rowspan="2">{{ substr(\Carbon\Carbon::parse($period)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>Last 3</x-table.th>
                        <x-table.th>Last 12</x-table.th>
                        <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->subMonths(2)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->subMonth(1)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>3 Months</x-table.th>
                        <x-table.th>12 Months</x-table.th>
                        <x-table.th>ALERT</x-table.th>
                        <x-table.th>ALERT</x-table.th>
                        <x-table.th rowspan="2">TREND</x-table.th>
                    </tr>
                    <tr>
                        <x-table.th>Months</x-table.th>
                        <x-table.th>Months</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>LEVEL</x-table.th>
                        <x-table.th>STATUS</x-table.th>
                    </tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($tblAta as $item)
                    <tr>
                        <x-table.th>{{ $item->ATA }}</x-table.th>
                        <x-table.th>{{ $item->ATA_DESC }}</x-table.th>
                        <x-table.td><a href="#">{{ $marepCountTwoMonthsAgo }}</a></x-table.td>
                        <x-table.td><a href="#">{{ $marepCountBefore }}</a></x-table.td>
                        <x-table.td><a href="#">{{ $marepCount }}</a></x-table.td>
                        <x-table.td>{{ $marep3Month }}</x-table.td>
                        <x-table.td>{{ $marep12Month }}</x-table.td>
                        <x-table.td>{{ number_format($marep2Rate, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($marep1Rate, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($marepRate, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($marepRate3Month, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($marepRate12Month, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($marepAlertLevel, 2) }}</x-table.td>
                        <x-table.td>{{ $marepAlertStatus }}</x-table.td>
                        <x-table.td>{{ $marepTrend }}</x-table.td>
                    </tr>  
                    @endforeach
                </x-table.tbody>
            </x-table.index>
        </div> --}}

        {{-- Delay Report --}}
        {{-- <div class="mt-4 flow-root px-4 bg-green-200">
            <h1 class="text-center mb-3 mt-3">Technical Delay Report</h1>
            <x-table.index>
                <x-table.thead>
                    <tr>
                        <x-table.th colspan="2">Total Flight Hours</x-table.th>
                        <x-table.th>{{ round($flyingHours2Before) }}</x-table.th>
                        <x-table.th>{{ round($flyingHoursBefore) }}</x-table.th>
                        <x-table.th>{{ round($flyingHoursTotal) }}</x-table.th>
                        <x-table.th>{{ round($fh3Last) }}</x-table.th>
                        <x-table.th>{{ round($fh12Last) }}</x-table.th>
                        <x-table.th colspan="8"></x-table.th>
                    </tr>
                    <tr>
                        <x-table.th colspan="2" rowspan="2">ATA CHAPTER</x-table.th>
                        <x-table.th rowspan="2">{{ substr(\Carbon\Carbon::parse($period)->subMonths(2)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th rowspan="2">{{ substr(\Carbon\Carbon::parse($period)->subMonth(1)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th rowspan="2">{{ substr(\Carbon\Carbon::parse($period)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>Last 3</x-table.th>
                        <x-table.th>Last 12</x-table.th>
                        <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->subMonths(2)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->subMonth(1)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>{{ substr(\Carbon\Carbon::parse($period)->format('F'), 0, 3) }}</x-table.th>
                        <x-table.th>3 Months</x-table.th>
                        <x-table.th>12 Months</x-table.th>
                        <x-table.th>ALERT</x-table.th>
                        <x-table.th>ALERT</x-table.th>
                        <x-table.th rowspan="2">TREND</x-table.th>
                    </tr>
                    <tr>
                        <x-table.th>Months</x-table.th>
                        <x-table.th>Months</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>RATE</x-table.th>
                        <x-table.th>LEVEL</x-table.th>
                        <x-table.th>STATUS</x-table.th>
                    </tr>
                </x-table.thead>
                <x-table.tbody>
                    @foreach ($tblAta as $item)
                    <tr>
                        <x-table.th>{{ $item->ATA }}</x-table.th>
                        <x-table.th>{{ $item->ATA_DESC }}</x-table.th>
                        <x-table.td><a href="#">{{ $delayCountTwoMonthsAgo }}</a></x-table.td>
                        <x-table.td><a href="#">{{ $delayCountBefore }}</a></x-table.td>
                        <x-table.td><a href="#">{{ $delayCount }}</a></x-table.td>
                        <x-table.td>{{ $delay3Month }}</x-table.td>
                        <x-table.td>{{ $delay12Month }}</x-table.td>
                        <x-table.td>{{ number_format($delay2Rate, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($delay1Rate, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($delayRate, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($delayRate3Month, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($delayRate12Month, 2) }}</x-table.td>
                        <x-table.td>{{ number_format($delayAlertLevel, 2) }}</x-table.td>
                        <x-table.td>{{ $delayAlertStatus }}</x-table.td>
                        <x-table.td>{{ $delayTrend }}</x-table.td>
                    </tr>  
                    @endforeach
                </x-table.tbody>
            </x-table.index>
        </div> --}}
    </div>
</x-app-layout>