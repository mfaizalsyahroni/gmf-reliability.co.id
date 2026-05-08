<!--Views aos-content.blade.php-->
{{-- @extends('layouts.aps') --}}


{{-- @section('content') --}}
{{-- <!DOCTYPE html>
<html lang="en"> --}}

{{-- <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        <h1 class="text-3xl font-bold mb-8 text-center">Aircraft Operation Summary</h1>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-S...HASH..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/aos.js') }}" defer></script> <!-- Script js terletak di public/js/report.js -->
</head>


<body> --}}

{{-- Bootstrap CSS --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-S...HASH..." crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- Filter Form --}}
<div class="py-3 h-full min-h-screen" style="background-color: #bdd8f4;"> 
    <div class="px-3"> 
        <p class="fw-bold fs-1 text-center">Aircraft Operations Summary</p>
        <form action="{{ url('/report/aos') }}" method="POST">
            @csrf
            <div class="d-flex flex-column gap-3">

                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center gap-1">
                        <label class="fw-bold text-dark text-nowrap small mb-0">Periode:</label>
                        <select name="period" class="form-select shadow-sm" style="font-size: 12px;">
                            {{-- Bulan hari ini --}}
                            <option value="">2024-09</option>
                            @foreach ($periods as $period)
                                <option value="{{ $period['original'] }}">{{ $period['formatted'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <label class="fw-bold text-dark text-nowrap small mb-0">Operator:</label>
                        <select id="operator-dropdown" name="operator" class="form-select shadow-sm"
                            style="width: 130px; font-size: 12px;">
                            <option value="">Operator Type</option>
                            @foreach ($operators as $operator)
                                <option value="{{ $operator->Operator }}">{{ $operator->Operator }}</option>
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
                    <button type="submit" name="export_pdf"
                        class="btn btn-outline-danger d-flex align-items-center gap-1 px-2 py-1" style="width: 55px; height: 31px; font-size: 12px;">
                        <i class="bi bi-file-pdf"></i>
                        <span class="small fw-bold">PDF</span>
                    </button>
                    <button type="submit" name="export_excel"
                        class="btn btn-outline-success d-flex align-items-center gap-1 px-2 py-1" style="width: 60px; height: 31px; font-size: 12px;">
                        <i class="fas fa-file-excel"></i>
                        <span class="small fw-bold">EXCEL</span>
                    </button>
                </div>

                {{-- <div class="d-flex flex-wrap gap-2" style="margin-left: 60px"> --}}
                {{-- <button type="submit" class="btn btn-dark fw-bold px-4 shadow-sm" style="background-color: #1e293b;">
                    Display Report
                </button>
                <button type="submit" name="export_pdf"
                    class="btn btn-white border shadow-sm px-3 d-flex align-items-center gap-2">
                    <img src="{{ asset('images/pdf.png') }}" width="20">
                    <span class="small fw-bold">PDF</span>
                </button>
                <button type="submit" name="export_excel"
                    class="btn btn-white border shadow-sm px-3 d-flex align-items-center gap-2">
                    <img src="{{ asset('images/excel.png') }}" width="20">
                    <span class="small fw-bold">EXCEL</span>
                </button> --}}
                {{-- </div> --}}

            </div>
        </form>
    </div>
</div>


{{-- </body> --}}

<!-- Tambahkan script untuk handle perubahan operator -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('operator-dropdown').addEventListener('change', function() {
            console.log('Operator changed');
            const operator = this.value;
            const aircraftTypeDropdown = document.getElementById('aircraft-type-dropdown');

            // Kosongkan dropdown AC Type
            aircraftTypeDropdown.innerHTML = '<option value="">Select Aircraft Type</option>';

            if (operator) {
                // Kirim permintaan AJAX
                fetch(`/get-aircraft-types?operator=${operator}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log(data); // Debugging
                        data.forEach(type => {
                            const option = document.createElement('option');
                            option.value = type.ACType;
                            option.textContent = type.ACType;
                            aircraftTypeDropdown.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching aircraft types:', error));
            }
        });
    });
</script>




{{-- @endsection --}}

{{-- </html> --}}
