@extends('layouts.app')

@section('content')
    <div x-data="paymentRateModal()" class="container mx-auto mt-5">
        <h1 class="text-2xl font-bold mb-4">Payment Rates</h1>
        <x-primary-button @click="openModal('add')">Add Payment Rate</x-primary-button>
        <!-- Table of Payment Rates -->
        @livewire('settings.payment-rate-table')
        {{-- Include the modal for payment rates --}}
        <div x-show="isOpen" @click.away="closeModal()" @keydown.escape.window="closeModal()" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold" x-text="modalTitle"></h2>
                    <!-- Close Button (X) with Font Awesome Icon -->
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
                <!-- Add Payment Rate Form -->
                <div x-show="action === 'add' || action === 'edit'">
                    <form @submit.prevent="submitForm">
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label>Title:</x-input-label>
                                    <x-text-input type="text" x-model="formData.title" required />
                                </div>
                                <div>
                                    <x-input-label>Rate:</x-input-label>
                                    <x-text-input type="number" x-model="formData.rate" required />
                                </div>
                                <div>
                                    <x-input-label for="event_id">Event</x-input-label>
                                    <x-select-input id="event_id" x-model="formData.event_id" required>
                                        <option value="">-- Select Event --</option>
                                        @foreach ($events as $event)
                                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                                        @endforeach
                                    </x-select-input>
                                </div>
                                <div>
                                    <x-input-label>Order:</x-input-label>
                                    <x-text-input type="number" x-model="formData.order" required />
                                </div>
                                <div>
                                    <x-input-label>Date To Pay:</x-input-label>
                                    <x-text-input type="date" x-model="formData.date_to_pay" required />
                                </div>
                            </div>
                            <div class="mt-4 w-full text-center">
                                <x-primary-button type="submit"
                                    x-text="action === 'add' ? 'Create' : 'Update'"></x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Delete Payment Rate Confirmation -->
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this Payment Rate?</p>
                    <div class="flex justify-center">
                        <x-danger-button @click="confirmDelete()">Delete</x-danger-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function paymentRateModal() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    title: '',
                    rate: '',
                    order: '',
                    date_to_pay: '',
                    event_id: ''
                },
                selectedPaymentRateId: null,
                selectedPaymentRate: null,

                openModal(action, paymentRate = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Payment Rate' : action === 'edit' ? 'Edit Payment Rate' :
                        'Delete Payment Rate';
                    if (paymentRate) {
                        this.selectedPaymentRate = JSON.parse(paymentRate);
                        this.selectedPaymentRateId = this.selectedPaymentRate.id;
                        this.formData = {
                            ...this.selectedPaymentRate
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit PaymentRate: ' + this.selectedPaymentRate.title;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete PaymentRate: ' + this.selectedPaymentRate.title;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedPaymentRateId = null;
                },

                resetForm() {
                    this.formData = {
                        title: '',
                        rate: '',
                        order: '',
                        date_to_pay: '',
                        event_id: ''
                    };
                    this.errors = null;
                },

                submitForm() {
                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('payment_rates.store') }}` :
                        `{{ route('payment_rates.update', '') }}/${this.selectedPaymentRateId}`;

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
                    fetch(`{{ route('payment_rates.destroy', '') }}/${this.selectedPaymentRateId}`, {
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
