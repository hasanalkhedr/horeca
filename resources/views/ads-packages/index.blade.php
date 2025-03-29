@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8" x-data="packageTable()">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Advertisement Packages</h1>
            <button @click="openModal('add')"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                <i class="fas fa-plus mr-2"></i>Create Package
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <form action="{{ route('ads-packages.index') }}" method="GET" class="flex items-center space-x-4">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Search packages..."
                           value="{{ request('search') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                @if(request('search'))
                    <a href="{{ route('ads-packages.index') }}"
                       class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Packages Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('ads-packages.index', ['sort' => 'id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                ID {!! request('sort') == 'id' ? (request('direction') == 'asc' ? '↑' : '↓') : '' !!}
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('ads-packages.index', ['sort' => 'title', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                Title {!! request('sort') == 'title' ? (request('direction') == 'asc' ? '↑' : '↓') : '' !!}
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Currencies
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Options
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($packages as $package)
                        <tr class="hover:bg-gray-50 cursor-pointer"
                            onclick="window.location.href='{{ route('ads-packages.show', $package->id) }}'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $package->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $package->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @foreach($package->currencies as $currency)
                                    <span class="inline-block bg-gray-100 rounded-full px-3 py-1 text-xs font-semibold text-gray-700 mr-1 mb-1">
                                        {{ $currency->CODE }}: {{ $currency->pivot->total_price }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @foreach($package->adsOptions as $opt)
                                    <span class="inline-block bg-gray-100 rounded-full px-3 py-1 text-xs font-semibold text-gray-700 mr-1 mb-1">
                                        {{ $opt->title }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" onclick="event.stopPropagation()">
                                <button @click="openModal('edit', {{ $package }})"
                                        class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="openModal('delete', {{ $package }})"
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $packages->links() }}
        </div>

        <!-- Create/Edit Modal -->
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

                <!-- Form -->
                <div x-show="action === 'add' || action === 'edit'" class="p-6">
                    <form @submit.prevent="submitForm">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" id="title" x-model="formData.title" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea id="description" x-model="formData.description" rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
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
                                            <button type="button" @click="removeCurrency(index)"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" @click="addCurrency"
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
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    x-text="action === 'add' ? 'Create' : 'Update'">
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
        function packageTable() {
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
                selectedPackageId: null,
                selectedPackage: null,

                openModal(action, package = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;

                    if (action === 'add') {
                        this.modalTitle = 'Create New Package';
                        this.formData = {
                            title: '',
                            description: '',
                            currencies: [],
                        };
                    } else if (package) {
                        this.selectedPackage = package;
                        this.selectedPackageId = package.id;

                        if (action === 'edit') {
                            this.modalTitle = 'Edit Package: ' + package.title;
                            this.formData = {
                                title: package.title,
                                description: package.description,
                                currencies: package.currencies ? package.currencies.map(c => ({
                                    id: c.id,
                                    price: c.pivot.total_price
                                })) : []
                            };
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Package: ' + package.title;
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
                    this.selectedPackageId = null;
                    this.selectedPackage = null;
                },

                submitForm() {
                    const data = {
                        title: this.formData.title,
                        description: this.formData.description,
                        currencies: this.formData.currencies.filter(c => c.id)
                    };

                    const url = this.action === 'add'
                        ? '{{ route("ads-packages.store") }}'
                        : '{{ route("ads-packages.update", "") }}/' + this.selectedPackageId;

                    const method = this.action === 'add' ? 'POST' : 'PUT';

                    fetch(url, {
                        method: method,
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
                    fetch('{{ route("ads-packages.destroy", "") }}/' + this.selectedPackageId, {
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
                        this.closeModal();
                        window.location.reload();
                    })
                    .catch(error => {
                        this.errors = { general: [error.message] };
                    });
                }
            };
        }
    </script>
@endsection
