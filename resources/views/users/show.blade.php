<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->name }}
        </h2>
    </x-slot>

    <div>Email : {{ $user->email }}</div>
    <div>Password : {{ $user->password }}</div>
    <div>Position as {{ $user->Position }}</div>
    <div>Registered at {{ $user->created_at->diffForHumans() }}</div>

    <form action="/users/{{ $user->id }}" method="post" class="mt-6">
        @method('DELETE')
        @csrf
        <x-third-button class="hover:bg-red-600" type="submit">
            Delete
        </x-third-button>
    </form>
</x-app-layout>