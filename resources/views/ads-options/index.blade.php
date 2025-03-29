@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="optionTable()">
        <div class="flex">
            <h1 class="w-1/2 text-2xl font-bold mb-4">Advertisement Options</h1>
            <div class="w-1/2 flex justify-end mb-4">
                <button @click="openModal('add')" class="bg-blue-500 text-white px-4 py-2 rounded">Create Option</button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-4">
            <form action="{{ route('ads-options.index') }}" method="GET" class="flex-1">
                <input type="text" name="search" placeholder="Search by title" value="{{ request('search') }}"
                    class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm">
            </form>
        </div>

        <!-- Options Table -->
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left">
                        <a href="{{ route('ads-options.index', ['sort' => 'id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                            ID {!! request('sort') == 'id' ? (request('direction') == 'asc' ? '&#9650;' : '&#9660;') : '' !!}
                        </a>
                    </th>
                    <th class="py-3 px-4 text-left">
                        <a href="{{ route('ads-options.index', ['sort' => 'title', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                            Title {!! request('sort') == 'title' ? (request('direction') == 'asc' ? '&#9650;' : '&#9660;') : '' !!}
                        </a>
                    </th>
                    <th class="py-3 px-4 text-left">Currencies</th>
                    <th class="py-3 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($options as $option)
                    <tr class="border-b {{ $loop->even ? 'bg-gray-50' : '' }} hover:bg-gray-100"
                        onclick="window.location.href='{{ route('ads-options.show', $option->id) }}'">
                        <td class="py-3 px-4 cursor-pointer">{{ $option->id }}</td>
                        <td class="py-3 px-4 cursor-pointer">{{ $option->title }}</td>
                        <td class="py-3 px-4" onclick="event.stopPropagation()">
                            @foreach ($option->currencies as $currency)
                                <span class="bg-gray-200 px-2 py-1 rounded text-sm mr-1">
                                    {{ $currency->CODE }}: {{ $currency->pivot->price }}
                                </span>
                            @endforeach
                        </td>
                        <td class="py-3 px-4" onclick="event.stopPropagation()">
                            <button @click="openModal('edit', {{ $option }})"
                                class="text-yellow-500 hover:text-yellow-700">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button @click="openModal('delete', {{ $option }})"
                                class="text-red-500 hover:text-red-700 ml-2">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $options->links() }}
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
                <div x-show="action === 'add' || action === 'edit'">
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
                                        @foreach(\App\Models\Settings\Currency::all() as $currency)
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
                            <x-primary-button type="submit" x-text="action === 'add' ? 'Create' : 'Update'"></x-primary-button>
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
                selectedOptionId: null,
                selectedOption: null,

                openModal(action, option = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Option' : action === 'edit' ? 'Edit Option' : 'Delete Option';

                    if (option) {
                        this.selectedOption = option;
                        this.selectedOptionId = this.selectedOption.id;
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
                            this.modalTitle = 'Edit Option: ' + this.selectedOption.title;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Option: ' + this.selectedOption.title;
                        }
                    } else {
                        this.formData.currencies = [];
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
                    this.selectedOptionId = null;
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

                    const method = this.action === 'add' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('ads-options.store') }}` :
                        `{{ route('ads-options.update', '') }}/${this.selectedOptionId}`;

                    fetch(url, {
                            method: method,
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
                    fetch(`{{ route('ads-options.destroy', '') }}/${this.selectedOptionId}`, {
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
