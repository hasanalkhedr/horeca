@extends('layouts.app')
@section('content')
    <div x-data="priceModal()" class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Prices</h1>

        <!-- Button to Add Price -->
        <x-primary-button @click="openModal('add')">Add Price</x-primary-button>

        <!-- Table of Prices -->
        @livewire('settings.price-table')

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
                                    <x-input-label for="name">Name</x-input-label>
                                    <x-text-input id="name" x-model="formData.name" required />
                                </div>
                                <div>
                                    <x-input-label for="category_id">Category</x-input-label>
                                    <x-select-input id="category_id" x-model="formData.category_id">
                                        <option value="">-- Select Category --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </x-select-input>
                                </div>
                                {{-- <div>
                                    <x-input-label for="stand_type_id">Stand Type</x-input-label>
                                    <x-text-input type="number" id="stand_type_id"
                                        x-model="formData.stand_type_id" />
                                </div> --}}
                                <div>
                                    <x-input-label for="currency_id">Currency</x-input-label>
                                    <x-select-input id="currency_id" x-model="formData.currency_id" required>
                                        <option value="">-- Select Currency --</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{$currency->id}}">{{$currency->CODE}}</option>
                                        @endforeach
                                    </x-select-input>
                                </div>
                                <div>
                                    <x-input-label for="amount">Amount</x-input-label>
                                    <x-text-input type="number" id="amount"
                                        x-model="formData.amount" required />
                                </div>
                                <div>
                                    <x-input-label for="description">Description</x-input-label>
                                    <x-text-input type="text" class="w-full" id="description"
                                        x-model="formData.description" required />
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
                    <p class="mb-4">Are you sure you want to delete this price?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function priceModal() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    name: '',
                    pricing_strategy_id: '',
                    category_id: '',
                    stand_type_id: '',
                    currency_id: '',
                    amount: '',
                    description: ''
                },
                selectedPriceId: null,
                selectedPrice: null,

                openModal(action, price = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Price' : action === 'edit' ? 'Edit Price' : 'Delete Price';
                    if (price) {
                        this.selectedPrice = JSON.parse(price);
                        this.selectedPriceId = this.selectedPrice.id;
                        this.formData = {
                            ...this.selectedPrice
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit Price: ' + this.selectedPrice.name;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Price: ' + this.selectedPrice.name;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedPriceId = null;
                },

                resetForm() {
                    this.formData = {
                        name: '',
                    pricing_strategy_id: '',
                    category_id: '',
                    stand_type_id: '',
                    currency_id: '',
                    amount: '',
                    description: ''
                    };
                    this.errors = null;
                },

                submitForm() {
                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('prices.store') }}` :
                        `{{ route('prices.update', '') }}/${this.selectedPriceId}`;

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
                    fetch(`{{ route('prices.destroy', '') }}/${this.selectedPriceId}`, {
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
