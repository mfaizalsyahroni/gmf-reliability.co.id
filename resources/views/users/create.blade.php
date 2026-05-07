<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create User
        </h2>
    </x-slot>

    <form action="/users" method="post" class="space-y-6" novalidate>

        @csrf
        <div>
            <label for="name">Name:</label>
            <input class="border px-4 py-2 rounded block mt-1" type="text" id="name" name="name">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="email">Email:</label>
            <input class="border px-4 py-2 rounded block mt-1" type="email" id="email" name="email">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="Position">Position:</label>
            <input class="border px-4 py-2 rounded block mt-1" type="Position" id="Position" name="Position">
            @error('Position')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password">Password:</label>
            <input class="border px-4 py-2 rounded block mt-1" type="password" id="password" name="password">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <x-third-button>
            Create
        </x-third-button>
    </form>
</x-app-layout>