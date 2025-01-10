@extends('layouts.app')
@section('content')
    <div x-data="accountModal()" class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Bank Accounts</h1>

        <!-- Button to Add Land -->
        <x-primary-button @click="openModal('add')">Add Bank Account</x-primary-button>

        <!-- Table of Lands -->
        @livewire('settings.bank-account-table')

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
                <div x-show="action==='add' || action==='edit'">
                    <form @submit.prevent="submitForm">
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="name">Name</x-input-label>
                                    <x-text-input id="name" x-model="formData.name" required />
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
                                    <x-input-label for="IBAN">IBAN</x-input-label>
                                    <x-text-input id="IBAN" x-model="formData.IBAN" required />
                                </div>
                                <div>
                                    <x-input-label for="swift_code">Swift Code</x-input-label>
                                    <x-text-input id="swift_code" x-model="formData.swift_code" required />
                                </div>
                                <div>
                                    <x-input-label for="account_name">Account Name</x-input-label>
                                    <x-text-input id="account_name" x-model="formData.account_name" required />
                                </div>
                            </div>
                            <div class="mt-4 w-full text-center">
                                <x-primary-button type="submit"
                                    x-text="action === 'add' ? 'Create' : 'Update'"></x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>

                <div x-show="action==='delete'">
                    <div>
                        <p class="mb-4">Are you sure you want to delete this Bank Account?</p>
                        <x-danger-button type="submit" @click="confirmDelete()">Delete</x-danger-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function accountModal() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    name: '',
                    IBAN: '',
                    swift_code: '',
                    account_name: '',
                    event_id: ''
                },
                selectedBankAccountId: null,
                selectedBankAccount: null,

                openModal(action, bankAccount = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.modalTitle = action === 'add' ? 'Add Bank Account' : action === 'edit' ? 'Edit Bank Account' :
                        'Delete Bank Account';

                    if (bankAccount) {

                        this.selectedBankAccount = JSON.parse(bankAccount);
                        this.selectedBankAccountId = this.selectedBankAccount.id;
                        this.formData = {
                            ...this.selectedBankAccount
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit Bank Account: ' + this.selectedBankAccount.name;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Bank Account: ' + this.selectedBankAccount.name;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedBankAccountId = null;
                },

                resetForm() {
                    this.formData = {
                        name: '',
                        IBAN: '',
                        swift_code: '',
                        account_name: '',
                        event_id: ''
                    };
                },

                submitForm() {
                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('bank_accounts.store') }}` :
                        `{{ route('bank_accounts.update', '') }}/${this.selectedBankAccountId}`;
                    fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: this.formData.name,
                                IBAN: this.formData.IBAN,
                                swift_code: this.formData.swift_code,
                                account_name: this.formData.account_name
                            })
                        })
                        .then(response => response.json())
                        .then(() => {
                            this.closeModal();
                            location.reload();
                        });
                },

                confirmDelete() {
                    fetch(`{{ route('bank_accounts.destroy', '') }}/${this.selectedBankAccountId}`, {
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
