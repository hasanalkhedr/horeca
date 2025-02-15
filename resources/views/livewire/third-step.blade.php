<div class="flex">
    <div class="flex flex-wrap px-4 mx-4">
        <div x-data="{ all_categories: @entangle('state.all_categories'), categories: @entangle('state.categories') }">
            <h2>Categories</h2>
            {{-- <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <template x-for="category in all_categories">
                    <div>
                        <x-input-label x-text="category.name"></x-input-label>
                        <template x-if="categories.includes(category.id)">
                            <x-text-input checked type="checkbox"
                                @change="$wire.toogleCategory(category.id,$event.target.checked)" />
                        </template>
                        <template x-if="!categories.includes(category.id)">
                            <x-text-input type="checkbox"
                                @change="$wire.toogleCategory(category.id,$event.target.checked)" />
                        </template>
                    </div>
                </template>
            </div> --}}
            <div class="space-y-2 grid grid-cols-2 gap-2">
                <template x-for="category in all_categories">
                    <label class="flex items-center space-x-2">
                        <template x-if="categories.includes(category.id)">
                            <input type="checkbox" checked @change="$wire.toogleCategory(category.id,$event.target.checked)"
                                class="form-checkbox h-5 w-5 text-blue-600 rounded">
                        </template>
                        <template x-if="!categories.includes(category.id)">
                            <input type="checkbox" @change="$wire.toogleCategory(category.id,$event.target.checked)"
                                class="form-checkbox h-5 w-5 text-blue-600 rounded">
                        </template>
                        <span class="text-gray-700" x-text="category.name"></span>
                    </label>
                </template>
            </div>
        </div>
        <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />
        <div>
            <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />
            <div>
                <h2>Prices of Event</h2>
                <div x-data="{ prices: @entangle('state.prices'), currencies: @entangle('state.currencies'), isOpen: false, action: '', price: @entangle('price') }">
                    <div class="w-full mb-4">
                        <table class="w-full border border-gray-300 rounded-lg shadow-lg">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th @click="console.log(prices)"
                                        class="border border-gray-300 px-1 py-0.5 text-left">Name</th>
                                    <th class="border border-gray-300 px-1 py-0.5 text-left">Currency</th>
                                    <th class="border border-gray-300 px-1 py-0.5 text-left">Amount</th>
                                    <th class="border border-gray-300 px-1 py-0.5 text-left">Description</th>
                                    <th class="border border-gray-300 px-1 py-0.5 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="ps in JSON.parse(prices)">
                                    <tr class="bg-white even:bg-gray-50 hover:bg-gray-100">
                                        <td class="border border-gray-300 px-1 py-0.5" x-text="ps.name"></td>
                                        <td class="border border-gray-300 px-1 py-0.5" style="width: 80px;"
                                            x-text="ps.currency_code"></td>
                                        <td class="border border-gray-300 px-1 py-0.5" style="width: 80px;"
                                            x-text="ps.amount"></td>
                                        <td class="border border-gray-300 px-1 py-0.5 text-xs" style="width: 80px;"
                                            x-text="ps.description"></td>
                                        <td class="border border-gray-300 px-1 py-0.5 space-x-2">
                                            <x-secondary-button type="button"
                                                @click="$wire.editPrice(ps);isOpen=true;action='Edit';">Edit</x-secondary-button>
                                            <x-danger-button type="button"
                                                @click="$wire.deletePrice(ps)">Delete</x-danger-button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-4">
                        <div class="flex justify-center mt-0">
                            <x-primary-button type="button" @click="isOpen=true;action='Add'">Add New
                                Price</x-primary-button>
                        </div>
                    </div>
                    <div x-show="isOpen" @click.away="isOpen = false" @keydown.escape.window="isOpen = false" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
                        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-semibold" x-text="action + ' Price'"></h2>
                                <button @click="isOpen = false" type="button"
                                    class="text-gray-600 text-3xl hover:text-gray-800 transition-colors duration-200">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div>
                                <div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                        <div>
                                            <x-input-label for="currency_id">Currency</x-input-label>
                                            <x-select-input id="currency_id" x-model="price.currency_id"
                                                x-required="isOpen? 'required': ''">
                                                <option value="">-- Select Currency --</option>
                                                <template x-for="currency in JSON.parse(currencies)">
                                                    <option x-model="currency.id" x-text="currency.CODE" />
                                                </template>
                                            </x-select-input>
                                        </div>
                                        <div>
                                            <x-input-label for="name">Name</x-input-label>
                                            <x-text-input id="name" x-model="price.name"
                                                x-required="isOpen? 'required': ''" />
                                        </div>
                                        <div>
                                            <x-input-label for="amount">Amount</x-input-label>
                                            <x-text-input type="number" id="amount" x-model="price.amount"
                                                x-required="isOpen? 'required': ''" />
                                        </div>
                                        <div>
                                            <x-input-label for="description">description</x-input-label>
                                            <x-text-input type="text" id="description" x-model="price.description" />
                                        </div>
                                    </div>
                                    <div class="mt-4 w-full text-center">
                                        <x-primary-button type="button"
                                            @click="isOpen=false;action == 'Add' ?$wire.addPrice(price): $wire.updatePrice(price)"
                                            x-text="action"></x-primary-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="mb-4">
                    <div class="flex justify-center mt-0">
                        <x-primary-button type="button" @click="$wire.addPrice()">Add New Price</x-primary-button>
                    </div>
                </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
