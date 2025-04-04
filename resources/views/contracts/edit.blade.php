@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 text-center">Contract Form</h1>
    <p class="text-center text-gray-600">Fill out the contract form with the required details.</p>

    <form action="{{ route('contracts.update', $contract->id) }}" method="POST"
        class="w-full mx-auto bg-white shadow-lg rounded-lg px-2 py-1 space-y-1" x-data="contractForm()"
        x-init="init()">
        @method('PUT')
        @csrf
        <h2 class="text-xl font-semibold text-gray-800">Contract Basic Information</h2>
        <div class="flex flex-wrap -mx-3 mb-2">
            <div class="w-1/3 px-3">
                <x-input-label>
                    Contract #
                </x-input-label>
                <x-text-input name="contract_no" value="{{ $contract->contract_no }}" />
            </div>
            <div class="w-1/3 px-3">
                <x-input-label>
                    Contract Status
                </x-input-label>
                <x-text-input name="contract_status" disabled>
                    <option value="draft">{{ $contract->status }}</option>
                </x-text-input>
            </div>
        </div>

        <!-- Client Info Section -->
        @if (in_array('company-details-component', $report->components))
            <x-form-divider>Client Info:</x-form-divider>
            <livewire:client-select model="App\Models\Company" dependentModel="App\Models\Client" foreignKey="company_id"
                placeholder="Choose a Company" parentLabel="Company" childLabel="Exhibition Co-ordinator"
                child2Label="Daily Contact Person" :parentField="$contract->company_id ?? null" :coordinatorId="$contract->exhabition_coordinator ?? null" :contactPerson="$contract->contact_person ?? null" />
            <x-form-divider>Categories:</x-form-divider>
            <div class="space-y-2 grid grid-cols-3 gap-2">
                @foreach ($categories as $category)
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="categories[]" value="{{ $category }}"
                            class="form-checkbox h-5 w-5 text-blue-600 rounded single-checkbox" @checked($contract->category_id == $category->id)
                            @change="uncheckOthers($event)">
                        <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>
        @endif

        <!-- Stand Info Section -->
        @if (in_array('price-section-component', $report->components))
            <x-form-divider>Stand Info:</x-form-divider>
            <div class="flex">
                <div class="flex flex-wrap -mx-3 mb-2 w-3/4">
                    <div class="w-full px-3">
                        <x-input-label for="stand_id">Stand:</x-input-label>
                        <x-select-input name="stand_id" id="stand_id" required x-model="standId"
                            @change="calculateTotal()">
                            <option value="">-- Select Value --</option>
                            @foreach ($stands as $stand)
                                <option value="{{ $stand->id }}" data-space="{{ $stand->space }}">
                                    {{ $stand->no }}|{{ $stand->space }}
                                </option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="w-full px-3">
                        <x-input-label for="price_id">Price:</x-input-label>
                        @foreach ($prices as $price)
                            <div class="block">
                                <input type="radio" name="price_id" value="{{ $price->id }}"
                                    data-price="{{ $price->Currencies()->where('currencies.id', $report->Currency->id)->first()->pivot->amount }}" x-model="priceId" @change="calculateTotal()"/> {{ $price->name }} | {{ $report->Currency->CODE }} |
                                    {{ $price->Currencies()->where('currencies.id', $report->Currency->id)->first()->pivot->amount }}}
                            </div>
                        @endforeach
                        @if ($report->special_price)
                            <div class="block">
                                <input type="radio" name="price_id" value="0" id="special_price_radio"
                                    x-model="priceId" @click="toggleSpecialPrice()" />Special
                                pavilion
                                specify
                                <input
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    name="special_price_amount" id="special_price_amount" type="number" step="0.01"
                                    x-model="specialPriceAmount" :disabled="priceId != 0" @input="calculateTotal()" />
                            </div>
                        @endif
                    </div>
                </div>
                <div class="w-1/4 total text-center p-2">
                    <div class="text-lg font-bold  bg-gray-200">
                        Total Space Amount: <span x-text="formatCurrency(spaceTotal)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}
                        <input type="hidden" name="space_amount" x-model="spaceTotal">
                        <div>
                            Discount: <input
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-1/2"
                                type="number" name="space_discount" x-model="spaceDiscount" step="0.01"
                                @blur="checkMinPrice()">{{ $report->Currency->CODE ?? 'USD' }}
                        </div>
                        Net Space Amount: <span x-text="formatCurrency(spaceNet)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}
                        <input type="hidden" name="space_net" x-model="spaceNet">
                    </div>
                </div>
            </div>
        @endif

        <!-- Extra Water/Electricity Section -->
        @if (in_array('water-section', $report->components))
            <x-form-divider>Extra Water/Electricity:</x-form-divider>
            <label class="inline-flex items-center">
                <input type="checkbox" name="if_water" value="1" @checked($contract->if_water)
                    class="text-indigo-600 focus:ring-indigo-500 border-gray-300" x-model="ifWater"
                    @change="calculateTotal(); resetWaterElectricityAmount()">
                <span class="ml-2 text-sm text-gray-700"> Water point needed (if available)</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="if_electricity" class="text-indigo-600 focus:ring-indigo-500 border-gray-300"
                    value="1" @checked($contract->if_electricity) id="if_electricity" x-model="ifElectricity"
                    @change="calculateTotal(); resetWaterElectricityAmount()" /> Extra electricity
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="electricity_text" id="electricity_text" type="text"
                    value="{{ $contract->electricity_text }}" placeholder="WATT Needed" :disabled="!ifElectricity" />
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="water_electricity_amount" placeholder="Water & electricity Amount" type="number"
                    step="0.01" value="{{ $contract->water_electricity_amount }}" x-model="waterElectricityAmount"
                    @input="calculateTotal()" />
            </label>
        @endif

        @if (in_array('new-product-section', $report->components))
            <x-form-divider>New Product to launch</x-form-divider>
            <div class="w-full px-3">
                <x-input-label for="new_product">New Product to launch</x-input-label>
                <x-text-input name="new_product" id="new_product" value="{{ $contract->new_product }}" />
            </div>
        @endif

        <!-- Sponsor Package Section -->
        @if (in_array('sponsor-section', $report->components))
            <x-form-divider>Sponsor Package:</x-form-divider>
            <div class="flex">
                <div class="flex flex-wrap -mx-3 mb-2 w-3/4">
                    <div class="w-full px-3">
                        <x-input-label for="sponsor_package_id">Choose Sponsor Package:</x-input-label>
                        <x-select-input name="sponsor_package_id" id="sponsor_package_id" x-model="sponsorPackageId"
                            @change="calculateTotal()">
                            <option value="">-- Select Value --</option>
                            @foreach ($sponsor_packages as $package)
                                <option value="{{ $package->id }}"
                                    data-price="{{ $package->currencies->where('id', $report->Currency->id)->first()
                                        ? $package->currencies->where('id', $report->Currency->id)->first()->pivot->total_price
                                        : 0 }}"
                                    @selected($package->id == $contract->sponsor_package_id)>
                                    {{ $package->title }}|{{ $package->currencies->where('id', $report->Currency->id)->first()
                                        ? $package->currencies->where('id', $report->Currency->id)->first()->pivot->total_price
                                        : 0 }}
                                    {{ $report->Currency->CODE }}
                                </option>
                            @endforeach
                        </x-select-input>
                    </div>
                </div>
                <div class="w-1/4 total text-center p-2">
                    <div class="text-lg font-bold  bg-gray-200">
                        Sponsorship Total: <span x-text="formatCurrency(sponsorTotal)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}
                        <input type="hidden" name="sponsor_amount" x-model="sponsorTotal">
                        <div>
                            Discount: <input
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-1/2"
                                type="number" name="sponsor_discount" x-model="sponsorDiscount" step="0.01"
                                @input="calculateTotal()">{{ $report->Currency->CODE ?? 'USD' }}
                        </div>
                        Net Space Amount: <span x-text="formatCurrency(sponsorNet)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}
                        <input type="hidden" name="sponsor_net" x-model="sponsorNet">
                    </div>
                </div>
            </div>
        @endif

        @if (in_array('advertisement-section', $report->components))
            <x-form-divider>Advertisement in Hospitality News</x-form-divider>
            <div class="flex">
                <div class="flex flex-wrap -mx-3 mb-2 w-3/4">
                    @foreach ($contract->Event->AdsPackages as $package)
                        <div class="flex justify-between">
                            <div class="w-full font-bold text-lg leading-none">
                                <h1>{{ $package->title }}</h1>
                            </div>
                        </div>
                        <table class="w-full">
                            @foreach ($package->AdsOptions as $option)
                                @if ($loop->even)
                                    @continue
                                @endif
                                <tr class="text-xs">
                                    <td class="w-2/6">
                                        <input @change="calculateTotal()" name="ads_check[]"
                                            value="{{ $package->id }}_{{ $option->id }}" @checked(in_array($package->id . '_' . $option->id, $contract->ads_check))
                                            data-price="{{ $option->Currencies->where('id', $report->Currency->id)->first()
                                                ? $option->Currencies->where('id', $report->Currency->id)->first()->pivot->price
                                                : 0 }}"
                                            type="checkbox" class="mr-1">{{ $option->title }}
                                    </td>
                                    <td class="w-1/6">
                                        {{ $option->Currencies->where('id', $report->Currency->id)->first()
                                            ? $option->Currencies->where('id', $report->Currency->id)->first()->pivot->price
                                            : 0 }}
                                        {{ $report->Currency->CODE }}</td>
                                    @if (!$loop->last)
                                        <td class="w-2/6">
                                            <input type="checkbox" @change="calculateTotal()" name="ads_check[]"
                                                value="{{ $package->id }}_{{ $package->AdsOptions[$loop->index + 1]->id }}"
                                                @checked(in_array($package->id . '_' . $package->AdsOptions[$loop->index + 1]->id, $contract->ads_check))
                                                data-price="{{ $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $report->Currency->id)->first()
                                                    ? $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $report->Currency->id)->first()->pivot->price
                                                    : 0 }}"
                                                class="mr-1">{{ $package->AdsOptions[$loop->index + 1]->title }}
                                        </td>
                                        <td class="w-1/6">
                                            {{ $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $report->Currency->id)->first()
                                                ? $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $report->Currency->id)->first()->pivot->price
                                                : 0 }}
                                            {{ $report->Currency->CODE }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </table>
                    @endforeach
                </div>
                <div class="w-1/4 total text-center p-2">
                    <div class="text-lg font-bold  bg-gray-200">
                        Advertisement Total: <span x-text="formatCurrency(adsTotal)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}
                        <input type="hidden" name="advertisment_amount" x-model="adsTotal">
                        <div>
                            Discount: <input
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-1/2"
                                type="number" name="ads_discount" x-model="adsDiscount" step="0.01"
                                @input="calculateTotal()">{{ $report->Currency->CODE ?? 'USD' }}
                        </div>
                        Net Space Amount: <span x-text="formatCurrency(adsNet)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}
                        <input type="hidden" name="ads_net" x-model="adsNet">
                    </div>
                </div>
            </div>
        @endif

        @if (in_array('notes-section', $report->components))
            <x-form-divider>Contract Notes:</x-form-divider>
            <div class="flex flex-wrap -mx-3 mb-2 w-full">
                <div class="w-full px-3">
                    <x-input-label for="notes1">Notes:</x-input-label>
                    <x-text-input name="notes1" id="notes1" value="{{ $contract->notes1 }}" />
                </div>
                <div class="w-full px-3">
                    <x-input-label for="notes2">Notes:</x-input-label>
                    <x-text-input name="notes2" id="notes2" value="{{ $contract->notes2 }}" />
                </div>
            </div>
        @endif

        <h2 class="text-xl font-semibold text-gray-800">Who is the seller of this contract?</h2>
        <div class="flex flex-wrap -mx-3 mb-2">
            <div class="w-full px-3">
                <x-select-input name="seller" id="seller">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($contract->seller == $user->id)>
                            {{ $user->name }}|{{ $user->getRoleNames() }}
                        </option>
                    @endforeach
                </x-select-input>
            </div>
        </div>
        <div class="flex">
            <!-- Total Cost Display -->
            <div class="w-1/2 total pt-4 py-3 text-right justify-between">
                <div class="text-2xl font-bold  bg-gray-200">
                    <p> Total Amount: <span class="text-red-400" x-text="formatCurrency(sub_total_1)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}</p>
                    <p> Total Discount (-): <span class="text-red-400" x-text="formatCurrency(d_i_a)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}</p>
                    <p> Net Total: <span class="text-red-400" x-text="formatCurrency(sub_total_2)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}</p>
                    <p> VAT (+{{ $contract->Event->vat_rate }}%): <span class="text-red-400"
                            x-text="formatCurrency(vat_amount)"></span> {{ $report->Currency->CODE ?? 'USD' }}</p>
                    <p> Final Amount: <span class="text-red-400" x-text="formatCurrency(net_total)"></span>
                        {{ $report->Currency->CODE ?? 'USD' }}</p>
                    <input type="hidden" name="sub_total_1" x-model="sub_total_1">
                    <input type="hidden" name="d_i_a" x-model="d_i_a">
                    <input type="hidden" name="sub_total_2" x-model="sub_total_2">
                    <input type="hidden" name="vat_amount" x-model="vat_amount">
                    <input type="hidden" name="net_total" x-model="net_total">

                </div>
            </div>

            <!-- Submit Button -->
            <div class="w-1/2 pt-4 text-right">
                <button type="submit"
                    class="w-1/2 bg-blue-600 text-white py-3 px-4 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Submit
                </button>
            </div>
        </div>
    </form>

    <script>
        function contractForm() {
            return {
                standId: {{$contract->stand_id}},
                priceId: {{$contract->price_id ?? "0"}},
                specialPriceAmount: {{$contract->price_amount ?? 0}},
                spaceTotal: {{$contract->space_amount}},
                spaceDiscount: {{$contract->space_discount}},
                spaceNet: {{$contract->space_net}},
                ifWater: {{$contract->if_water}},
                ifElectricity: {{$contract->if_electricity}},
                waterElectricityAmount: {{$contract->water_electricity_amount}},
                sponsorPackageId: {{$contract->sponsor_package_id ?? 0}},
                sponsorTotal: {{$contract->sponsor_amount}},
                sponsorDiscount: {{$contract->sponsor_discount}},
                sponsorNet: {{$contract->sponsor_net}},
                adsTotal: {{$contract->advertisment_amount}},
                adsDiscount: {{$contract->ads_discount}},
                adsNet: {{$contract->ads_net}},
                sub_total_1: {{$contract->sub_total_1}},
                d_i_a: {{$contract->d_i_a}},
                sub_total_2: {{$contract->sub_total_2}},
                vat_amount: {{$contract->vat_amount}},
                net_total: {{$contract->net_total}},

                init() {
                    //this.calculateTotal();
                },

                formatCurrency(value) {
                    return parseFloat(value || 0).toFixed(2);
                },

                uncheckOthers(event) {
                    if (event.target.checked) {
                        document.querySelectorAll('.single-checkbox').forEach(checkbox => {
                            if (checkbox !== event.target) {
                                checkbox.checked = false;
                            }
                        });
                    }
                },

                toggleSpecialPrice() {
                    if (this.priceId != 0) {
                        this.specialPriceAmount = 0;
                    }
                    this.calculateTotal();
                },

                resetWaterElectricityAmount() {
                    if (!this.ifWater && !this.ifElectricity) {
                        this.waterElectricityAmount = 0;
                    }
                    this.calculateTotal();
                },

                checkMinPrice() {
                    if (this.standId) {
                        const standSelect = document.getElementById('stand_id');
                        const selectedOption = standSelect.options[standSelect.selectedIndex];
                        const space = parseFloat(selectedOption.getAttribute('data-space')) || 0;
                        if (this.priceId && this.priceId !== "0") {
                            const priceRadios = document.querySelector(`input[name="price_id"][value="${this.priceId}"]`);
                            const price = parseFloat(priceRadios.getAttribute('data-price')) || 0;
                            this.spaceTotal = space * price;
                        } else if (this.priceId === "0") {
                            this.spaceTotal = space * parseFloat(this.specialPriceAmount || 0);
                        }
                        this.spaceNet = this.spaceTotal - parseFloat(this.spaceDiscount || 0);
                        console.log(this.spaceDiscount, this.spaceNet);
                        if (this.spaceNet / space <
                            {{ $contract->Event->Currencies()->where('currencies.id', $report->Currency->id)->first()->pivot->min_price }}
                        ) {
                            this.spaceDiscount = '';
                            alert('sdflj kasdfh iasdfu shdf ico');
                        }
                        this.calculateTotal();
                    }
                },

                calculateTotal() {
                    this.spaceTotal = 0;
                    this.spaceNet = 0;
                    this.sponsorTotal = 0;
                    this.sponsorNet = 0;
                    this.adsTotal = 0;
                    this.adsNet = 0;

                    this.sub_total_1= 0;
                    this.d_i_a= 0;
                    this.sub_total_2= 0;
                    this.vat_amount= 0;
                    this.net_total= 0;
                    // Calculate space total
                    if (this.standId) {
                        const space = {{$contract->Stand->space}};

                        if (this.priceId && this.priceId !== "0") {
                            const priceRadios = document.querySelector(`input[name="price_id"][value="${this.priceId}"]`);
                            const price = parseFloat(priceRadios.getAttribute('data-price')) || 0;
                            this.spaceTotal = space * price;
                        } else /*if (this.priceId === "0")*/ {
                            this.spaceTotal = space * parseFloat(this.specialPriceAmount || 0);
                        }
                    }

                    // Calculate net space amount
                    this.spaceNet = this.spaceTotal - parseFloat(this.spaceDiscount || 0);

                    // Add water/electricity amount
                    //this.totalCost += parseFloat(this.waterElectricityAmount || 0);

                    // Add sponsor package amount
                    if (this.sponsorPackageId) {
                        const sponsorSelect = document.getElementById('sponsor_package_id');
                        const selectedOption = sponsorSelect.options[sponsorSelect.selectedIndex];
                        this.sponsorTotal = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                    }

                    // Calculate net sponsor amount
                    this.sponsorNet = this.sponsorTotal - parseFloat(this.sponsorDiscount || 0);

                    // Calculate ads total
                    const adsChecks = document.querySelectorAll('input[name="ads_check[]"]:checked');
                    adsChecks.forEach(element => {
                        const price = parseFloat(element.getAttribute('data-price')) || 0;
                        this.adsTotal += price;
                    });

                    // Calculate net Advertisement amount
                    this.adsNet = this.adsTotal - parseFloat(this.adsDiscount || 0);

                    // Calculate final total
                   this.sub_total_1 = this.spaceTotal + this.sponsorTotal + this.adsTotal + parseFloat(this.waterElectricityAmount || 0);
                   this.d_i_a = parseFloat(this.spaceDiscount || 0) + parseFloat(this.sponsorDiscount || 0) + parseFloat(this.adsDiscount || 0);
                   this.sub_total_2 = this.sub_total_1 - this.d_i_a;
                   this.vat_amount =this.sub_total_2 * {{$contract->Event->vat_rate}} / 100;
                   this.net_total = this.sub_total_2 + this.vat_amount;
                }
            }
        }
    </script>
@endsection
