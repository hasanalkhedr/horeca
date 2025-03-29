@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 text-center">Contract Form</h1>
    <p class="text-center text-gray-600">Fill out the contract form with the required details.</p>

    <form action="{{ route('contracts.store') }}" method="POST"
        class="w-full mx-auto bg-white shadow-lg rounded-lg px-2 py-1 space-y-1">
        @csrf
        <h2 class="text-xl font-semibold text-gray-800">Contract Basic Information</h2>
        <div class="flex flex-wrap -mx-3 mb-2">
            <div class="w-1/3 px-3">
                <x-input-label>
                    Contract #
                </x-input-label>
                <x-text-input name="contract_no" value="new contract" />
            </div>
            <div class="w-1/3 px-3">
                <x-input-label>
                    Contract Date:<span class="text-red-500">*</span>
                </x-input-label>
                <x-text-input type="date" name="contract_date" value="{{ (new \DateTime())->format('Y-m-d') }}">
                </x-text-input>
            </div>
            <div class="w-1/3 px-3">
                <x-input-label>
                    Contract Status
                </x-input-label>
                <x-text-input name="contract_status" disabled>
                    <option value="draft">Draft</option>
                </x-text-input>
            </div>
        </div>

        @if (in_array('company-details-component', $report->components))
            <x-form-divider>Client Info:</x-form-divider>
            <livewire:client-select model="App\Models\Company" dependentModel="App\Models\Client" foreignKey="company_id"
                placeholder="Choose a Company" parentLabel="Company" childLabel="Exhabition Co-ordinator"
                child2Label="Daily Contact Person" />
            <x-form-divider>Categories:</x-form-divider>
            <div class="space-y-2 grid grid-cols-3 gap-2">
                @foreach ($categories as $category)
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="categories[]" value="{{ $category }}"
                            class="form-checkbox h-5 w-5 text-blue-600 rounded single-checkbox">
                        <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>
            <script>
                document.querySelectorAll('.single-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            // Uncheck all other checkboxes
                            document.querySelectorAll('.single-checkbox').forEach(other => {
                                if (other !== this) other.checked = false;
                            });
                        }
                    });
                });
            </script>
        @endif

        @if (in_array('price-section-component', $report->components))
            <x-form-divider>Stand Info:</x-form-divider>
            <div class="flex flex-wrap -mx-3 mb-2 w-full">
                <div class="w-full px-3">
                    <x-input-label for="stand_id">Stand:</x-input-label>
                    <x-select-input name="stand_id" id="stand_id" required onchange="calculateTotal()">
                        <option value="">-- Select Value --</option>
                        @foreach ($stands as $stand)
                            <option value="{{ $stand->id }}" data-space="{{ $stand->space }}">
                                {{ $stand->no }}|{{ $stand->space }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div class="w-full px-3">
                    <x-input-label for="price_id">Price:</x-input-label>
                    @foreach ($prices as $price)
                        <div class="block">
                            <input type="radio" name="price_id" value="{{ $price->id }}"
                                data-price="{{ $price->amount }}" onclick="calculateTotal()" /> {{ $price->name }} |
                            {{ $price->Currency->CODE }} | {{ $price->amount }}
                        </div>
                    @endforeach
                    @if ($report->special_price)
                        <div class="block">
                            <input type="radio" name="price_id" value="0" id="special_price_radio"
                                onclick="toggleAmountInput(this)" />Special pavilion specify
                            <input
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                name="special_price_amount" id="special_price_amount" type="number" step="0.01" disabled
                                oninput="calculateTotal()" />
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if (in_array('water-section', $report->components))
            <x-form-divider>Extra Water/Electricity:</x-form-divider>
            <label class="inline-flex items-center">
                <input type="checkbox" name="if_water" value="1"
                    class="text-indigo-600 focus:ring-indigo-500 border-gray-300" onchange="calculateTotal()">
                <span class="ml-2 text-sm text-gray-700"> Water point needed (if available)</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="if_electricity" class="text-indigo-600 focus:ring-indigo-500 border-gray-300"
                    value="1" id="if_electricity" onclick="toogleElectricityAmount(this)"
                    onchange="calculateTotal()" /> Extra electricity
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="electricity_text" id="electricity_text" type="text" placeholder="WATT Needed" disabled />
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="water_electricity_amount" placeholder="Water & electricity Amount" type="number" step="0.01"
                    oninput="calculateTotal()" />
            </label>
        @endif

        @if (in_array('new-product-section', $report->components))
            <x-form-divider>New Product to launch</x-form-divider>
            <div class="w-full px-3">
                <x-input-label for="new_product">New Product to launch</x-input-label>
                <x-text-input name="new_product" id="new_product" />
            </div>
        @endif

        @if (in_array('sponsor-section', $report->components))
            <x-form-divider>Sponsor Package:</x-form-divider>
            <div class="flex flex-wrap -mx-3 mb-2 w-full">
                <div class="w-full px-3">
                    <x-input-label for="sponsor_package_id">Choose Sponsor Package:</x-input-label>
                    <x-select-input name="sponsor_package_id" id="sponsor_package_id" onchange="calculateTotal()">
                        <option value="">-- Select Value --</option>
                        @foreach ($sponsor_packages as $package)
                            <option value="{{ $package->id }}" data-price="{{ $package->currencies->where('id',$report->Currency->id)->first() ?
                                    $package->currencies->where('id',$report->Currency->id)->first()->pivot->total_price : 0 }}">
                                {{ $package->title }}|{{ $package->currencies->where('id',$report->Currency->id)->first() ?
                                    $package->currencies->where('id',$report->Currency->id)->first()->pivot->total_price : 0}}
                                {{ $report->Currency->CODE }}</option>
                        @endforeach
                    </x-select-input>
                </div>
            </div>
        @endif

        @if (in_array('advertisement-section', $report->components))
            <x-form-divider>Advertisement in Hospitality News</x-form-divider>
            <div class="flex">
                <div class="w-3/4">
                    @foreach ($event->AdsPackages as $package)
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
                                        <input onchange="calculateTotal()" name="ads_check[]"
                                            value="{{ $package->id }}_{{ $option->id }}"
                                            data-price="{{ $option->Currencies->where('id', $report->Currency->id)->first() ?
                                                $option->Currencies->where('id', $report->Currency->id)->first()->pivot->price : 0 }}"
                                            type="checkbox" class="mr-1">{{ $option->title }}
                                    </td>
                                    <td class="w-1/6">
                                        {{ $option->Currencies->where('id', $report->Currency->id)->first() ?
                                            $option->Currencies->where('id', $report->Currency->id)->first()->pivot->price : 0}}
                                        {{ $report->Currency->CODE }}</td>
                                    @if (!$loop->last)
                                        <td class="w-2/6">
                                            <input type="checkbox" onchange="calculateTotal()" name="ads_check[]"
                                                value="{{ $package->id }}_{{ $package->AdsOptions[$loop->index + 1]->id }}"
                                                data-price="{{ $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $report->Currency->id)->first() ?
                                                    $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $report->Currency->id)->first()->pivot->price : 0 }}"
                                                class="mr-1">{{ $package->AdsOptions[$loop->index + 1]->title }}
                                        </td>
                                        <td class="w-1/6">
                                            {{ $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $report->Currency->id)->first() ?
                                                $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $report->Currency->id)->first()->pivot->price : 0 }}
                                            {{ $report->Currency->CODE }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </table>
                    @endforeach
                </div>
                <div class="w-1/4 total text-center p-2">
                    <div class="text-lg font-bold  bg-gray-200">
                        Advertisement Total: <span class="" id="ads_total">0</span> {{ $report->Currency->CODE ?? 'USD' }}
                        <input type="hidden" name="advertisment_amount" id="advertisment_amount" value="0">
                    </div>
                </div>
            </div>
        @endif

        @if (in_array('notes-section', $report->components))
            <x-form-divider>Contract Notes:</x-form-divider>
            <div class="flex flex-wrap -mx-3 mb-2 w-full">
                <div class="w-full px-3">
                    <x-input-label for="notes1">Notes:</x-input-label>
                    <x-text-input name="notes1" id="notes1" />
                </div>
                <div class="w-full px-3">
                    <x-input-label for="notes2">Notes:</x-input-label>
                    <x-text-input name="notes2" id="notes2" />
                </div>
            </div>
        @endif

        <h2 class="text-xl font-semibold text-gray-800">Who is the seller of this contract?</h2>
        <div class="flex flex-wrap -mx-3 mb-2">
            <div class="w-full px-3">
                <x-select-input name="seller" id="seller">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(auth()->id() == $user->id)>
                            {{ $user->name }}|{{ $user->getRoleNames() }}
                        </option>
                    @endforeach
                </x-select-input>
            </div>
        </div>
        <input type="hidden" name="report_id" value="{{ $report->id }}">
        <input type="hidden" name="event_id" value="{{ $event->id }}">
        <div class="flex">
            <!-- Total Cost Display -->
            <div class="w-1/2 total pt-4 py-3 text-center">
                <div class="text-2xl font-bold  bg-gray-200">
                    Total: <span class="" id="totalCost">0</span> {{ $report->Currency->CODE ?? 'USD' }}
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
        function calculateTotal() {
            let total = 0;
            let space = 0;

            // Stand Price
            const standSelect = document.getElementById('stand_id');
            if (standSelect.value) {
                const selectedOption = standSelect.options[standSelect.selectedIndex];
                space = parseFloat(selectedOption.getAttribute('data-space')) || 0;
            }

            // Price Selection
            const priceRadios = document.querySelectorAll('input[name="price_id"]:checked');
            if (priceRadios.length > 0) {
                const selectedPrice = priceRadios[0];
                if (selectedPrice.value !== "0") {
                    total += space * parseFloat(selectedPrice.getAttribute('data-price')) || 0;
                } else {
                    const specialPriceInput = document.getElementById('special_price_amount');
                    if (specialPriceInput && !specialPriceInput.disabled) {
                        total += space * parseFloat(specialPriceInput.value) || 0;
                    }
                }
            }

            // Water/Electricity
            const waterElectricityInput = document.querySelector('input[name="water_electricity_amount"]');
            if (waterElectricityInput) {
                total += parseFloat(waterElectricityInput.value) || 0;
            }

            // Sponsor Package
            const sponsorPackageSelect = document.getElementById('sponsor_package_id');
            if (sponsorPackageSelect.value) {
                const selectedOption = sponsorPackageSelect.options[sponsorPackageSelect.selectedIndex];
                total += parseFloat(selectedOption.getAttribute('data-price')) || 0;
            }

            let adsTotal = 0;
            const adsChecks = document.querySelectorAll('input[name="ads_check[]"]:checked');
            if (adsChecks.length > 0) {
                adsChecks.forEach(element => {
                    adsTotal += parseFloat(element.getAttribute('data-price')) || 0;
                    total += parseFloat(element.getAttribute('data-price')) || 0;
                });
            }
            document.getElementById('ads_total').textContent = adsTotal.toFixed(2);
            document.getElementById('advertisment_amount').value = adsTotal;

            // Update Total Display
            document.getElementById('totalCost').textContent = total.toFixed(2);
        }

        function toggleAmountInput(radioElement) {
            const amountInput = document.getElementById('special_price_amount');
            const radio_sp = document.getElementById('special_price_radio');
            amountInput.disabled = !radio_sp.checked;
            amountInput.focus();
            calculateTotal();
        }

        function toogleElectricityAmount(checkboxElement) {
            const amountInput = document.getElementById('electricity_text');
            const radio_sp = document.getElementById('if_electricity');
            amountInput.disabled = !radio_sp.checked;
            amountInput.focus();
            calculateTotal();
        }

        function resetWaterElectricityAmount() {
            const ifElectricity = document.getElementById('if_electricity');
            const ifWater = document.querySelector('input[name="if_water"]');
            const waterElectricityInput = document.querySelector('input[name="water_electricity_amount"]');

            // If both checkboxes are unchecked, reset the amount to 0
            if (!ifElectricity.checked && !ifWater.checked) {
                waterElectricityInput.value = 0;
            }

            // Recalculate the total
            calculateTotal();
        }

        // Attach event listeners to checkboxes
        document.getElementById('if_electricity').addEventListener('change', resetWaterElectricityAmount);
        document.querySelector('input[name="if_water"]').addEventListener('change', resetWaterElectricityAmount);

        // Initial calculation on page load
        document.addEventListener('DOMContentLoaded', calculateTotal);
    </script>
@endsection
