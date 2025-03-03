@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4">Roles</h1>
        <a href="{{ route('roles.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Create Role</a>
        <table class="min-w-full bg-white mt-4">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">ID</th>
                    <th class="py-2 px-4 border-b">Name</th>
                    <th class="py-2 px-4 border-b">Permissions</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td class="py-2 px-4 border-b">{{ $role->id }}</td>
                        <td class="py-2 px-4 border-b">{{ $role->name }}</td>
                        <td class="py-2 px-4 border-b">
                            @foreach ($role->permissions as $permission)
                                <span class="bg-gray-200 px-2 py-1 rounded text-sm">{{ $permission->name }}</span>
                            @endforeach
                        </td>
                        <td class="py-2 px-4 border-b">
                            <a href="{{ route('roles.edit', $role->id) }}" class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</a>
                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
