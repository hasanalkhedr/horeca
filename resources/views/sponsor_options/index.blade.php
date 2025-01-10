@extends('layouts.app')

@section('content')
    <div x-data="sponsorOptionModal()" class="container mx-auto mt-5">
        <h1 class="text-2xl font-bold mb-4">Sponsor Options</h1>
        <x-primary-button @click="openModal('add')">Add Sponsor Option</x-primary-button>
        <!-- Table of Sponsor Options -->
        @livewire('sponsor-option-table')
        {{-- Include the modal for sponsor option --}}
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
                <!-- Add Sponsor Option Form -->
                <div x-show="action === 'add' || action === 'edit'">
                    <form @submit.prevent="submitForm">
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label>Title:</x-input-label>
                                    <x-text-input type="text" x-model="formData.title" required />
                                </div>
                            </div>
                            <div class="mt-4 w-full text-center">
                                <x-primary-button type="submit"
                                    x-text="action === 'add' ? 'Create' : 'Update'"></x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Delete Sponsor Option Confirmation -->
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this Sponsor Option?</p>
                    <div class="flex justify-center">
                        <x-danger-button @click="confirmDelete()">Delete</x-danger-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sponsorOptionModal() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    title: '',
                },
                selectedSponsorOptionId: null,
                selectedSponsorOption: null,

                openModal(action, sponsorOption = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Sponsor Option' : action === 'edit' ? 'Edit Sponsor Option' :
                        'Delete Sponsor Option';
                    if (sponsorOption) {
                        this.selectedSponsorOption = JSON.parse(sponsorOption);
                        this.selectedSponsorOptionId = this.selectedSponsorOption.id;
                        this.formData = {
                            ...this.selectedSponsorOption
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit SponsorOption: ' + this.selectedSponsorOption.title;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete SponsorOption: ' + this.selectedSponsorOption.title;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedSponsorOptionId = null;
                },

                resetForm() {
                    this.formData = {
                        title: '',
                    };
                    this.errors = null;
                },

                submitForm() {
                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('sponsor_options.store') }}` :
                        `{{ route('sponsor_options.update', '') }}/${this.selectedSponsorOptionId}`;

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
                    fetch(`{{ route('sponsor_options.destroy', '') }}/${this.selectedSponsorOptionId}`, {
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
