<div class="flex min-h-screen bg-gray-100">
    <div class="w-1/3 p-6 bg-white border-r border-gray-200 h-full">
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

        <!-- Choose Event Dropdown -->
        <div class="mb-4">
            <label class="text-gray-700 font-semibold">Choose Event <span class="text-red-500">*</span></label>
            <select class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none"
                required wire:model.live="event_id" name="event_id">
                <option value="">-- Select Event --</option>
                @foreach ($events as $evt)
                    <option value="{{ $evt->id }}">{{ $evt->name }}</option>
                @endforeach
            </select>
            @error('event_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label class="text-gray-700 font-semibold">Choose Currency <span class="text-red-500">*</span></label>
            <select class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none"
                required wire:model="currency_id" name="currency_id">
                <option value="">-- Select Currency --</option>
                @if ($event)
                    @foreach ($event->Currencies as $currency)
                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                    @endforeach
                @endif
            </select>
            @error('currency_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Component Selection -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Select Components</h2>
            <span class="text-xs text-gray-600"><strong>Note: </strong>you can't update section properties after add it
                to the template, to acheive this you should remove the section first, then readd it with updated
                properties, and reorder the sections.</span>
            <div class="space-y-2">
                @foreach ([
        'header-component' => 'Header',
        'company-details-component' => 'Company Details',
        'price-section-component' => 'Prices Section',
        'water-section' => 'Water/Electricity Section',
        'new-product-section' => 'New Product(s) Section',
        'sponsor-section' => 'Sponsorship Section',
        'advertisement-section' => 'Advertisement Section',
        'payment-section' => 'Payment and Totals Section',
        'notes-section' => 'Notes/Contact Person Section',
        'signature-section' => 'Signature Section',
    ] as $component => $label)
                    <div x-data="{ open: false }" class="border rounded-md">
                        <!-- Collapsible Header -->
                        <button @click="open = !open"
                            class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 flex justify-between items-center">
                            <span class="font-medium text-gray-700">{{ $label }}</span>
                            <svg :class="{ 'transform rotate-180': open }"
                                class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Collapsible Content -->
                        <div x-show="open" x-collapse class="p-3 space-y-2">
                            <!-- Example Properties -->
                            <div class="text-sm text-gray-600">
                                @if ($component === 'payment-section')
                                    <!-- Payment Section Properties -->
                                    <div class="space-y-2">
                                        <label class="block text-gray-700 font-semibold">Payment Method</label>
                                        <input type="text" wire:model.live="paymentMethod"
                                            class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none"
                                            placeholder="Enter bank account details">
                                        <label class="block text-gray-700 font-semibold">Bank Account Number</label>
                                        <input type="text" wire:model.live="bankAccount"
                                            class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none"
                                            placeholder="Enter bank account details">
                                        <label class="block text-gray-700 font-semibold">Bank Name and Address</label>
                                        <input type="text" wire:model.live="bankNameAddress"
                                            class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none"
                                            placeholder="Enter bank account details">
                                        <label class="block text-gray-700 font-semibold">Swift Code</label>
                                        <input type="text" wire:model.live="swiftCode"
                                            class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none"
                                            placeholder="Enter bank account details">
                                        <label class="block text-gray-700 font-semibold">IBAN</label>
                                        <input type="text" wire:model.live="iban"
                                            class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none"
                                            placeholder="Enter bank account details">
                                    </div>
                                @elseif($component === 'header-component')
                                    <!-- Header Component Properties -->
                                    <div class="space-y-2">
                                        <input type="checkbox" wire:model.live="with_logo"
                                            class="w-3 px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none">
                                        <label class="text-gray-700 font-semibold">Header with LOGO image</label>
                                        <label class="block text-gray-700 font-semibold">Logo path:</label>
                                        <input type="file" wire:model="logo_image" accept="image/*"
                                            class="w-full px-3 py-2 border rounded-md focus:ring focus:ring-blue-200 focus:outline-none"
                                            placeholder="choose logo">
                                    </div>
                                @endif
                            </div>

                            <!-- Add Component Button -->
                            <button wire:click="addComponent('{{ $component }}')"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                Add&Update {{ $label }}
                            </button>
                        </div>
                    </div>
                @endforeach
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
                        <a target="_blank" href="{{ route('reports.show', ['id' => $report->id]) }}">Preview Report</a>
                    </div>
                @endif
            </div>
        @endif

        <!-- Save Report Button -->
        <div class="mt-6">
            <button wire:click="saveReport"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Save Report Template
            </button>
        </div>

        <!-- Additional Buttons: Go Back & Go to Route -->
        <div class="mt-4 flex gap-2">
            <button onclick="window.history.back()"
                class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none">
                Go Back
            </button>
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
                                @if ($component == 'payment-section')
                                    @livewire($component, [null, $paymentMethod, $bankAccount, $bankNameAddress, $swiftCode, $iban, $currency], key($component . '-' . $index))
                                @elseif($component == 'header-component')
                                    @livewire($component, [null, $with_logo, $logo_path], key($component . '-' . $index))
                                @elseif($component == 'price-section-component')
                                    @livewire($component, [null, $currency], key($component . '-' . $index))
                                @elseif($component == 'water-section')
                                    @livewire($component, [null, $currency], key($component . '-' . $index))
                                @elseif($component == 'advertisement-section')
                                    @livewire($component, [null, $currency], key($component . '-' . $index))
                                @elseif($component == 'sponsor-section')
                                    @livewire($component, [null, $currency], key($component . '-' . $index))
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
            </div>
        </div>
    </div>
</div>
