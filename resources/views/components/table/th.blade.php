@props([
    'class' => '', // Properti untuk menerima kelas tambahan
    'colspan' => 1,
    'rowspan' => 1,
])

<th scope="col" colspan="{{ $colspan }}" rowspan="{{ $rowspan }}" class="px-5 py-2.5 text-sm font-semibold text-gray-900 border border-solid {{ trim($class) }} {{ $class }}">
    {{ $slot }}
</th>
