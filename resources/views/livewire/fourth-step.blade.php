<div class="flex">
    <div class="w-full px-4 mx-4">
        {{-- <div x-data="{ all_packages: @entangle('state.all_packages'), event_packages: @entangle('state.event_packages') }"> --}}
            <div><h2>Sponsorship Packages</h2>
            <div class="flex w-full">
                <div class="w-1/2">
                    <div x-data="{ all_packages: @entangle('state.all_packages')}">
                        <h1>Available Packages</h1>
                        <template x-for="package in JSON.parse(all_packages)">
                            <div x-data="{ open: false }" class="border rounded-md">
                                <button @click="open = !open" type="button"
                                    class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 flex justify-between items-center">
                                    <span class="font-medium text-gray-700" x-text="package.title"></span>
                                    <svg :class="{ 'transform rotate-180': open }"
                                        class="w-5 h-5 text-gray-500 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" x-collapse class="p-3 space-y-2">
                                    <div class="text-sm text-gray-600">
                                        <template x-for="opt in package.sponsor_options">
                                            <li><span x-text="opt.title"></span></li>
                                        </template>
                                    </div>
                                    <button wire:click="addPackageToEvent(package)" type="button"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                        Add this package to the event
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="w-1/2">
                    <div x-data="{ event_packages: @entangle('state.event_packages')}">
                        <h1>Currently Event Packages</h1>
                        <template x-for="package in JSON.parse(event_packages)">
                            <div x-data="{ open: false }" class="border rounded-md">
                                <button @click="open = !open" type="button"
                                    class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 flex justify-between items-center">
                                    <span class="font-medium text-gray-700" x-text="package.title"></span>
                                    <svg :class="{ 'transform rotate-180': open }"
                                        class="w-5 h-5 text-gray-500 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" x-collapse class="p-3 space-y-2">
                                    <div class="text-sm text-gray-600">
                                        <template x-for="opt in package.sponsor_options">
                                            <li><span x-text="opt.title"></span></li>
                                        </template>
                                    </div>
                                    <button wire:click="removePackageFromEvent(package)" type="button"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                        Remove this package from event
                                    </button>
                                </div>
                            </div>
                        </template>
                        <span @click="console.log(all_packages); console.log(event_packages);">Hasan HAsan</span>
                    </div>
                </div>
            </div>
        </div>
        <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />
        {{-- <div>
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
                </div>
            </div>
        </div> --}}
    </div>
</div>
