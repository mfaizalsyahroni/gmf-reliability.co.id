<!--Views report.blade.php-->
<x-app-layout>



    <div class="flex flex-1" style="min-height: calc(100vh - 64px)">
        <!-- Sidebar -->
        <div class="w-[17%] bg-white p-4 border-r border-gray-300">
            <div class="mb-4">
                <a href="/report" class="font-bold border-b-2 border-black w-full py-2">All Report</a>
            </div>
            <ul class="space-y-2">
                <li><a href="" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"
                        data-url="{{ route('report.aos.index') }}"><span class="mr-2 text-xl">✈</span> Aircraft Operation
                        Summary</a></li>
                <li><a href="" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"
                        data-url="{{ route('report.pilot.index') }}"><span class="mr-2 text-xl">✈</span> Pilot Report
                        And Technical Delay</a></li>
                <li><a href="" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"
                        data-url="{{ route('report.cumulative') }}"><span class="mr-2 text-xl">✈</span> Cumulative
                        Flight Hours and Take Off</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> Reliability Graph</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> Engine Operation Summary</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> Engine Removal & Shutdown</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> Weekly Reliability Report</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> Summary Report</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> Graph ATA Pilot</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> Graph ATA Delay</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> APU Operation Summary</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> APU Removal</a></li>
                <li><a href="#" class="flex items-center text-blue-500 hover:text-blue-900 sidebar-item"><span
                            class="mr-2 text-xl">✈</span> Cabin Reliability Report</a></li>
            </ul>
        </div>


        <!-- Main Content Area -->
        <div class="flex-1 bg-blue-200" id="main-content">
            <h1 class="text-3xl font-bold mb-4 p-6">Main Content Area</h1>
            <p class="p-6">This is where the main content will go.</p>
        </div>



    </div>
    <script src="{{ asset('js/report.js') }}"></script> <!-- Script js terletak di public/js/report.js -->


</x-app-layout>
