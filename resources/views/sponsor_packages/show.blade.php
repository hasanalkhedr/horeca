@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="packageShow()">
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="{{ route('sponsor_packages.index') }}" class="text-blue-500 hover:text-blue-700 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Packages
                </a>
            </div>
            <div class="flex space-x-3">
                <button @click="openModal('edit', {{ $sponsorPackage }})"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    <i class="fas fa-edit mr-2"></i>Edit
                </button>
                <button @click="openModal('delete', {{ $sponsorPackage }})"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                    <i class="fas fa-trash mr-2"></i>Delete
                </button>
            </div>
        </div>

        <!-- Package Details -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">{{ $sponsorPackage->title }}</h2>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'currencies'"
                        :class="{
                            'border-blue-500 text-blue-600': activeTab === 'currencies',
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'currencies'
                        }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Currencies
                </button>
                <button @click="activeTab = 'options'"
                        :class="{
                            'border-blue-500 text-blue-600': activeTab === 'options',
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'options'
                        }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Options
                </button>
            </nav>
        </div>

        <!-- Currencies Tab -->
        <div x-show="activeTab === 'currencies'" class="bg-white rounded-lg shadow overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Package Currencies</h3>
            </div>

            @if($sponsorPackage->currencies->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($sponsorPackage->currencies as $currency)
                        <div class="px-6 py-4 flex justify-between items-center">
                            <div>
                                <h4 class="text-md font-medium text-gray-900">{{ $currency->name }} ({{ $currency->CODE }})</h4>
                                <p class="text-sm text-gray-500">{{ $currency->symbol }} {{ number_format($currency->pivot->total_price, 2) }}</p>
                            </div>
                            <button @click="removeCurrency({{ $currency->id }})"
                                    class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-4 text-gray-500">
                    No currencies assigned to this package.
                </div>
            @endif

            <!-- Add Currency Form -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <h4 class="text-md font-medium text-gray-900 mb-3">Add Currency</h4>
                <form @submit.prevent="addCurrency" class="flex items-end space-x-3">
                    <div class="flex-1">
                        <label for="currency_id" class="block text-sm font-medium text-gray-700">Currency</label>
                        <select id="currency_id" x-model="newCurrency.id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Currency</option>
                            @foreach($availableCurrencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->CODE }} - {{ $currency->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" step="0.01" id="price" x-model="newCurrency.price" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-plus mr-2"></i>Add
                    </button>
                </form>
            </div>
        </div>

        <!-- Options Tab -->
        <div x-show="activeTab === 'options'" class="bg-white rounded-lg shadow overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Package Options</h3>
            </div>

            @if($sponsorPackage->sponsorOptions->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($sponsorPackage->sponsorOptions as $option)
                        <div class="px-6 py-4 flex justify-between items-center">
                            <div>
                                <h4 class="text-md font-medium text-gray-900">{{ $option->title }}</h4>
                            </div>
                            <button @click="removeOption({{ $option->id }})"
                                    class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-4 text-gray-500">
                    No options assigned to this package.
                </div>
            @endif

            <!-- Add Option Form -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <h4 class="text-md font-medium text-gray-900 mb-3">Add Option</h4>
                <form @submit.prevent="addOption" class="flex items-end space-x-3">
                    <div class="flex-1">
                        <label for="option_id" class="block text-sm font-medium text-gray-700">Option</label>
                        <select id="option_id" x-model="newOption.id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Option</option>
                            @foreach($availableOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-plus mr-2"></i>Add
                    </button>
                </form>
            </div>
        </div>

        <!-- Edit/Delete Modals -->
        <div x-show="isOpen" @click.away="closeModal()" @keydown.escape.window="closeModal()" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center border-b px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900" x-text="modalTitle"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Validation Errors -->
                <div x-show="errors" class="bg-red-50 border-l-4 border-red-400 p-4 mx-6 mt-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <template x-for="(error, field) in errors" :key="field">
                                <p x-text="error" class="text-sm text-red-700"></p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <div x-show="action === 'edit'" class="p-6">
                    <form @submit.prevent="submitForm">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="edit_title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" id="edit_title" x-model="formData.title" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Currency Prices</label>
                                <div class="mt-2 space-y-2">
                                    <template x-for="(currency, index) in formData.currencies" :key="index">
                                        <div class="flex space-x-2">
                                            <select x-model="currency.id"
                                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select Currency</option>
                                                @foreach(\App\Models\Settings\Currency::all() as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->CODE }} - {{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" step="0.01" x-model="currency.price" placeholder="Price"
                                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <button type="button" @click="removeCurrencyFromForm(index)"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" @click="addCurrencyToForm"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-plus mr-1"></i> Add Currency
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="closeModal()"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Update
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Delete Confirmation -->
                <div x-show="action === 'delete'" class="p-6">
                    <p class="text-gray-700">Are you sure you want to delete this package? This action cannot be undone.</p>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="button" @click="confirmDelete()"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function packageShow() {
            return {
                activeTab: 'currencies',
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    title: '',
                    currencies: [],
                },
                newCurrency: {
                    id: '',
                    price: ''
                },
                newOption: {
                    id: ''
                },
                selectedPackageId: {{ $sponsorPackage->id }},
                selectedPackage: @json($sponsorPackage),

                openModal(action) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;

                    if (action === 'edit') {
                        this.modalTitle = 'Edit Package: ' + this.selectedPackage.title;
                        this.formData = {
                            title: this.selectedPackage.title,
                            currencies: this.selectedPackage.currencies.map(c => ({
                                id: c.id,
                                price: c.pivot.total_price
                            }))
                        };
                    } else if (action === 'delete') {
                        this.modalTitle = 'Delete Package: ' + this.selectedPackage.title;
                    }
                },

                addCurrencyToForm() {
                    this.formData.currencies.push({ id: '', price: 0 });
                },

                removeCurrencyFromForm(index) {
                    this.formData.currencies.splice(index, 1);
                },

                addCurrency() {
                    if (!this.newCurrency.id) return;

                    fetch(`/sponsor_packages/${this.selectedPackageId}/add-currency`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            currency_id: this.newCurrency.id,
                            price: this.newCurrency.price
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(() => {
                        this.newCurrency = { id: '', price: 0 };
                        window.location.reload();
                    })
                    .catch(error => {
                        alert(error.message || 'Failed to add currency');
                    });
                },

                removeCurrency(currencyId) {
                    if (!confirm('Are you sure you want to remove this currency?')) return;

                    fetch(`/sponsor_packages/${this.selectedPackageId}/remove-currency/${currencyId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Delete failed');
                        }
                        return response.json();
                    })
                    .then(() => {
                        window.location.reload();
                    })
                    .catch(error => {
                        alert(error.message);
                    });
                },

                addOption() {
                    if (!this.newOption.id) return;

                    fetch(`/sponsor_packages/${this.selectedPackageId}/attach-option`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            sponsor_option_id: this.newOption.id
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(() => {
                        this.newOption = { id: '' };
                        window.location.reload();
                    })
                    .catch(error => {
                        alert(error.message || 'Failed to add option');
                    });
                },

                removeOption(optionId) {
                    if (!confirm('Are you sure you want to remove this option?')) return;

                    fetch(`/sponsor_packages/${this.selectedPackageId}/detach-option/${optionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Delete failed');
                        }
                        return response.json();
                    })
                    .then(() => {
                        window.location.reload();
                    })
                    .catch(error => {
                        alert(error.message);
                    });
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                },

                resetForm() {
                    this.formData = {
                        title: '',
                        currencies: [],
                    };
                    this.errors = null;
                },

                submitForm() {
                    const data = {
                        title: this.formData.title,
                        currencies: this.formData.currencies.filter(c => c.id)
                    };

                    fetch(`/sponsor_packages/${this.selectedPackageId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(() => {
                        this.closeModal();
                        window.location.reload();
                    })
                    .catch(error => {
                        this.errors = error.errors || { general: ['An error occurred. Please try again.'] };
                    });
                },

                confirmDelete() {
                    fetch(`/sponsor_packages/${this.selectedPackageId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Delete failed');
                        }
                        return response.json();
                    })
                    .then(() => {
                        window.location.href = '{{ route("sponsor_packages.index") }}';
                    })
                    .catch(error => {
                        this.errors = { general: [error.message] };
                    });
                }
            };
        }
    </script>
@endsection
