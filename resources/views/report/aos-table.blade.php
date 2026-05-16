<style>
    .aos-table th,
    .aos-table td {
        padding: 3px 6px;
        font-size: 11px;
        white-space: nowrap;
    }

    .aos-table thead th {
        background-color: #e4eaf0;
    }
</style>

<div class="table-responsive">

    <p class="text-center">
        Data Aircraft Operation Summary
        {{ $operator }}
        {{ $aircraftType }}
        {{ $month }}-{{ $year }}
    </p>

    <div class="aos-table">

        <table class="table table-bordered">

            <thead>

                <tr>

                    <th>Metrics</th>

                    @foreach($months as $month)
                        <th>{{ $month->format('M') }}</th>
                    @endforeach

                    <th>Last 12 MTHS</th>

                </tr>

            </thead>

            <tbody>

                @foreach($rows as $row)

                    <tr>

                        <th class="text-left">
                            {{ $row['label'] }}
                        </th>

                        @foreach($months as $month)

                            @php
                                $key = $month->format('Y-m');
                                $value = $reportData[$key][$row['field']] ?? 0;
                            @endphp

                            <td>

                                @switch($row['format'])

                                    @case('decimal2')
                                        {{ number_format($value, 2) }}
                                        @break

                                    @case('decimal3')
                                        {{ number_format($value, 3) }}
                                        @break

                                    @case('percent')
                                        {{ number_format($value, 2) }}%
                                        @break

                                    @case('round')
                                        {{ round($value) }}
                                        @break

                                    @default
                                        {{ $value }}

                                @endswitch

                            </td>

                        @endforeach

                        <td>

                            @switch($row['format'])

                                @case('decimal2')
                                    {{ number_format($row['summary'], 2) }}
                                    @break

                                @case('decimal3')
                                    {{ number_format($row['summary'], 3) }}
                                    @break

                                @case('percent')
                                    {{ number_format($row['summary'], 2) }}%
                                    @break

                                @case('round')
                                    {{ round($row['summary']) }}
                                    @break

                                @default
                                    {{ $row['summary'] }}

                            @endswitch

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>