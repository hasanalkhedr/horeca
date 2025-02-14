@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 text-center">Contract Form</h1>
    <p class="text-center text-gray-600">Fill out the contract form with the required details.</p>

    <form action="{{ route('contracts.store') }}" method="POST"
        class="w-full mx-auto bg-white shadow-lg rounded-lg p-6 space-y-8">
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
            <div class="mt-2 flex flex-wrap gap-4">
                @foreach ($categories as $category)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="categories[]" value="{{ $category }}"
                            class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        <span class="ml-2 text-sm text-gray-700">{{ $category }}</span>
                    </label>
                @endforeach
            </div>
        @endif
        @if (in_array('price-section-component', $report->components))
            <x-form-divider>Stand Info:</x-form-divider>
            <div class="flex flex-wrap -mx-3 mb-2 w-full">
                <div class="w-full px-3">
                    <x-input-label for="stand_id">Stand:</x-input-label>
                    <x-select-input name="stand_id" id="stand_id" required>
                        <option value="">-- Select Value --</option>
                        @foreach ($stands as $stand)
                            <option value="{{ $stand->id }}">{{ $stand->no }}|{{ $stand->space }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div class="w-full px-3">
                    <x-input-label for="price_id">Price:</x-input-label>
                    @foreach ($prices as $price)
                        <div class="block">
                            <input type="radio" name="price_id" value="{{ $price->id }}"
                                onclick="toggleAmountInput(this)" /> {{ $price->name }} |
                            {{ $price->Currency->CODE }} | {{ $price->amount }}
                        </div>
                    @endforeach
                    <div class="block">
                        <input type="radio" name="price_id" value="0" id="special_price_radio"
                            onclick="toggleAmountInput(this)" />Special pavilion specify
                        <input
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            name="special_price_amount" id="special_price_amount" type="number" step="0.01" disabled />
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
                <input type="checkbox" name="if_water" value="true"
                    class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <span class="ml-2 text-sm text-gray-700"> Water point needed (if available)</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="if_electricity" class="text-indigo-600 focus:ring-indigo-500 border-gray-300"
                    value="true" id="if_electricity" onclick="toogleElectricityAmount(this)" /> Extra electricity
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="electricity_text" id="electricity_text" type="text" placeholder="WATT Needed" disabled />
                <input
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    name="water_electricity_amount" placeholder="Water & electricity Amount" type="number"
                    step="0.01" />
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
            <h1>__TO_BE_CONTINUED__</h1>
        @endif
        {{-- <input type="hidden" name="path" value="{{ $contract_type->path }}"> --}}
        <input type="hidden" name="report_id" value="{{ $report->id }}">
        <input type="hidden" name="event_id" value="{{ $event->id }}">

        <div class="pt-4">
            <button type="submit"
                class="w-1/2 bg-blue-600 text-white py-3 px-4 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Submit
            </button>
        </div>
    </form>
@endsection
