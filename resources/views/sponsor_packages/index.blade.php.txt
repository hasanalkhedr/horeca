@extends('layouts.app')

@section('content')
    <div x-data="sponsorPackageModal()" class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Sponsor Packages</h1>
        <!-- Button to Add Sponsor Package -->
        <x-primary-button @click="openModal('add')">Add Sponsor Package</x-primary-button>
        <!-- Table of Sponsor Packages -->

        @livewire('sponsor-package-table')
        <!-- Modal for Adding/Editing Sponsor Package -->
        <div x-show="isOpen" @click.away="closeModal()" @keydown.escape.window="closeModal()" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-full w-auto">
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
                <!-- Form for Sponsor Package -->
                <div x-show="action === 'add' || action === 'edit'">
                    <form @submit.prevent="submitForm">
                        <div class="mb-4">
                            <x-input-label for="title">Title</x-input-label>
                            <x-text-input type="text" id="title" x-model="formData.title" required />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="currency_id">Currency</x-input-label>
                            <x-select-input id="currency_id" x-model="formData.currency_id" required>
                                <option value="">-- Select Currency --</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->CODE }}</option>
                                @endforeach
                            </x-select-input>
                        </div>
                        <div class="mb-4">
                            <x-input-label for="total_price">Total Price</x-input-label>
                            <x-text-input type="number" step="0.001" id="total_price" x-model="formData.total_price" required />
                        </div>
                        <!-- Button Section (Centered) -->
                        <div class="mt-4 w-full text-center">
                            <x-primary-button type="submit"
                                x-text="action === 'add' ? 'Create' : 'Update'"></x-primary-button>
                        </div>
                    </form>
                </div>
                <!-- Delete Sponsor Package Confirmation -->
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this Sponsor Package?</p>
                    <div class="flex justify-center">
                        <x-danger-button @click="confirmDelete()">Delete</x-danger-button>
                    </div>
                </div>

                <!-- Modal for Managing Options -->
                <div x-show="action === 'manageOption'">
                    <!-- Table for Managing Options -->
                    <div class="overflow-x-auto mb-4">
                        <table class="min-w-full border border-gray-300 rounded-lg shadow-lg">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Option Title</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="option in relatedOptions" :key="option.id">
                                    <tr class="bg-white even:bg-gray-50 hover:bg-gray-100">
                                        <td class="border border-gray-300 px-4 py-2" x-text="option.title"></td>
                                        <td class="border border-gray-300 px-4 py-2 space-x-2">
                                            <x-danger-button @click="unrelateOption(option.id)">Unrelate</x-danger-button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Form to Add New Option -->
                    <div class="mb-4">
                        <x-input-label for="optionSelect">Select Option to Relate</x-input-label>
                        <select id="optionSelect" x-model="selectedOptionId"
                            class="border border-gray-300 rounded-md p-2 w-full">
                            <option value="">-- Select Option --</option>
                            <template x-for="option in allOptions" :key="option.id">
                                <option :value="option.id" x-text="option.title"></option>
                            </template>
                        </select>
                        <div class="flex justify-center mt-0">
                            <x-primary-button type="button" @click="relateOption()">Relate</x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sponsorPackageModal() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    title: '',
                    currency_id: '',
                    total_price: ''
                },
                selectedSponsorPackageId: null,
                selectedSponsorPackage: null,
                relatedOptions: [], // Options related to the selected payment method
                allOptions: @json($allOptions), // All available options from backend
                selectedOptionId: null,

                openModal(action, sponsorPackage = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Sponsor Package' : action === 'edit' ?
                        'Edit Sponsor Package' :
                        action === 'delete' ? 'Delete Sponsor Package' : 'Manage Related Options: ';
                    if (sponsorPackage) {
                        this.selectedSponsorPackage = JSON.parse(sponsorPackage);
                        this.selectedSponsorPackageId = this.selectedSponsorPackage.id;
                        this.formData = {
                            ...this.selectedSponsorPackage
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit Sponsor Package: ' + this.selectedSponsorPackage.title;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Sponsor Package: ' + this.selectedSponsorPackage.title;
                        } else if (action === 'manageOption') {
                            this.modalTitle = 'Manage Related Options: ' + this.selectedSponsorPackage.title;
                            this.fetchRelatedOptions(this.selectedSponsorPackageId);
                        }
                    }
                },
                closeModal() {
                    this.isOpen = false; // Hide the main modal
                    this.resetForm();
                    this.selectedSponsorPackageId = null;
                },
                resetForm() {
                    this.formData = {
                        title: '',
                    currency_id: '',
                    total_price: ''
                    };
                    this.errors = null;
                },
                submitForm() {
                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ? '/sponsor_packages' :
                        `/sponsor_packages/${this.selectedSponsorPackageId}`;

                    fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                            },
                            body: JSON.stringify(this.formData),
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
                    fetch(`{{ route('sponsor_packages.destroy', '') }}/${this.selectedSponsorPackageId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(() => {
                            this.closeModal();
                            location.reload();
                        });
                },
                fetchRelatedOptions(id) {
                    fetch(`/sponsor_packages/${id}/options`)
                        .then(response => response.json())
                        .then(data => {
                            this.relatedOptions = data; // Set related options for the selected payment method
                        });
                },
                relateOption() {
                    if (!this.selectedOptionId) return;

                    fetch(`/sponsor_packages/${this.selectedSponsorPackageId}/options`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                            },
                            body: JSON.stringify({
                                option_id: this.selectedOptionId
                            })
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
                           // this.closeModal();
                           // location.reload();
                           this.fetchRelatedOptions(this.selectedSponsorPackageId);
                           this.errors = null;
                        })
                        .catch(error => {
                            this.errors = error;
                        });

                },
                unrelateOption(optionId) {
                    fetch(`/sponsor_packages/${this.selectedSponsorPackageId}/options/${optionId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                            }
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
                            this.fetchRelatedOptions(this.selectedSponsorPackageId);
                           this.errors = null;
                        })
                        .catch(error => {
                            this.errors = error;
                        });
                },
            };
        }
    </script>
@endsection
