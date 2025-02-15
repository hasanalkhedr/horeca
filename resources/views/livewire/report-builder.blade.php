<div class="flex min-h-screen bg-gray-100">
    <div class="w-1/3 p-6 bg-white border-r border-gray-200 fixed h-full">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Contract Template Builder</h1>
        <!-- Report Name Input -->
        <div class="mb-4">
            <label class="text-gray-700 font-semibold">Report Name <span class="text-red-500">*</span></label>
            <input type="text" wire:model="reportName"
                class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none" required
                placeholder="Enter report name">
            @error('reportName')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label class="text-gray-700 font-semibold">Choose Event <span class="text-red-500">*</span></label>
            <select class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none"
                required wire:model="event_id" name="event_id">
                <option value="">-- Select Event --</option>
                @foreach ($events as $evt)
                    <option value="{{ $evt->id }}">{{ $evt->name }}</option>
                @endforeach
            </select>
            @error('event_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <!-- Component Selection -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Select Components</h2>
            <div class="space-y-2 grid grid-cols-2 gap-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="header-component"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Header</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="company-details-component"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Company Details</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="price-section-component"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Prices Section</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="water-section"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Water/Electricity Section</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="new-product-section"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">New Product(s) Section</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="sponsor-section"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Sponsorship Section</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="advertisement-section"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Advertisement Section</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="payment-section"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Payment and Totals Section</span>
                </label>
                <input type="text" name="bankAccount" wire:model="bankAccount" id="">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="notes-section"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Notes/contact Person Section</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="signature-section"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Signature Section</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" checked disabled class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Terms Page <span class="text-[10px] text-gray-400">(Added by
                            default)</span></span>
                </label>
                {{-- <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="selectedComponents" value="footer"
                        class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="text-gray-700">Footer</span>
                </label> --}}

            </div>
        </div>
        <!-- Flash Message -->
        @if ($message)
            <div
                class="p-4 @if ($messageType === 'success') bg-green-100 border-green-400 text-green-700 @else bg-red-100 border-red-400 text-red-700 @endif rounded">
                {{ $message }}
                @if ($messageType === 'success')
                    <div
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none">
                        <a target="_blank" href="{{ route('reports.show', ['id' => $report->id]) }}">Preview
                            Report</a>
                    </div>
                @endif
            </div>
        @endif
        {{-- <!-- Save Button -->
        <div class="mt-6">
            <button wire:click="saveReport"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Save Report Template
            </button>
        </div> --}}
        <!-- Save Report Button -->
        <div class="mt-6">
            <button wire:click="saveReport"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Save Report Template
            </button>
        </div>

        <!-- Additional Buttons: Go Back & Go to Route -->
        <div class="mt-4 flex gap-2">
            <!-- Go Back Button -->
            <button onclick="window.history.back()"
                class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none">
                Go Back
            </button>

            <!-- Go to Route Button -->
            <button onclick="window.location.href='{{ route('reports.index') }}'"
                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none">
                View Templates
            </button>
        </div>


    </div>

    <!-- Main Content Area -->
    <div class="flex-1 ml-64 p-6">
        <!-- Dynamically Render Selected Components -->
        <div class="space-y-1 bg-gray-100">
            <div
                class="page mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none p-[10mm] my-8 print:my-0">
                <div x-data="{
                    initSortable() {
                        let el = this.$refs.sortableList;
                        Sortable.create(el, {
                            animation: 150,
                            ghostClass: 'bg-gray-200',
                            onEnd: event => {
                                let newOrder = Array.from(el.children).map(el => el.dataset.id);
                                @this.call('updateSort', newOrder);
                            }
                        });
                    }
                }" x-init="initSortable()" class="space-y-2">

                    <div x-ref="sortableList" class="space-y-2">
                        @foreach ($selectedComponents as $index => $component)
                            <div data-id="{{ $component }}"
                                class="bg-white p-2 border rounded shadow-sm cursor-move flex justify-between items-center">
                                {{-- <span class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $component)) }}</span> --}}
                                @if ($component == 'payment-section')
                                    @livewire($component, [$bankAccount], key($component . '-' . $index))
                                @else
                                    @livewire($component, [], key($component . '-' . $index))
                                @endif
                                <button wire:click="removeComponent('{{ $component }}')"
                                    class="text-red-500 hover:text-red-700">
                                    &times;
                                </button>
                            </div>
                        @endforeach
                    </div>

                </div>
                {{-- @if (in_array('header', $selectedComponents))
                    @livewire('header-component')
                @endif
                @if (in_array('company_details', $selectedComponents))
                    @livewire('company-details-component')
                @endif
                @if (in_array('price_section', $selectedComponents))
                    @livewire('price-section-component')
                @endif
                @if (in_array('water_section', $selectedComponents))
                    @livewire('water-section')
                @endif
                @if (in_array('new_product', $selectedComponents))
                    @livewire('new-product-section')
                @endif
                @if (in_array('sponsor_section', $selectedComponents))
                    @livewire('sponsor-section')
                @endif
                @if (in_array('advertisement_section', $selectedComponents))
                    @livewire('advertisement-section')
                @endif
                @if (in_array('payment_section', $selectedComponents))
                    @livewire('payment-section')
                @endif
                @if (in_array('notes_section', $selectedComponents))
                    @livewire('notes-section')
                @endif
                @if (in_array('signature_section', $selectedComponents))
                    @livewire('signature-section')
                @endif --}}

            </div>
            {{-- <div
                class="page mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[5mm] py-[1mm] my-8 print:my-0 relative">
                @if (in_array('footer', $selectedComponents))

                        @livewire('footer-component')

                @endif
            </div> --}}
        </div>
    </div>
</div>
