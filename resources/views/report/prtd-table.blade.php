<style>
    .prtd-table th,
    .prtd-table td {
        padding: 3px 6px;
        font-size: 11px;
        white-space: nowrap;
    }
    .prtd-table thead th {
        background-color: #e4eaf0;
    }
</style>

<div class="table-responsive mt-3">

    <p class="text-center fw-bold">
        Pilot Report And Technical Delay
        {{ $period ? \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') : '' }}
    </p>

    <div class="prtd-table">
        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>Metrics</th>
                    @foreach($months as $m)
                        <th>{{ $m->format('M') }}</th>
                    @endforeach
                    <th>Last 12 MTHS</th>
                </tr>
            </thead>

            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <th class="text-left">{{ $row['label'] }}</th>

                        @foreach($months as $m)
                            @php
                                $key   = $m->format('Y-m');
                                $value = $reportData[$key][$row['field']] ?? 0;
                            @endphp
                            <td>
                                @switch($row['format'])
                                    @case('decimal2')
                                        {{ number_format($value, 2) }}
                                        @break
                                    @case('hhmm')
                                        @php
                                            $h     = floor($value);
                                            $m_val = round(($value - $h) * 60);
                                        @endphp
                                        {{ $h }}h {{ str_pad($m_val, 2, '0', STR_PAD_LEFT) }}m
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
                                @case('hhmm')
                                    @php
                                        $h     = floor($row['summary']);
                                        $m_val = round(($row['summary'] - $h) * 60);
                                    @endphp
                                    {{ $h }}h {{ str_pad($m_val, 2, '0', STR_PAD_LEFT) }}m
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