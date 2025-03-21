{{-- @extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 text-center">Contract Form</h1>
    <p class="text-center text-gray-600">Fill out the contract form with the required details.</p>

    <form action="{{ route('contracts.update', $contract->id) }}" method="POST"
        class="w-full mx-auto bg-white shadow-lg rounded-lg p-6 space-y-8">
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
            {{-- <div class="w-1/3 px-3">
                <x-input-label>
                    Contract Date:<span class="text-red-500">*</span>
                </x-input-label>
                <x-text-input type="date" name="contract_date" value="{{$contract->contract_date->format('Y-m-d') }}">
                </x-text-input>
            </div> --}}
{{-- <div class="w-1/3 px-3">
                <x-input-label>
                    Contract Status
                </x-input-label>
                <x-text-input name="contract_status" disabled>
                    <option value="draft">{{ $contract->status }}</option>
                </x-text-input>
            </div>
        </div>
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
                            class="form-checkbox h-5 w-5 text-blue-600 rounded single-checkbox"
                            @checked($contract->category_id == $category->id)>
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
                    <x-select-input name="stand_id" id="stand_id" required>
                        <option value="">-- Select Value --</option>
                        @foreach ($stands as $stand)
                            <option value="{{ $stand->id }}" @selected($stand->id == $contract->stand_id)>
                                {{ $stand->no }}|{{ $stand->space }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div class="w-full px-3">
                    <x-input-label for="price_id">Price:</x-input-label>
                    @foreach ($prices as $price)
                        <div class="block">
                            <input type="radio" name="price_id" value="{{ $price->id }}"
                                onclick="toggleAmountInput(this)" @checked($price->id == $contract->price_id) /> {{ $price->name }} |
                            {{ $price->Currency->CODE }} | {{ $price->amount }}
                        </div>
                    @endforeach
                    <div class="block">

                        <input type="radio" name="price_id" value="0" id="special_price_radio"
                            @checked($contract->price_id == null) onclick="toggleAmountInput(this)" />Special pavilion specify
                        <input
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            name="special_price_amount" id="special_price_amount" type="number" step="0.01"
                            @disabled($contract->price_id != null)
                            value="{{ $contract->price_id == null ? $contract->price_amount : 0 }}" />
                        <script>
                            function toggleAmountInput(radioElement) {
                                // Disable all amount inputs
                                const amountInput = document.getElementById('special_price_amount');
                                const radio_sp = document.getElementById('special_price_radio')
                                amountInput.disabled = !radio_sp.checked;
                                amountInput.focus();
                            }
                        </script>
                    </div>
                    <div class="">
                        <x-input-label class="inline" for="special_design_text">Special Conditions:</x-input-label>
                        <input
                            class="w-1/2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            type="text" name="special_design_text" />
                        <x-input-label class="inline" for="special_design_price">Price per SQM:</x-input-label>
                        <input
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            name="special_design_price" id="special_design_price" type="number" step="0.01" />
                    </div>
                </div>
            </div>
        @endif
        @if (in_array('water-section', $report->components))
            <x-form-divider>Extra Water/Electricity:</x-form-divider>
            <label class="inline-flex items-center">
                <input type="checkbox" name="if_water" value="1" @checked($contract->if_water)
                    class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <span class="ml-2 text-sm text-gray-700"> Water point needed (if available)</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="if_electricity" class="text-indigo-600 focus:ring-indigo-500 border-gray-300"
                    value="1" @checked($contract->if_electricity) id="if_electricity"
                    onclick="toogleElectricityAmount(this)" /> Extra electricity
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="electricity_text" id="electricity_text" type="text" value="{{ $contract->electricity_text }}"
                    placeholder="WATT Needed" disabled />
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="water_electricity_amount" placeholder="Water & electricity Amount" type="number" step="0.01"
                    value="{{ $contract->water_electricity_amount }}" />
                <script>
                    function toogleElectricityAmount(checkboxElement) {
                        // Disable all amount inputs
                        const amountInput = document.getElementById('electricity_text');
                        const radio_sp = document.getElementById('if_electricity')
                        amountInput.disabled = !radio_sp.checked;
                        amountInput.focus();
                    }
                </script>

            </label>
        @endif
        @if (in_array('new-product-section', $report->components))
            <x-form-divider>New Product to launch</x-form-divider>
            <div class="w-full px-3">
                <x-input-label for="new_product">New Product to launch</x-input-label>
                <x-text-input name="new_product" id="new_product" value="{{ $contract->new_product }}" />
            </div>
        @endif
        @if (in_array('sponsor-section', $report->components))
            <x-form-divider>Sponsor Package:</x-form-divider>
            <div class="flex flex-wrap -mx-3 mb-2 w-full">
                <div class="w-full px-3">
                    <x-input-label for="sponsor_package_id">Choose Sponsor Package:</x-input-label>
                    <x-select-input name="sponsor_package_id" id="sponsor_package_id">
                        <option value="">-- Select Value --</option>
                        @foreach ($sponsor_packages as $package)
                            <option value="{{ $package->id }}" @selected($package->id == $contract->sponsor_package_id)>
                                {{ $package->title }}|{{ $package->total_price }}
                                {{ $package->Currency->CODE }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div class="w-full px-3">
                    <x-input-label for="specify_text">Specify:</x-input-label>
                    <x-text-input name="specify_text" id="specify_text" value="{{ $contract->specify_text }}" />
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



        <div class="pt-4">
            <button type="submit"
                class="w-1/2 bg-blue-600 text-white py-3 px-4 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Submit
            </button>
        </div>
    </form>
@endsection --}}

@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 text-center">Contract Form</h1>
    <p class="text-center text-gray-600">Fill out the contract form with the required details.</p>

    <form action="{{ route('contracts.update', $contract->id) }}" method="POST"
        class="w-full mx-auto bg-white shadow-lg rounded-lg p-6 space-y-8">
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
                            class="form-checkbox h-5 w-5 text-blue-600 rounded single-checkbox"
                            @checked($contract->category_id == $category->id)>
                        <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>
        @endif

        <!-- Stand Info Section -->
        @if (in_array('price-section-component', $report->components))
            <x-form-divider>Stand Info:</x-form-divider>
            <div class="flex flex-wrap -mx-3 mb-2 w-full">
                <div class="w-full px-3">
                    <x-input-label for="stand_id">Stand:</x-input-label>
                    <x-select-input name="stand_id" id="stand_id" required onchange="calculateTotal()">
                        <option value="">-- Select Value --</option>
                        @foreach ($stands as $stand)
                            <option value="{{ $stand->id }}" data-space="{{ $stand->space }}"
                                @selected($stand->id == $contract->stand_id)>
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
                                data-price="{{ $price->amount }}" onclick="calculateTotal()"
                                @checked($price->id == $contract->price_id) /> {{ $price->name }} |
                            {{ $price->Currency->CODE }} | {{ $price->amount }}
                        </div>
                    @endforeach
                    <div class="block">
                        <input type="radio" name="price_id" value="0" id="special_price_radio"
                            @checked($contract->price_id == null) onclick="toggleAmountInput(this)" />Special pavilion specify
                        <input
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            name="special_price_amount" id="special_price_amount" type="number" step="0.01"
                            value="{{ $contract->price_id == null ? $contract->price_amount : 0 }}"
                            oninput="calculateTotal()" @disabled($contract->price_id != null) />
                    </div>
                </div>
            </div>
        @endif

        <!-- Extra Water/Electricity Section -->
        @if (in_array('water-section', $report->components))
            <x-form-divider>Extra Water/Electricity:</x-form-divider>
            <label class="inline-flex items-center">
                <input type="checkbox" name="if_water" value="1" @checked($contract->if_water)
                    class="text-indigo-600 focus:ring-indigo-500 border-gray-300" onchange="calculateTotal()">
                <span class="ml-2 text-sm text-gray-700"> Water point needed (if available)</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="if_electricity" class="text-indigo-600 focus:ring-indigo-500 border-gray-300"
                    value="1" @checked($contract->if_electricity) id="if_electricity" onclick="toogleElectricityAmount(this)"
                    onchange="calculateTotal()" /> Extra electricity
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="electricity_text" id="electricity_text" type="text" value="{{ $contract->electricity_text }}"
                    placeholder="WATT Needed" disabled />
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="water_electricity_amount" placeholder="Water & electricity Amount" type="number" step="0.01"
                    value="{{ $contract->water_electricity_amount }}" oninput="calculateTotal()" />
            </label>
        @endif

        @if (in_array('new-product-section', $report->components))
            <x-form-divider>New Product to launch</x-form-divider>
            <div class="w-full px-3">
                <x-input-label for="new_product">New Product to launch</x-input-label>
                <x-text-input name="new_product" id="new_product" value="{{$contract->new_product}}"/>
            </div>
        @endif

        <!-- Sponsor Package Section -->
        @if (in_array('sponsor-section', $report->components))
            <x-form-divider>Sponsor Package:</x-form-divider>
            <div class="flex flex-wrap -mx-3 mb-2 w-full">
                <div class="w-full px-3">
                    <x-input-label for="sponsor_package_id">Choose Sponsor Package:</x-input-label>
                    <x-select-input name="sponsor_package_id" id="sponsor_package_id" onchange="calculateTotal()">
                        <option value="">-- Select Value --</option>
                        @foreach ($sponsor_packages as $package)
                            <option value="{{ $package->id }}" data-price="{{ $package->total_price }}"
                                @selected($package->id == $contract->sponsor_package_id)>
                                {{ $package->title }}|{{ $package->total_price }} {{ $package->Currency->CODE }}
                            </option>
                        @endforeach
                    </x-select-input>
                </div>
            </div>
        @endif

        @if (in_array('notes-section', $report->components))
            <x-form-divider>Contract Notes:</x-form-divider>
            <div class="flex flex-wrap -mx-3 mb-2 w-full">
                <div class="w-full px-3">
                    <x-input-label for="notes1">Notes:</x-input-label>
                    <x-text-input name="notes1" id="notes1" value="{{$contract->notes1}}"/>
                </div>
                <div class="w-full px-3">
                    <x-input-label for="notes2">Notes:</x-input-label>
                    <x-text-input name="notes2" id="notes2" value="{{$contract->notes2}}"/>
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
            <div class="w-1/2 total pt-4 py-3 text-center">
                <div class="text-2xl font-bold  bg-gray-200">
                    Total: <span id="totalCost">{{ $contract->total_cost ?? 0 }}</span> {{ $defaultCurrency ?? 'USD' }}
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
