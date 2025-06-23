@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="optionTable()">
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="{{ route('eff-ads-options.index') }}" class="text-blue-500 hover:text-blue-700">
                    &larr; Back to Options
                </a>
            </div>
            <div class="flex space-x-4">
                <button @click="openModal('edit', {{ $adsOption }})"
                    class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button @click="openModal('delete', {{ $adsOption }})"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>

        <!-- Option Details Card -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-4">{{ $adsOption->title }}</h2>
            <p class="text-gray-600 mb-6">{{ $adsOption->description }}</p>

            <!-- Currencies Table -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-4">Pricing</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Currency</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($adsOption->currencies as $currency)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium">{{ $currency->name }} ({{ $currency->CODE }})</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $currency->symbol }} {{ number_format($currency->pivot->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <form action="{{ route('eff-ads-options.remove-currency', [$adsOption->id, $currency->id]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to remove this currency?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Add Currency Form -->
                <div class="mt-4">
                    <h4 class="text-md font-semibold mb-2">Add Currency</h4>
                    <form action="{{ route('eff-ads-options.add-currency', $adsOption->id) }}" method="POST" class="flex items-end space-x-4">
                        @csrf
                        <div class="flex-1">
                            <x-input-label for="currency_id">Currency</x-input-label>
                            <select name="currency_id" id="currency_id" required class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Currency</option>
                                @foreach ($availableCurrencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->CODE }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <x-input-label for="price">Price</x-input-label>
                            <x-text-input type="number" step="0.01" id="price" name="price" required />
                        </div>
                        <x-primary-button type="submit">Add Currency</x-primary-button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modals -->
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
                <div x-show="action === 'edit'">
                    <form @submit.prevent="submitForm">
                        <div class="mb-4">
                            <x-input-label for="title">Option Title</x-input-label>
                            <x-text-input id="title" x-model="formData.title" required />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="description">Description</x-input-label>
                            <textarea id="description" x-model="formData.description"
                                class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm"></textarea>
                        </div>

                        <h3 class="text-lg font-semibold mb-2">Currency Prices</h3>
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(currency, index) in formData.currencies" :key="index">
                                <div class="flex items-center space-x-2">
                                    <select x-model="currency.id" class="flex-1 px-4 py-2 rounded-md border-gray-300 shadow-sm">
                                        <option value="">Select Currency</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->CODE }} ({{ $currency->name }})</option>
                                        @endforeach
                                    </select>
                                    <x-text-input type="number" step="0.01" x-model="currency.price" placeholder="Price" />
                                    <button type="button" @click="removeCurrency(index)" class="text-red-500">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="addCurrency" class="bg-gray-200 px-4 py-2 rounded">
                                <i class="fas fa-plus"></i> Add Currency
                            </button>
                        </div>

                        <div class="mt-4 w-full text-center">
                            <x-primary-button type="submit">Update</x-primary-button>
                        </div>
                    </form>
                </div>
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this option?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function optionTable() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    title: '',
                    description: '',
                    currencies: [],
                },
                selectedOptionId: {{ $adsOption->id }},
                selectedOption: @json($adsOption),

                openModal(action, option = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    if (action === 'edit' || action === 'delete') {
                        this.modalTitle = action === 'edit' ? 'Edit Option' : 'Delete Option';
                        if (option) {
                            this.selectedOption = option;
                            this.selectedOptionId = option.id;
                            this.formData = {
                                id: option.id,
                                title: option.title,
                                description: option.description,
                                currencies: option.currencies ? option.currencies.map(currency => ({
                                    id: currency.id,
                                    price: currency.pivot.price
                                })) : []
                            };
                            if (action === 'edit') {
                                this.modalTitle = 'Edit Option: ' + option.title;
                            } else if (action === 'delete') {
                                this.modalTitle = 'Delete Option: ' + option.title;
                            }
                        }
                    }
                },

                addCurrency() {
                    this.formData.currencies.push({ id: '', price: 0 });
                },

                removeCurrency(index) {
                    this.formData.currencies.splice(index, 1);
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                },

                resetForm() {
                    this.formData = {
                        title: '',
                        description: '',
                        currencies: [],
                    };
                    this.errors = null;
                },

                submitForm() {
                    const data = {
                        title: this.formData.title,
                        description: this.formData.description,
                        currencies: this.formData.currencies.filter(c => c.id)
                    };

                    fetch(`{{ route('eff-ads-options.update', '') }}/${this.selectedOptionId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data),
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
                        this.errors = error.errors || {
                            general: ['Something went wrong. Please try again.']
                        };
                    });
                },

                confirmDelete() {
                    fetch(`{{ route('eff-ads-options.destroy', '') }}/${this.selectedOptionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(() => {
                        this.closeModal();
                        window.location.href = '{{ route('eff-ads-options.index') }}';
                    });
                },
            };
        }
    </script>
@endsection
