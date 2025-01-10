@extends('layouts.app')
@section('content')
    <div x-data="brandModal()" class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Brands {{ 'of company: ' . $company->name }}</h1>

        <!-- Button to Add Brand -->
        <x-primary-button @click="openModal('add')">Add Brand</x-primary-button>

        <!-- Table of Brands -->
        @if ($company)
            @livewire('brand-table', ['company' => $company])
        @else
            @livewire('brand-table')
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
                                    <x-input-label for="name">Brand Name</x-input-label>
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
                                    <x-input-label for="logo">Brand Logo</x-input-label>
                                    <x-text-input id="logo" x-model="formData.logo" />
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
                    <p class="mb-4">Are you sure you want to delete this brand?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function brandModal() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    name: '',
                    logo: '',
                    company_id: ''
                },
                selectedBrandId: null,
                selectedBrand: null,

                openModal(action, brand = null) {

                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Brand' : action === 'edit' ? 'Edit Brand' :
                        'Delete Brand';
                    if (brand) {
                        this.selectedBrand = JSON.parse(brand);
                        this.selectedBrandId = this.selectedBrand.id;
                        this.formData = {
                            ...this.selectedBrand
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit Brand: ' + this.selectedBrand.name + '|' + this.selectedBrand.CODE;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Brand: ' + this.selectedBrand.name + '|' + this.selectedBrand
                            .CODE;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedBrandId = null;
                },

                resetForm() {
                    this.formData = {
                        name: '',
                    logo: '',
                    company_id: ''
                    };
                    this.errors = null;
                },

                submitForm() {
                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('brands.store') }}` :
                        `{{ route('brands.update', '') }}/${this.selectedBrandId}`;

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
                    fetch(`{{ route('brands.destroy', '') }}/${this.selectedBrandId}`, {
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
