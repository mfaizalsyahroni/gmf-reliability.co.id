        <div class="py-3 h-full min-h-screen" style="background-color: #bdd8f4;">
            <div class="px-3">

                <p class="fw-bold fs-1 text-center">Pilot Report and Technical Delay</p>
{{-- 
                <form id="form-prtd-pdf" action="{{ route('report.prtd.pdf') }}" method="GET" target="_blank">
                    <input type="hidden" name="period" value="{{ request('period') }}">
                    <input type="hidden" name="operator" value="{{ request('operator') }}">
                    <input type="hidden" name="aircraft_type" value="{{ request('aircraft_type') }}">
                </form>

                <form id="form-prtd-excel" action="{{ route('report.prtd.excel') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="period" value="{{ request('period') }}">
                    <input type="hidden" name="operator" value="{{ request('operator') }}">
                    <input type="hidden" name="aircraft_type" value="{{ request('aircraft_type') }}">
                </form> --}}


                <form id="form-prtd" action="{{ route('report.prtd.store') }}" method="POST">
                    @csrf
                    <div class="d-flex flex-column gap-3">

                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center gap-1">
                                <label class="fw-bold text-dark text-nowrap small mb-0">Periode:</label>
                                <select name="period" class="form-select shadow-sm" style="font-size: 12px;">
                                    <option value="">
                                        2024-09
                                    </option>
                                    @foreach ($periods as $p)
                                        <option value="{{ $p['original'] }}"
                                            {{ isset($period) && $period == $p['original'] ? 'selected' : '' }}>
                                            {{ $p['formatted'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <label class="fw-bold text-dark text-nowrap small mb-0">Operator:</label>
                                <select id="operator-dropdown" name="operator" class="form-select shadow-sm"
                                    style="width: 130px; font-size: 12px;">
                                    <option value="">Operator Type</option>
                                    @foreach ($operators as $type)
                                        <option value="{{ $type->Operator }}">{{ $type->Operator }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <label class="fw-bold text-dark text-nowrap small mb-0">ACType:</label>
                                <select id="aircraft-type-dropdown" name="aircraft_type" class="form-select shadow-sm"
                                    style="width: 120px; font-size: 12px;">
                                    <option value="">Aircraft Type</option>
                                    @foreach ($aircraftTypes as $type)
                                        <option value="{{ $type->ACType }}">{{ $type->ACType }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary fw-bold shadow-sm"
                                style="background-color: #707783; width: 120px; font-size: 12px;">
                                Display Report
                            </button>

                            {{-- <button type="submit" form="form-pdf"
                                class="btn btn-outline-danger d-flex align-items-center gap-1 px-2 py-1"
                                style="width: 55px; height: 31px; font-size: 12px;">
                                <i class="bi bi-file-pdf"></i>
                                <span class="small fw-bold">PDF</span>
                            </button>

                            <button type="submit" form="form-excel"
                                class="btn btn-outline-success d-flex align-items-center gap-1 px-2 py-1"
                                style="width: 60px; height: 31px; font-size: 12px;">
                                <i class="fas fa-file-excel"></i>
                                <span class="small fw-bold">EXCEL</span>
                            </button> --}}
                        </div>

                        {{-- div aos-result.blade.php move here --}}

                        {{-- div aos-result.blade.php move here --}}
                        @if (isset($reportData))
                            @include('report.prtd-table')
                        @endif





                    </div>
                </form>
            </div>
        </div>
