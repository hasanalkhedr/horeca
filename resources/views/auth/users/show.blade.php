@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="userTable()">
        <!-- Back to Index Link -->
        <div class="mb-6">
            <a href="{{ route('users.index') }}" class="text-blue-500 hover:text-blue-700">
                &larr; Back to Users
            </a>
        </div>

        <!-- User Details Card -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex flex-col items-center space-y-4">
                <!-- Profile Picture -->
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-200">
                    @if ($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture"
                            class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500 text-2xl">No Image</span>
                        </div>
                    @endif
                </div>

                <!-- User Name and Email -->
                <div class="text-center">
                    <h2 class="text-2xl font-semibold">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>
                </div>

                <!-- Roles -->
                <div class="text-center">
                    <h3 class="text-lg font-semibold mb-2">Roles</h3>
                    <div class="flex space-x-2">
                        @foreach ($user->roles as $role)
                            <span class="bg-gray-200 px-2 py-1 rounded text-sm">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <!-- Edit and Delete Buttons -->
                <div class="mt-6 flex space-x-4">
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
                    profile_picture_url: '', // For displaying the current image
                },
                selectedUserId: null,
                selectedUser: null,
                openModal(action, user = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'edit' ? 'Edit User' : 'Delete User';
                    if (user) {
                        this.selectedUser = user;
                        this.selectedUserId = this.selectedUser.id;
                        this.formData = {
                            id: user.id,
                            name: user.name,
                            email: user.email,
                            roles: user.roles.map(role => role.id), // Map roles to IDs
                            profile_picture_url: user.profile_picture ?
                                `{{ asset('storage/${user.profile_picture}') }}` : '',
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit User: ' + this.selectedUser.name;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete User: ' + this.selectedUser.name;
                        }
                    }
                },
                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.formData.profile_picture = file;
                        this.formData.profile_picture_url = URL.createObjectURL(file); // Preview the image
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
                        profile_picture_url: '', // For displaying the current image
                    };
                    this.errors = null;
                },
                submitForm() {
                    const formData = new FormData();

                    // Append basic fields
                    formData.append('name', this.formData.name || '');
                    formData.append('email', this.formData.email || '');

                    // Append password fields only if they are provided
                    if (this.formData.password) {
                        formData.append('password', this.formData.password);
                        formData.append('password_confirmation', this.formData.password_confirmation);
                    }

                    // Append roles as an array
                    if (this.formData.roles && this.formData.roles.length > 0) {
                        this.formData.roles.forEach(role => {
                            formData.append('roles[]', role); // Use 'roles[]' for arrays
                        });
                    }

                    // Append profile picture as a file
                    if (this.formData.profile_picture) {
                        formData.append('profile_picture', this.formData.profile_picture);
                    }

                    // For update requests, append _method=PUT
                    if (this.action === 'edit') {
                        formData.append('_method', 'PUT');
                    }

                    const url = this.action === 'edit' ?
                        `{{ route('users.update', '') }}/${this.selectedUserId}` :
                        `{{ route('users.store') }}`;

                    // Retrieve the CSRF token from the meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(url, {
                            method: 'POST', // Always use POST for FormData
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
                }
            };
        }
    </script>
@endsection
