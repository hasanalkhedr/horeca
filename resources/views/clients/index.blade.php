@extends('layouts.app')
@section('content')
    <div x-data="clientModal()" class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Persons {{ 'of company: ' . $company->name }}</h1>

        <!-- Button to Add Person -->
        <x-primary-button @click="openModal('add')">Add Person</x-primary-button>

        <!-- Table of Persons -->
        @if ($company)
            @livewire('client-table', ['company' => $company])
        @else
            @livewire('client-table')
        @endif

        <!-- Modal -->
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
                        <div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-1">
                                <div>
                                    <x-input-label for="name">Person Name</x-input-label>
                                    <x-text-input id="name" x-model="formData.name" required />
                                </div>
                                @if ($company->id == null)
                                    <div>
                                        <x-input-label for="company_id">Company</x-input-label>
                                        <x-select-input name="company_id" id="company_id" x-model="formData.company_id" required>
                                            <option value="">-- Select Company --</option>
                                            @foreach ($companies as $cmp)
                                                <option value="{{ $cmp->id }}">{{ $cmp->name }}</option>
                                            @endforeach
                                        </x-select-input>
                                    </div>
                                @else
                                    <div>
                                        <x-input-label for="company_id">Company</x-input-label>
                                        <x-select-input name="company_id" id="company_id" x-model="formData.company_id" required>
                                            <option value="">-- Select Company --</option>
                                            <option selected value="{{ $company->id }}">{{ $company->name }}</option>
                                        </x-select-input>
                                    </div>
                                @endif
                                <div>
                                    <x-input-label for="position">Position in Company</x-input-label>
                                    <x-text-input id="position" x-model="formData.position" />
                                </div>
                                <div>
                                    <x-input-label for="mobile">Mobile</x-input-label>
                                    <x-text-input id="mobile" x-model="formData.mobile" />
                                </div>
                                <div>
                                    <x-input-label for="phone">Phone</x-input-label>
                                    <x-text-input id="phone" x-model="formData.phone" />
                                </div>
                                <div>
                                    <x-input-label for="email">Email</x-input-label>
                                    <x-text-input id="email" x-model="formData.email" />
                                </div>
                            </div>
                            <div class="mt-4 w-full text-center">
                                <x-primary-button type="submit"
                                    x-text="action === 'add' ? 'Create' : 'Update'"></x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this person?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function clientModal() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    name: '',
                    position: '',
                    mobile: '',
                    phone: '',
                    email: '',
                    company_id: ''
                },
                selectedPersonId: null,
                selectedPerson: null,

                openModal(action, client = null) {

                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Person' : action === 'edit' ? 'Edit Person' :
                        'Delete Person';
                    if (client) {
                        this.selectedPerson = JSON.parse(client);
                        this.selectedPersonId = this.selectedPerson.id;
                        this.formData = {
                            ...this.selectedPerson
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit Person: ' + this.selectedPerson.name + '|' + this.selectedPerson.CODE;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Person: ' + this.selectedPerson.name + '|' + this.selectedPerson
                            .CODE;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedPersonId = null;
                },

                resetForm() {
                    this.formData = {
                        name: '',
                    position: '',
                    mobile: '',
                    phone: '',
                    email: '',
                    company_id: ''
                    };
                    this.errors = null;
                },

                submitForm() {
                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('clients.store') }}` :
                        `{{ route('clients.update', '') }}/${this.selectedPersonId}`;
console.log(this.formData);
                    fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.formData)
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
                            this.errors = error;
                        });
                },

                confirmDelete() {
                    fetch(`{{ route('clients.destroy', '') }}/${this.selectedPersonId}`, {
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
