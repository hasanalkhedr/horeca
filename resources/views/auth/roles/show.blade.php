@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="roleTable()">
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="{{ route('roles.index') }}" class="text-blue-500 hover:text-blue-700">
                    &larr; Back to Roles
                </a>
            </div>
            <div class="flex space-x-4">
                <button @click="openModal('edit', {{ $role }})"
                    class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button @click="openModal('delete', {{ $role }})"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>

        <!-- Role Details Card -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex flex-row items-start space-x-8">
                <div class="flex-1">
                    <div class="text-left">
                        <h2 class="text-2xl font-semibold">{{ $role->name }}</h2>
                    </div>

                    <!-- Permissions Table -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">Permissions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                            Permissions</th>
                                        <th
                                            class="px-6 py-3 text-right text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($role->permissions as $permission)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="text-blue-500 hover:text-blue-700 font-medium">
                                                    {{ $permission->name }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <form
                                                    action="{{ route('roles.removePermission', [$role->id, $permission->id]) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure you want to remove this permission?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <button @click="openModal('addPermission')"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                <i class="fas fa-plus"></i> Add Permission
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
                <div x-show="action === 'edit'">
                    <form @submit.prevent="submitForm">
                        <div class="flex">
                            <div class="w-1/2">
                                <div class="mb-2">
                                    <x-input-label for="name">Role Name</x-input-label>
                                    <x-text-input id="name" x-model="formData.name" required />
                                </div>
                                <div class="mb-2">
                                    <x-input-label for="permissions">Permissions</x-input-label>
                                    <select name="permissions[]" x-model="formData.permissions" multiple
                                        class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm">
                                        @foreach ($permissions as $permission)
                                            <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 w-full text-center">
                            <x-primary-button type="submit">Update</x-primary-button>
                        </div>
                    </form>
                </div>
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this ROLE?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
                <!-- Add Permission Modal -->
                <div x-show="action === 'addPermission'">
                    <form action="{{ route('roles.givePermission', $role->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="permission_id">Select Permission</x-input-label>
                            <select name="permission_id" id="permission_id"
                                class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm">
                                @foreach ($availablePermissions as $permission)
                                    <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-center">
                            <x-primary-button type="submit">Add Permission</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function roleTable() {
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
                openModal(action, data = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    if (action === 'edit' || action === 'delete') {
                        this.modalTitle = action === 'edit' ? 'Edit Role' : 'Delete Role';
                        if (data) {
                            this.selectedRole = data;
                            this.selectedRoleId = data.id;
                            this.formData = {
                                id: data.id,
                                name: data.name,
                                permissions: data.permissions.map(permission => permission.id),
                            };
                            if (action === 'edit') {
                                this.modalTitle = 'Edit Role: ' + data.name;
                            } else if (action === 'delete') {
                                this.modalTitle = 'Delete Role: ' + data.name;
                            }
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
                    formData.append('name', this.formData.name || '');
                    if (this.formData.permissions && this.formData.permissions.length > 0) {
                        this.formData.permissions.forEach(permission => {
                            formData.append('permissions[]', permission);
                        });
                    }
                    if (this.action === 'edit') {
                        formData.append('_method', 'PUT');
                    }
                    const url = this.action === 'edit' ?
                        `{{ route('roles.update', '') }}/${this.selectedRoleId}` :
                        `{{ route('roles.store') }}`;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
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
                            window.location.href = '{{ route('roles.index') }}';
                        });
                },
            };
        }
    </script>
@endsection
