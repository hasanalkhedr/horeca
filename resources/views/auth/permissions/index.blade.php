@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4">Permissions</h1>
        <a href="{{ route('permissions.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Create Permission</a>
        <table class="min-w-full bg-white mt-4">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">ID</th>
                    <th class="py-2 px-4 border-b">Name</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissions as $permission)
                    <tr>
                        <td class="py-2 px-4 border-b">{{ $permission->id }}</td>
                        <td class="py-2 px-4 border-b">{{ $permission->name }}</td>
                        <td class="py-2 px-4 border-b">
                            <a href="{{ route('permissions.edit', $permission->id) }}" class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</a>
                            <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" class="inline">
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
