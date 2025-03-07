@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="userTable()">
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="{{ route('users.index') }}" class="text-blue-500 hover:text-blue-700">
                    &larr; Back to Users
                </a>
            </div>
            <div class="flex space-x-4">
                <button @click="openModal('edit', {{ $user }})"
                    class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button @click="openModal('delete', {{ $user }})"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>

        <!-- User Details Card -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex flex-row items-start space-x-8">
                <div class="flex-1">
                    <div class="text-left">
                        <h2 class="text-2xl font-semibold">{{ $user->name }}</h2>
                        <p class="text-gray-600">{{ $user->email }}</p>
                    </div>

                    <!-- Roles Table -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">Roles and Permissions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Permissions</th>
                                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($user->roles as $role)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('roles.show', $role->id) }}" class="text-blue-500 hover:text-blue-700 font-medium">
                                                    {{ $role->name }}
                                                </a>
                                            </td>
                                            <!-- Permissions -->
                                            <td class="px-6 py-4">
                                                @if ($role->permissions->count() > 0)
                                                    <ul class="list-disc list-inside">
                                                        @foreach ($role->permissions as $permission)
                                                            <li class="text-sm text-gray-700">{{ $permission->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-gray-500 text-sm">No permissions assigned</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <form action="{{ route('users.unassignRole', [$user->id, $role->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this role?');">
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
                            <button @click="openModal('addRole')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                <i class="fas fa-plus"></i> Add Role
                            </button>
                        </div>
                    </div>
                </div>

                <div class="w-96 h-96 rounded-full overflow-hidden border-4 border-gray-200">
                    <img src="{{ $user->getProfilePictureUrlAttribute() }}" alt="Profile Picture"
                                class="w-full h-full object-cover">
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
                    <form @submit.prevent="submitForm" enctype="multipart/form-data">
                        <div class="flex">
                            <div class="w-1/2">
                                <div class="mb-2">
                                    <x-input-label for="name">User Name</x-input-label>
                                    <x-text-input id="name" x-model="formData.name" required />
                                </div>
                                <div class="mb-2">
                                    <x-input-label for="email">Email</x-input-label>
                                    <x-text-input type="email" id="email" x-model="formData.email" />
                                </div>
                                <div class="mb-2">
                                    <x-input-label for="password">Password (Leave blank to keep current password)</x-input-label>
                                    <x-text-input type="password" id="password" x-model="formData.password" />
                                </div>
                                <div class="mb-2">
                                    <x-input-label for="password_confirmation">Confirm Password</x-input-label>
                                    <x-text-input type="password" id="password_confirmation"
                                        x-model="formData.password_confirmation" />
                                </div>
                                <div class="mb-2">
                                    <x-input-label for="roles">Roles</x-input-label>
                                    <select name="roles[]" x-model="formData.roles" multiple class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="w-1/2">
                                <!-- Profile Picture Upload -->
                                <div class="mb-2">
                                    <x-input-label for="profile_picture">Profile Picture</x-input-label>
                                    <input type="file" id="profile_picture" name="profile_picture"
                                        @change="handleFileUpload" class="mt-1 block w-full">
                                </div>
                                <!-- Display Current Profile Picture -->
                                <div x-show="formData.profile_picture_url">
                                    <img :src="formData.profile_picture_url" alt="Profile Picture"
                                        class="w-20 h-20 rounded-full mt-2">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 w-full text-center">
                            <x-primary-button type="submit">Update</x-primary-button>
                        </div>
                    </form>
                </div>
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this USER?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
                <!-- Add Role Modal -->
                <div x-show="action === 'addRole'">
                    <form action="{{ route('users.assignRole', $user->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="role_id">Select Role</x-input-label>
                            <select name="role_id" id="role_id" class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm">
                                @foreach ($availableRoles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-center">
                            <x-primary-button type="submit">Add Role</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function userTable() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    roles: [],
                    profile_picture: null,
                    profile_picture_url: '',
                },
                selectedUserId: null,
                selectedUser: null,
                openModal(action, data = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    if (action === 'edit' || action === 'delete') {
                        this.modalTitle = action === 'edit' ? 'Edit User' : 'Delete User';
                        if (data) {
                            this.selectedUser = data;
                            this.selectedUserId = data.id;
                            this.formData = {
                                id: data.id,
                                name: data.name,
                                email: data.email,
                                roles: data.roles.map(role => role.id),
                                profile_picture_url: data.profile_picture ?
                                    `{{ asset('storage/${data.profile_picture}') }}` : '',
                            };
                            if (action === 'edit') {
                                this.modalTitle = 'Edit User: ' + data.name;
                            } else if (action === 'delete') {
                                this.modalTitle = 'Delete User: ' + data.name;
                            }
                        }
                    }
                },
                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.formData.profile_picture = file;
                        this.formData.profile_picture_url = URL.createObjectURL(file);
                    }
                },
                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedUserId = null;
                },
                resetForm() {
                    this.formData = {
                        name: '',
                        email: '',
                        password: '',
                        password_confirmation: '',
                        roles: [],
                        profile_picture: null,
                        profile_picture_url: '',
                    };
                    this.errors = null;
                },
                submitForm() {
                    const formData = new FormData();
                    formData.append('name', this.formData.name || '');
                    formData.append('email', this.formData.email || '');
                    if (this.formData.password) {
                        formData.append('password', this.formData.password);
                        formData.append('password_confirmation', this.formData.password_confirmation);
                    }
                    if (this.formData.roles && this.formData.roles.length > 0) {
                        this.formData.roles.forEach(role => {
                            formData.append('roles[]', role);
                        });
                    }
                    if (this.formData.profile_picture) {
                        formData.append('profile_picture', this.formData.profile_picture);
                    }
                    if (this.action === 'edit') {
                        formData.append('_method', 'PUT');
                    }
                    const url = this.action === 'edit' ?
                        `{{ route('users.update', '') }}/${this.selectedUserId}` :
                        `{{ route('users.store') }}`;
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
                    fetch(`{{ route('users.destroy', '') }}/${this.selectedUserId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(() => {
                            this.closeModal();
                            window.location.href = '{{ route('users.index') }}';
                        });
                },
            };
        }
    </script>
@endsection
