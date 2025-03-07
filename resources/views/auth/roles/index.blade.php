@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="rolesTable()">
        <div class="flex">
            <h1 class="w-1/2 text-2xl font-bold mb-4">Roles</h1>
            <div class="w-1/2 flex justify-end mb-4">
                <button @click="openModal('add')" class="bg-blue-500 text-white px-4 py-2 rounded">Create Role</button>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="mb-4 flex space-x-4">
            <!-- Search by Name -->
            <form action="{{ route('roles.index') }}" method="GET" class="flex-1">
                <input type="text" name="search" placeholder="Search by role name" value="{{ request('search') }}"
                    class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm">
            </form>

            <!-- Filter by Permission -->
            <form action="{{ route('roles.index') }}" method="GET" class="flex-1">
                <select name="permission" onchange="this.form.submit()"
                    class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm">
                    <option value="">All Permissions</option>
                    @foreach ($permissions as $permission)
                        <option value="{{ $permission->name }}"
                            {{ request('permission') == $permission->name ? 'selected' : '' }}>
                            {{ $permission->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Roles Table -->
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left">
                        <a
                            href="{{ route('roles.index', ['sort' => 'id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                            ID {!! request('sort') == 'id' ? (request('direction') == 'asc' ? '&#9650;' : '&#9660;') : '' !!}
                        </a>
                    </th>
                    <th class="py-3 px-4 text-left">
                        <a
                            href="{{ route('roles.index', ['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                            Name {!! request('sort') == 'name' ? (request('direction') == 'asc' ? '&#9650;' : '&#9660;') : '' !!}
                        </a>
                    </th>
                    <th class="py-3 px-4 text-left">Permissions</th>
                    <th class="py-3 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr class="border-b hover:bg-gray-100{{ $loop->even ? 'bg-gray-50' : '' }}"
                        onclick="window.location.href='{{ route('roles.show', $role->id) }}'">
                        <td class="py-3 px-4 cursor-pointer ">{{ $role->id }}</td>
                        <td class="py-3 px-4 cursor-pointer ">{{ $role->name }}</td>
                        <td class="py-3 px-4" onclick="event.stopPropagation()">
                            @foreach ($role->permissions as $permission)
                                <span class="bg-gray-200 px-2  cursor-pointer py-1 rounded text-sm hover:bg-gray-300"
                                    onclick="event.stopPropagation(); window.location.href='{{ route('permissions.show', $permission->id) }}'">
                                    {{ $permission->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="py-3 px-4" onclick="event.stopPropagation()">
                            <button @click="openModal('edit', {{ $role }})"
                                class="text-yellow-500 hover:text-yellow-700">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button @click="openModal('delete', {{ $role }})"
                                class="text-red-500 hover:text-red-700 ml-2">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $roles->links() }}
        </div>

        <!-- Modals -->
        <div x-show="isOpen" @click.away="closeModal()" @keydown.escape.window="closeModal()" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-4xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold" x-text="modalTitle"></h2>
                    <button @click="closeModal()"
                        class="text-gray-600 text-3xl hover:text-gray-800 transition-colors duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Validation Errors -->
                <div x-show="errors" class="mb-4">
                    <ul class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <template x-for="(error, field) in errors" :key="field">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>

                <!-- Form based on action type -->
                <div x-show="action === 'add' || action === 'edit'">
                    <form @submit.prevent="submitForm">
                        <div class="flex">
                            <div class="w-full">
                                <div class="mb-2">
                                    <x-input-label for="name">Role Name</x-input-label>
                                    <x-text-input id="name" x-model="formData.name" required />
                                </div>
                                <div class="mb-2">
                                    <x-input-label for="permissions[]">Permissions</x-input-label>
                                    <select name="permissions[]" x-model="formData.permissions" multiple>
                                        @foreach ($permissions as $permission)
                                            <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 w-full text-center">
                            <x-primary-button type="submit"
                                x-text="action === 'add' ? 'Create' : 'Update'"></x-primary-button>
                        </div>
                    </form>
                </div>
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this ROLE?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function rolesTable() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    name: '',
                    permissions: [],
                },
                selectedRoleId: null,
                selectedRole: null,
                openModal(action, role = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Role' : action === 'edit' ? 'Edit Role' : 'Delete Role';

                    if (role) {
                        this.selectedRole = role;
                        this.selectedRoleId = this.selectedRole.id;
                        this.formData = {
                            id: role.id,
                            name: role.name,
                            permissions: role.permissions.map(permission => permission.id),
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit Role: ' + this.selectedRole.name;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Role: ' + this.selectedRole.name;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedRoleId = null;
                },

                resetForm() {
                    this.formData = {
                        name: '',
                        permissions: [],
                    };
                    this.errors = null;
                },

                submitForm() {
                    const formData = new FormData();
                    formData.append('name', this.formData.name);
                    if (this.formData.permissions) {
                        this.formData.permissions.forEach(permission => {
                            formData.append('permissions[]', permission);
                        });
                    }
                    // For update requests, append _method=PUT
                    if (this.action === 'edit') {
                        formData.append('_method', 'PUT');
                    }
                    const method = this.action === 'add' ? 'POST' : 'POST';
                    const url = this.action === 'add' ?
                        `{{ route('roles.store') }}` :
                        `{{ route('roles.update', '') }}/${this.selectedRoleId}`;

                    // Retrieve the CSRF token from the meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(url, {
                            method: method,
                            headers: {
                                'X-CSRF-TOKEN': csrfToken, // Use the retrieved CSRF token
                            },
                            body: formData,
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(data => {
                                    throw data;
                                });
                            }
                            return response.json();
                        })
                        .then(() => {
                            this.closeModal();
                            location.reload();
                        })
                        .catch(error => {
                            this.errors = error || {
                                general: ['Something went wrong. Please try again.']
                            };
                        });
                },

                confirmDelete() {
                    fetch(`{{ route('roles.destroy', '') }}/${this.selectedRoleId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(() => {
                            this.closeModal();
                            location.reload();
                        });
                }
            };
        }
    </script>
@endsection
