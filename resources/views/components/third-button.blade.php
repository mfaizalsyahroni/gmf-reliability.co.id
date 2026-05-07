@props([
    'type' => 'submit',
    'as' => 'button'
])

@php
    $classes = "block rounded-md bg-gray-800 px-3 py-2 text-center text-sm font-medium text-white shadow-sm hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
@endphp

@if ($as == 'button')
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
            {{ $slot }}
    </button>
@else
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>

@endif