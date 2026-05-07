<h1 class="text-3xl font-bold mb-8 text-center">Pilot Report And Technical Delay</h1>

<div class="container mx-auto">
    <form action="{{ url('/report/pilot') }}" method="POST">
        @csrf
        <div class="flex space-x-4">

            <div class="flex items-center space-x-2">
                <label for="operator" class="mb-0">Periode:</label>
                <select id="period" name="period"
                    class="form-select text-black rounded-md border border-white-600 focus:outline-none focus:ring focus:ring-blue-300">
                    <option value="" class="text-gray-500">Select Periode</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period['original'] }}">{{ $period['formatted'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label for="operator" class="mb-0">Operator:</label>
                <select id="operator-dropdown" name="operator"
                    class="form-select text-black rounded-md border border-white-600 focus:outline-none focus:ring focus:ring-blue-300">
                    <option value="" class="text-gray-500">Select Operator</option>
                    @foreach ($operators as $operator)
                        <option value="{{ $operator->Operator }}">{{ $operator->Operator }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label for="aircraft_type" class="mb-0">ACType:</label>
                <select name="aircraft_type" class="form-select text-black rounded-md border border-white-600 focus:outline-none focus:ring focus:ring-blue-300">
                    <option value="" class="text-gray-500">Select Aircraft Type</option>
                    @foreach ($aircraftTypes as $type)
                        <option value="{{ $type->ACTYPE }}">{{ $type->ACTYPE }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex space-x-1">
                &nbsp;
                <x-third-button type="submit">
                    Display Report
                </x-third-button>

            <x-third-button type="submit" name="export_excel"
                style="background-color: white; border: 1px solid grey; color: black; padding: 8px 16px; cursor: pointer; display: flex; flex-direction: column; align-items: center;">
                <img src="{{ asset('images/excel.png') }}" style="width: 16px; height: 16px; margin-bottom: 4px;">
                Excel
            </x-third-button>
            <x-third-button type="submit" name="export_excel"
                style="background-color: white; border: 1px solid grey; color: black; padding: 8px 16px; cursor: pointer; display: flex; flex-direction: column; align-items: center;">
                <img src="{{ asset('images/pdf.png') }}" style="width: 16px; height: 16px; margin-bottom: 4px;">
                PDF
            </x-third-button>
            </div> 
        </div>
    </form>
</div>