<div class="flex flex-wrap -mx-3 mb-2">
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
</div>
