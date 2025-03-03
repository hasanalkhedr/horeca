@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4">Edit Role</h1>
        <form action="{{ route('roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text" name="name" id="name" value="{{ $role->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            </div>
            <div class="mb-4">
                <label for="permissions" class="block text-sm font-medium text-gray-700">Permissions</label>
                <select name="permissions[]" id="permissions" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" multiple>
                    @foreach ($permissions as $permission)
                        <option value="{{ $permission->name }}" {{ $role->hasPermissionTo($permission->name) ? 'selected' : '' }}>{{ $permission->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
        </form>
    </div>
@endsection
