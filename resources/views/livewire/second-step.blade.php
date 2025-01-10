<div class="flex">
    <div class="flex flex-wrap px-4 mx-4">
        <div>
            <h2>V. A. T. Rate</h2>
            <div>
                <x-input-label for="vat_rate">VAT Rate</x-input-label>
                <x-text-input id="vat_rate" type="number" wire:model="state.vat_rate" required />
            </div>
        </div>
        {{-- <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />
            <div>
                <label for="paymentMethod">Payment Method and Bank Account:</label>
                <input id="paymentMethod" type="hidden" name="paymentMethod">
                <trix-editor input="paymentMethod" wire:model="state.payment_method"></trix-editor>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/2.0.0/trix.min.js"></script>
            </div> --}}
        <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />

        {{-- <div>
            <h2>Bank Account of Event</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name">Name</x-input-label>
                    <x-text-input id="name" wire:model="state.bank_account.name" required />
                </div>
                <div>
                    <x-input-label for="IBAN">IBAN</x-input-label>
                    <x-text-input id="IBAN" required wire:model="state.bank_account.IBAN" />
                </div>
                <div>
                    <x-input-label for="swift_code">Swift Code</x-input-label>
                    <x-text-input id="swift_code" required wire:model="state.bank_account.swift_code" />
                </div>
                <div>
                    <x-input-label for="account_name">Account Name</x-input-label>
                    <x-text-input id="account_name" required wire:model="state.bank_account.account_name" />
                </div>
            </div>
        </div>
        <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />
        <div>
            <h2>Payments of Event</h2>
            <div x-data="{ payment_rates: @entangle('payment_rates') }">
                <div class="w-full mb-4">
                    <table class="w-full border border-gray-300 rounded-lg shadow-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-1 py-0.5 text-left">Title</th>
                                <th class="border border-gray-300 px-1 py-0.5 text-left">Rate</th>
                                <th class="border border-gray-300 px-1 py-0.5 text-left">Order</th>
                                <th class="border border-gray-300 px-1 py-0.5 text-left">Date of Pay</th>
                                <th class="border border-gray-300 px-1 py-0.5 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="payment_rate in JSON.parse(payment_rates)">
                                <tr class="bg-white even:bg-gray-50 hover:bg-gray-100">
                                    <td class="border border-gray-300 px-1 py-0.5">
                                        <x-text-input id="title" x-model="payment_rate.title" required />
                                    </td>
                                    <td class="border border-gray-300 px-1 py-0.5" style="width: 80px;">
                                        <x-text-input id="rate" type="number" x-model="payment_rate.rate"
                                            required />
                                    </td>
                                    <td class="border border-gray-300 px-1 py-0.5" style="width: 80px;">
                                        <x-text-input id="order" type="number" x-model="payment_rate.order"
                                            required />
                                    </td>
                                    <td class="border border-gray-300 px-1 py-0.5">
                                        <x-text-input id="date_to_pay" type="date" x-model="payment_rate.date_to_pay"
                                            required />
                                    </td>
                                    <td class="border border-gray-300 px-1 py-0.5 space-x-2">
                                        <x-secondary-button type="button"
                                            @click="$wire.editPayment(payment_rate)">Save</x-secondary-button>
                                        <x-danger-button type="button"
                                            @click="$wire.deletePayment(payment_rate.id)">Delete</x-danger-button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="mb-4">
                    <div class="flex justify-center mt-0">
                        <x-primary-button type="button" @click="$wire.addPayment()">Add New Payment</x-primary-button>
                    </div>
                </div>
            </div>
        </div>
        <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" /> --}}
        <div>
            <h2>Currencies of Event</h2>
            <div x-data="{ currencies: @entangle('currencies'), all_currencies: @entangle('all_currencies'), isOpen: false, currency_id: @entangle('currency_id') }">
                <div class="w-full mb-4">
                    <table class="w-full border border-gray-300 rounded-lg shadow-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-1 py-0.5 text-left">Name</th>
                                <th class="border border-gray-300 px-1 py-0.5 text-left">CODE</th>
                                <th class="border border-gray-300 px-1 py-0.5 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="currency in JSON.parse(currencies)">
                                <tr class="bg-white even:bg-gray-50 hover:bg-gray-100">
                                    <td class="border border-gray-300 px-1 py-0.5"  >
                                        <x-text-input id="title" x-model="currency.name" disabled required />
                                    </td>
                                    <td class="border border-gray-300 px-1 py-0.5">
                                        <x-text-input id="rate" x-model="currency.CODE" required disabled />
                                    </td>
                                    <td class="border border-gray-300 px-1 py-0.5 space-x-2">
                                        <x-danger-button type="button"
                                            @click="$wire.unrelateCurrency(currency)">Delete</x-danger-button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="isOpen" @click.away="isOpen = false" @keydown.escape.window="isOpen = false" x-cloak
                    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
                    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold">Add Currency to event</h2>
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
                                        <x-select-input name="currency_id" id="currency_id" x-model="currency_id" >
                                            <option value="">-- Select Currency --</option>
                                            <template x-for="c in JSON.parse(all_currencies)">
                                                <option x-model="c.id" x-value="c.id" x-text="c.CODE"></option>
                                            </template>
                                            {{-- @foreach ($all_currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->CODE }}</option>
                                            @endforeach --}}
                                        </x-select-input>
                                    </div>
                                </div>
                                <div class="mt-4 w-full text-center">
                                    <x-primary-button type="button"
                                        @click="isOpen=false;$wire.relateCurrency(currency_id)">Add</x-primary-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="flex justify-center mt-0">
                        <x-primary-button type="button" @click="isOpen=true;">Add New
                            Currency</x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
