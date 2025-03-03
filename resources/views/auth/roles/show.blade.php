@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4">Role Details</h1>
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">ID</label>
                <p class="mt-1 text-gray-900">{{ $role->id }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <p class="mt-1 text-gray-900">{{ $role->name }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Permissions</label>
                <div class="mt-1">
                    @foreach ($role->permissions as $permission)
                        <span class="bg-gray-200 px-2 py-1 rounded text-sm">{{ $permission->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('roles.edit', $role->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">Edit</a>
                <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
