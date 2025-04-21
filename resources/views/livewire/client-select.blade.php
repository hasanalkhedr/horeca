{{-- <div class="flex flex-wrap -mx-3 mb-2">
    <!-- Parent Select -->
    <div class="w-1/3 px-3">
        <x-input-label>{{ $parentLabel }}</x-input-label>
        <x-select-input wire:model="parentField" wire:change="myupdatedParentField" name="company_id" required>
            <option value="">{{ $placeholder }}</option>
            @foreach (app($model)->all() as $item)
                <option value="{{ $item[$primaryKey] }}" {{ $parentField == $item[$primaryKey] ? 'selected' : '' }}>
                    {{ $item['name'] }}
                </option>
            @endforeach
        </x-select-input>
    </div>
    <!-- Dependent/Child Select -->
    <div class="w-1/3 px-3">
        <x-input-label>{{ $childLabel }}</x-input-label>
        <select name="coordinator_id" required
            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 w-full dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
            {{ empty($options) ? 'disabled' : '' }}>
            <option value="">Select a value</option>
            @foreach ($options as $option)
                <option value="{{ $option[$primaryKey] }}" {{ $coordinatorId == $option[$primaryKey] ? 'selected' : '' }}>
                    {{ $option['name'] }}
                </option>
            @endforeach
        </select>
    </div>
    <!-- Dependent/Child Select -->
    <div class="w-1/3 px-3">
        <x-input-label>{{ $child2Label }}</x-input-label>
        <select name="contact_person" required
            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 w-full dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
            {{ empty($options) ? 'disabled' : '' }}>
            <option value="">Select a value</option>
            @foreach ($options as $option)
                <option value="{{ $option[$primaryKey] }}" {{ $contactPerson == $option[$primaryKey] ? 'selected' : '' }}>
                    {{ $option['name'] }}
                </option>
            @endforeach
        </select>
    </div>
</div> --}}
{{-- <div class="flex flex-wrap -mx-3 mb-2">
    <!-- Searchable Company Input -->
    <div class="w-1/3 px-3" x-data="{ showList: false }" @click.away="showList = false">
        <x-input-label>Company</x-input-label>
        <input type="text"
               class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 w-full rounded-md shadow-sm"
               placeholder="Search company"
               wire:model="searchTerm"
           @keyup.debounce.500ms="$wire.performCompanySearch()"
               @focus="showList = true"
        />
        <ul x-show="showList" class="bg-white border mt-1 rounded shadow max-h-60 overflow-y-auto z-10 relative">
            @forelse($companyResults as $company)
                <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                    wire:click="selectCompany('{{ $company['item']['id'] }}', '{{ $company['item']['name'] }}')"
                >
                    {{ $company['item']['name'] }}
                </li>
            @empty
                <li class="px-4 py-2 text-gray-400">No results</li>
            @endforelse
        </ul>
    </div>

    <!-- Coordinator Select -->
    <div class="w-1/3 px-3">
        <x-input-label>Coordinator</x-input-label>
        <select name="coordinator_id" wire:model="coordinatorId"
                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 w-full rounded-md shadow-sm"
                {{ empty($coordinators) ? 'disabled' : '' }}>
            <option value="">Select a value</option>
            @foreach ($coordinators as $person)
                <option value="{{ $person['id'] }}">{{ $person['name'] }}</option>
            @endforeach
        </select>
    </div>

    <!-- Contact Person Select -->
    <div class="w-1/3 px-3">
        <x-input-label>Contact Person</x-input-label>
        <select name="contact_person" wire:model="contactPerson"
                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 w-full rounded-md shadow-sm"
                {{ empty($coordinators) ? 'disabled' : '' }}>
            <option value="">Select a value</option>
            @foreach ($coordinators as $person)
                <option value="{{ $person['id'] }}">{{ $person['name'] }}</option>
            @endforeach
        </select>
    </div>
</div> --}}
<div class="flex flex-wrap -mx-3 mb-2">
    <div class="w-1/3 px-3" x-data="{ showList: false }" @click.away="showList = false">
        <x-input-label>Company</x-input-label>
        <input type="text"
            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 w-full rounded-md shadow-sm"
            placeholder="Search company" wire:model="searchTerm" @keyup.debounce.500ms="$wire.performCompanySearch()"
            @focus="showList = true" />
        <ul x-show="showList" class="bg-white border mt-1 rounded shadow max-h-60 overflow-y-auto z-10 relative">
            @forelse($companyResults as $company)
                <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                    wire:click="selectCompany('{{ $company['item']['id'] }}', '{{ $company['item']['name'] }}'); showList= false;">
                    {{ $company['item']['name'] }}
                </li>
            @empty
                <li class="px-4 py-2 text-gray-400">No results</li>
            @endforelse
        </ul>
        <input type="hidden" name="company_id" wire:model="selectedCompany" value="{{$selectedCompany}}"/>
    </div>
    <!-- Coordinator Select -->
    <div class="w-1/3 px-3">
        <x-input-label>Coordinator</x-input-label>
        <select name="coordinator_id" wire:model="coordinatorId" class="w-full border-gray-300 rounded-md shadow-sm"
            {{ empty($coordinators) ? 'disabled' : '' }}>
            <option value="">Select coordinator</option>
            @foreach ($coordinators as $person)
                <option value="{{ $person['id'] }}">{{ $person['name'] }}</option>
            @endforeach
        </select>
    </div>

    <!-- Contact Person Select -->
    <div class="w-1/3 px-3">
        <x-input-label>Contact Person</x-input-label>
        <select name="contact_person" wire:model="contactPerson" class="w-full border-gray-300 rounded-md shadow-sm"
            {{ empty($contactPersons) ? 'disabled' : '' }}>
            <option value="">Select contact person</option>
            @foreach ($contactPersons as $person)
                <option value="{{ $person['id'] }}">{{ $person['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>
