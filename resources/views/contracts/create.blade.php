@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 text-center">Contract Form</h1>
    <p class="text-center text-gray-600">Fill out the contract form with the required details.</p>

    <form x-data="contract()" action="{{ route('contracts.store') }}" method="POST"
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
            <div class="flex flex-wrap -mx-3 mb-2">
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
                </div>
            </div>
        @endif
        {{-- <input type="hidden" name="path" value="{{ $contract_type->path }}"> --}}
        <input type="hidden" name="report_id" value="{{ $report->id }}">
        <input type="hidden" name="event_id" value="{{ $event->id }}">

        <div class="pt-4">
            <button type="submit"
                class="w-1/2 bg-blue-600 text-white py-3 px-4 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Submit
            </button>
            {{-- <button type="button" @click="openModal('{{$contract_type->path}}')"
                class="w-1/2 bg-blue-600 text-white py-3 px-4 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Preview Contract
            </button> --}}
        </div>


        <!-- Modal -->
        <div x-show="isOpen" @click.away="closeModal()" @keydown.escape.window="closeModal()" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-4xl">
                <div class="flex justify-between items-center mb-4">
                    <button @click="closeModal()"
                        class="text-gray-600 text-3xl hover:text-gray-800 transition-colors duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="pdfPreviewContainer" x-show="previewUrl">
                    <embed id="pdfPreview" :src="previewUrl" type="application/pdf" width="100%" height="500px">
                </div>
            </div>
        </div>
    </form>
    <script>
        function contract() {
            return {
                isOpen: false,

                previewUrl: '',

                getBaseUrl() {
                    return `${window.location.protocol}//${window.location.host}`;
                },
                convertPathToUrl(filePath) {
                    const baseUrl = this.getBaseUrl();
                    return `${baseUrl}/storage/${filePath.replace(/\\/g, '/')}`;
                },

                openModal(path) {
                    this.isOpen = true;
                    this.previewUrl = this.convertPathToUrl(path);
                    console.log(this.previewUrl);
                },
                closeModal() {
                    this.isOpen = false;
                    this.previewUrl = null;
                },
            };
        }
    </script>
@endsection
