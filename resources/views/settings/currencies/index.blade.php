@extends('layouts.app')
@section('content')
    <div x-data="currencyModal()" class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Currencies</h1>

        <!-- Button to Add Currency -->
        <x-primary-button @click="openModal('add')">Add Currency</x-primary-button>

        <!-- Table of Currencies -->
        @livewire('settings.currency-table')

        <!-- Modal -->
        <div x-show="isOpen" @click.away="closeModal()" @keydown.escape.window="closeModal()" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="CODE">CODE</x-input-label>
                                    <x-text-input id="CODE" x-model="formData.CODE" required />
                                </div>
                                <div>
                                    <x-input-label for="name">Name</x-input-label>
                                    <x-text-input id="name" x-model="formData.name" required />
                                </div>
                                <div>
                                    <x-input-label for="rate_to_usd">Rate (to USD)</x-input-label>
                                    <x-text-input required type="number" step="0.01" id="rate_to_usd" x-model="formData.rate_to_usd"/>
                                </div>
                                <div>
                                    <x-input-label for="country">Country</x-input-label>
                                    <x-text-input id="country" x-model="formData.country"/>
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
                    <p class="mb-4">Are you sure you want to delete this currency?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function currencyModal() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    name: '',
                    CODE: '',
                    country: '',
                    rate_to_usd: ''
                },
                selectedCurrencyId: null,
                selectedCurrency: null,

                openModal(action, currency = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Currency' : action === 'edit' ? 'Edit Currency' : 'Delete Currency';
                    if (currency) {
                        this.selectedCurrency = JSON.parse(currency);
                        this.selectedCurrencyId = this.selectedCurrency.id;
                        this.formData = {
                            ...this.selectedCurrency
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit Currency: ' + this.selectedCurrency.name;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Currency: ' + this.selectedCurrency.name;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedCurrencyId = null;
                },

                resetForm() {
                    this.formData = {
                        name: '',
                    CODE: '',
                    country: '',
                    rate_to_usd: ''
                    };
                    this.errors = null;
                },

                submitForm() {
                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('currencies.store') }}` :
                        `{{ route('currencies.update', '') }}/${this.selectedCurrencyId}`;

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
                    fetch(`{{ route('currencies.destroy', '') }}/${this.selectedCurrencyId}`, {
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
