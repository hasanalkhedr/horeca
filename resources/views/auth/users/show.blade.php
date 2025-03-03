@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4">User Details</h1>
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">ID</label>
                <p class="mt-1 text-gray-900">{{ $user->id }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <p class="mt-1 text-gray-900">{{ $user->name }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <p class="mt-1 text-gray-900">{{ $user->email }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Roles</label>
                <div class="mt-1">
                    @foreach ($user->roles as $role)
                        <span class="bg-gray-200 px-2 py-1 rounded text-sm">{{ $role->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('users.edit', $user->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">Edit</a>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
