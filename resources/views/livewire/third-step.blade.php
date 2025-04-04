{{-- <div class="flex">
    <div x-date="{
        all_categories: @entangle('state.all_categories'),
        categories: @entangle('state.categories'),
        currencies: @entangle('state.currencies'),
        prices: @entangle('state.prices'),
        tempPrices: '',
        openModal: false,
        act: '',
        price: {
            index: '',
            id: '',
            name: '',
            description: '',
            currencies: []
        },
        checkMinPrice(currency_id, amount) {
            console.log(currency_id, amount);
        }
    }"
        x-init="currencies = currencies;
        tempPrices = typeof prices !== 'object' ? JSON.parse(prices) : prices;
        openModal = false;
        act = '';
        price = {
            index: '',
            id: '',
            name: '',
            description: '',
            currencies: []
        };" class="flex flex-wrap px-4 mx-4">
        <div>
            <h2>Categories</h2>
            <div class="space-y-2 grid grid-cols-2 gap-2" x-data="{ cats: JSON.parse(categories) }">
                <template x-for="category in all_categories">
                    <label class="flex items-center space-x-2">
                        <template x-if="cats.some(cat=>cat.id==category.id)">
                            <input type="checkbox" checked @change="$wire.toogleCategory(category,$event.target.checked)"
                                class="form-checkbox h-5 w-5 text-blue-600 rounded">
                        </template>
                        <template x-if="!cats.some(cat=>cat.id==category.id)">
                            <input type="checkbox" @change="$wire.toogleCategory(category,$event.target.checked)"
                                class="form-checkbox h-5 w-5 text-blue-600 rounded">
                        </template>
                        <span class="text-gray-700" x-text="category.name"></span>
                    </label>
                </template>
            </div>
        </div>
        <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />
        <div>
            <h2>Prices of Event</h2>
            <div class="w-full mb-4">
                <table class="w-full border border-gray-300 rounded-lg shadow-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-1 py-0.5 text-left">Name</th>
                            <th class="border border-gray-300 px-1 py-0.5 text-left">Price in Currencies</th>
                            <th class="border border-gray-300 px-1 py-0.5 text-left">Description</th>
                            <th class="border border-gray-300 px-1 py-0.5 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="ps in tempPrices">
                            <tr class="bg-white even:bg-gray-50 hover:bg-gray-100">
                                <td class="border border-gray-300 px-1 py-0.5" x-text="ps.name"></td>
                                <td class="border border-gray-300 px-1 py-0.5">
                                    <template x-for="cur in ps.currencies">
                                        <div>
                                            <span x-text="cur.amount"></span>
                                            <span x-text="cur.currency_code"></span>
                                        </div>
                                    </template>
                                </td>
                                <td class="border border-gray-300 px-1 py-0.5 text-xs" style="width: 80px;"
                                    x-text="ps.description"></td>
                                <td class="border border-gray-300 px-1 py-0.5 space-x-2">
                                    <x-secondary-button type="button"
                                        @click="price = {...ps, currencies: ps.currencies ? [...ps.currencies] : []}; openModal=true; act='Edit';console.log(price,act,openModal)">Edit</x-secondary-button>
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
                    <x-primary-button type="button" @click="openModal=true;act='Add'">Add New
                        Price</x-primary-button>
                </div>
            </div>
            <div x-show="openModal==true" @click.away="openModal=false;act=''"
                @keydown.escape.window="openModal=false;act=''" x-cloak
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold" x-text="act + ' Price'"></h2>
                        <button @click="openModal=false;act=''" type="button"
                            class="text-gray-600 text-3xl hover:text-gray-800 transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                            <div>
                                <x-input-label for="name">Name</x-input-label>
                                <x-text-input id="name" x-model="price.name"
                                    x-required="openModal? 'required': ''" />
                            </div>
                            <div>
                                <x-input-label for="description">description</x-input-label>
                                <x-text-input type="text" id="description" x-model="price.description" />
                            </div>
                            <table>
                                <template x-for="curr in price.currencies">
                                    <tr>
                                        <td>
                                            <x-select-input x-model="curr.currency_id"
                                                x-required="openModal? 'required': ''">
                                                <option value="">-- Select Currency --</option>
                                                <template x-for="currency in JSON.parse(currencies)">
                                                    <option x-model="currency.id" x-text="currency.CODE" />
                                                </template>
                                            </x-select-input>
                                        </td>
                                        <td>
                                            <x-text-input type="number" x-model="curr.amount"
                                                @blur="checkMinPrice(curr.currency_id, curr.amount)"
                                                x-required="openModal? 'required': ''" />
                                        </td>
                                    </tr>
                                </template>
                            </table>
                            <x-secondary-button type="button"
                                @click="price.currencies.push({currency_id: '', currency_code: '', amount: ''})">Add
                                Currency</x-secondary-button>

                        </div>
                        <div class="mt-4 w-full text-center">
                            <x-primary-button type="button"
                                @click="act == 'Add' ?$wire.addPrice(price): $wire.updatePrice(price);openModal=false;act=''"
                                x-text="act"></x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}

<div class="flex">
    <div class="flex flex-wrap px-4 mx-4">
        <div x-data="{
            all_categories: @entangle('state.all_categories'),
            categories: @entangle('state.categories')
        }">
            <h2>Categories</h2>
            <div class="space-y-2 grid grid-cols-2 gap-2" x-data="{ cats: JSON.parse(categories) }">
                <template x-for="category in all_categories">
                    <label class="flex items-center space-x-2">
                        <template x-if="cats.some(cat=>cat.id==category.id)">
                            <input type="checkbox" checked @change="$wire.toogleCategory(category,$event.target.checked)"
                                class="form-checkbox h-5 w-5 text-blue-600 rounded">
                        </template>
                        <template x-if="!cats.some(cat=>cat.id==category.id)">
                            <input type="checkbox" @change="$wire.toogleCategory(category,$event.target.checked)"
                                class="form-checkbox h-5 w-5 text-blue-600 rounded">
                        </template>
                        <span class="text-gray-700" x-text="category.name"></span>
                    </label>
                </template>
            </div>
        </div>
        <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />
        <div class="w-full">
        <h2>Prices of Event</h2>
        </div>
        <div x-data="{
            prices: @entangle('state.prices'),
            currencies: @entangle('state.currencies'),
            openModal: false,
            act: '',
            price: {
                index: '',
                id: '',
                name: '',
                description: '',
                currencies: []
            },
            checkMinPrice(currency_id, amount) {
                console.log(currency_id, amount);
            }
        }">
            <div class="w-full mb-4">
                <table class="w-full border border-gray-300 rounded-lg shadow-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-1 py-0.5 text-left">
                                Name</th>
                            <th class="border border-gray-300 px-1 py-0.5 text-left">Price in Currencies</th>
                            <th class="border border-gray-300 px-1 py-0.5 text-left">Description</th>
                            <th class="border border-gray-300 px-1 py-0.5 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="ps in JSON.parse(prices)">
                            <tr class="bg-white even:bg-gray-50 hover:bg-gray-100">
                                <td class="border border-gray-300 px-1 py-0.5" x-text="ps.name"></td>
                                <td class="border border-gray-300 px-1 py-0.5">
                                    <template x-for="cur in ps.currencies">
                                        <div>
                                            <span x-text="cur.amount"></span>
                                            <span x-text="cur.currency_code"></span>
                                        </div>
                                    </template>
                                </td>
                                <td class="border border-gray-300 px-1 py-0.5 text-xs" style="width: 80px;"
                                    x-text="ps.description"></td>
                                <td class="border border-gray-300 px-1 py-0.5 space-x-2">
                                    <x-secondary-button type="button"
                                        @click="price = {...ps, currencies: ps.currencies ? [...ps.currencies] : []}; openModal=true; act='Edit';console.log(price,act,openModal)">Edit</x-secondary-button>
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
                    <x-primary-button type="button" @click="openModal=true;act='Add'">Add New
                        Price</x-primary-button>
                </div>
            </div>
            <div x-show="openModal==true" @click.away="openModal=false;act='';price={index: '',id: '',name: '',description: '',currencies: []}"
                @keydown.escape.window="openModal=false;act='';price={index: '',id: '',name: '',description: '',currencies: []}" x-cloak
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold" x-text="act + ' Price'"></h2>
                        <button @click="openModal=false;act='';price={index: '',id: '',name: '',description: '',currencies: []}" type="button"
                            class="text-gray-600 text-3xl hover:text-gray-800 transition-colors duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                            <div>
                                <x-input-label for="name">Name</x-input-label>
                                <x-text-input id="name" x-model="price.name"
                                    x-required="openModal? 'required': ''" />
                            </div>
                            <div>
                                <x-input-label for="description">description</x-input-label>
                                <x-text-input type="text" id="description" x-model="price.description" />
                            </div>
                            <table>
                                <template x-for="curr in price.currencies">
                                    <tr>
                                        <td>
                                            <x-select-input x-model="curr.currency_id"
                                                x-required="openModal? 'required': ''">
                                                <option value="">-- Select Currency --</option>
                                                <template x-for="currency in JSON.parse(currencies)">
                                                    <template x-if="curr.currency_id==currency.id">
                                                        <option x-model="currency.id" x-text="currency.CODE" selected/>
                                                    </template>
                                                    <template x-if="curr.currency_id!=currency.id">
                                                        <option x-model="currency.id" x-text="currency.CODE" />
                                                    </template>
                                                </template>
                                            </x-select-input>
                                        </td>
                                        <td>
                                            <x-text-input type="number" x-model="curr.amount"
                                                @blur="checkMinPrice(curr.currency_id, curr.amount)"
                                                x-required="openModal? 'required': ''" />
                                        </td>
                                    </tr>
                                </template>
                            </table>
                            <x-secondary-button type="button"
                                @click="price.currencies.push({currency_id: '', currency_code: '', amount: ''})">Add
                                Currency</x-secondary-button>
                        </div>
                        <div class="mt-4 w-full text-center">
                            <x-primary-button type="button"
                                @click="act == 'Add' ?$wire.addPrice(price): $wire.updatePrice(price);openModal=false;act=''"
                                x-text="act"></x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
