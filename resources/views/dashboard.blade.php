<x-app-layout>
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot> --}}

    <div class="py-12">
        <div class="max-w-[1440px] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div style="height: 600px; width: 100%; margin-top: 20px;">
                    <iframe id="reportContainer" src="https://app.powerbi.com/view?r=eyJrIjoiNWYxNjYxZGItZTVjZS00YmQxLWIxMTctNjU3NDU0YmM0ODI5IiwidCI6ImIxNTAxOTBhLTE2ZjMtNGZiYS04YmY2LTNhNjIwYWI3NjA3OSIsImMiOjEwfQ%3D%3D" style="width: 100%; height: 100%; border: none;" allowFullScreen></iframe>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>
