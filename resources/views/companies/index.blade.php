@extends('layouts.app')
@section('content')
    <div x-data="companyModal()" class="max-w-7xl mx-auto p-6">
        <div class="flex">
            <h1 class="text-3xl w-1/2 font-semibold mb-1">Companies</h1>
            <!-- Button to Add Company -->
            <div class="w-1/2 justify-end text-right">
                <x-primary-button @click="openModal('add')">Add Company</x-primary-button>
            </div>
        </div>
        <!-- Table of Companies -->
        @livewire('company-table')

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
                                    <x-input-label for="name">Company Name</x-input-label>
                                    <x-text-input id="name" x-model="formData.name" required />
                                </div>
                                <div>
                                    <x-input-label for="CODE">Company CODE</x-input-label>
                                    <x-text-input id="CODE" x-model="formData.CODE" required />
                                </div>
                                <div>
                                    <x-input-label for="stand_name">Stand Name (Title)</x-input-label>
                                    <x-text-input id="stand_name" x-model="formData.stand_name" />
                                </div>
                                <div>
                                    <x-input-label for="logo">Logo</x-input-label>
                                    <x-text-input id="logo" x-model="formData.logo" />
                                </div>
                                <div>
                                    <x-input-label for="commerical_registry_number">Commerical Registry
                                        Number</x-input-label>
                                    <x-text-input id="commerical_registry_number"
                                        x-model="formData.commerical_registry_number" />
                                </div>
                                <div>
                                    <x-input-label for="vat_number">V.A.T Number (M.O.F.N)</x-input-label>
                                    <x-text-input id="vat_number" x-model="formData.vat_number" />
                                </div>
                                <div>
                                    <x-input-label for="country">Country</x-input-label>
                                    <x-text-input id="country" x-model="formData.country" />
                                </div>
                                <div>
                                    <x-input-label for="city">City</x-input-label>
                                    <x-text-input id="city" x-model="formData.city" />
                                </div>
                                <div>
                                    <x-input-label for="street">Street</x-input-label>
                                    <x-text-input id="street" x-model="formData.street" />
                                </div>
                                <div>
                                    <x-input-label for="po_pox">P.O.Box</x-input-label>
                                    <x-text-input id="po_pox" x-model="formData.po_pox" />
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
                                    <x-input-label for="additional_number">Additional Number</x-input-label>
                                    <x-text-input id="additional_number" x-model="formData.additional_number" />
                                </div>
                                <div>
                                    <x-input-label for="fax">Fax</x-input-label>
                                    <x-text-input id="fax" x-model="formData.fax" />
                                </div>
                                <div>
                                    <x-input-label for="email">Email</x-input-label>
                                    <x-text-input type="email" id="email" x-model="formData.email" />
                                </div>
                                <div>
                                    <x-input-label for="website">Website</x-input-label>
                                    <x-text-input id="website" x-model="formData.website" />
                                </div>
                                <div>
                                    <x-input-label for="facebook_link">Facebook Page</x-input-label>
                                    <x-text-input id="facebook_link" x-model="formData.facebook_link" />
                                </div>
                                <div>
                                    <x-input-label for="instagram_link">Instagram</x-input-label>
                                    <x-text-input id="instagram_link" x-model="formData.instagram_link" />
                                </div>
                                <div>
                                    <x-input-label for="x_link">X (twitter) account</x-input-label>
                                    <x-text-input id="x_link" x-model="formData.x_link" />
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
                    <p class="mb-4">Are you sure you want to delete this company?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function companyModal() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    name: '',
                    CODE: '',
                    commerical_registry_number: '',
                    vat_number: '',
                    country: '',
                    city: '',
                    street: '',
                    po_box: '',
                    mobile: '',
                    phone: '',
                    additional_number: '',
                    fax: '',
                    email: '',
                    website: '',
                    facebook_link: '',
                    instagram_link: '',
                    x_link: '',
                    stand_name: '',
                    logo: ''
                },
                selectedCompanyId: null,
                selectedCompany: null,

                openModal(action, company = null) {

                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Company' : action === 'edit' ? 'Edit Company' :
                        'Delete Company';
                    if (company) {
                        this.selectedCompany = JSON.parse(company);
                        this.selectedCompanyId = this.selectedCompany.id;
                        this.formData = {
                            ...this.selectedCompany
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit Company: ' + this.selectedCompany.name + '|' + this.selectedCompany
                            .CODE;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Company: ' + this.selectedCompany.name + '|' + this.selectedCompany
                                .CODE;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedCompanyId = null;
                },

                resetForm() {
                    this.formData = {
                        name: '',
                        CODE: '',
                        commerical_registry_number: '',
                        vat_number: '',
                        country: '',
                        city: '',
                        street: '',
                        po_box: '',
                        mobile: '',
                        phone: '',
                        additional_number: '',
                        fax: '',
                        email: '',
                        website: '',
                        facebook_link: '',
                        instagram_link: '',
                        x_link: '',
                        stand_name: '',
                        logo: ''
                    };
                    this.errors = null;
                },

                submitForm() {
                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('companies.store') }}` :
                        `{{ route('companies.update', '') }}/${this.selectedCompanyId}`;

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
                    fetch(`{{ route('companies.destroy', '') }}/${this.selectedCompanyId}`, {
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
