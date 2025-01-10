<div class="flex">
    <div class="flex flex-wrap px-4 mx-4">
        <div class="w-full flex flex-wrap">
            <div class="w-1/2 px-4 py-2">
                <x-input-label for="name">Event Name</x-input-label>
                <x-text-input id="name" wire:model="state.name" required />
            </div>
            <div class="w-1/2 px-4 py-2">
                <x-input-label for="CODE">Event CODE</x-input-label>
                <x-text-input id="CODE" wire:model="state.CODE" required />
            </div>
            <div class="w-full px-4 py-2">
                <x-input-label for="description">Description</x-input-label>
                <x-textarea-input rows="3" id="description" wire:model="state.description" required />
            </div>

            <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />

            <div class="w-1/2 px-4 py-2">
                <x-input-label for="start_date">Start Date</x-input-label>
                <x-text-input id="start_date" type="date" wire:model="state.start_date" required />
            </div>
            <div class="w-1/2 px-4 py-2">
                <x-input-label for="end_date">End Date</x-input-label>
                <x-text-input id="end_date" type="date" wire:model="state.end_date" required />
            </div>
            <div class="w-1/2 px-4 py-2">
                <x-input-label for="apply_start_date">Apply from (date)</x-input-label>
                <x-text-input id="apply_start_date" type="date" wire:model="state.apply_start_date" required />
            </div>
            <div class="w-1/2 px-4 py-2">
                <x-input-label for="apply_deadline_date">Apply Deadline (date)</x-input-label>
                <x-text-input id="apply_deadline_date" type="date" wire:model="state.apply_deadline_date" required />
            </div>

            <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />

            <div class="w-1/3 px-4 py-2">
                <x-input-label for="total_space">Total Space</x-input-label>
                <x-text-input id="total_space" type="number" step="0.01" wire:model="state.total_space" required />
            </div>
            <div class="w-1/3 px-4 py-2">
                <x-input-label for="space_to_sell">Total To Sell Space</x-input-label>
                <x-text-input id="space_to_sell" type="number" step="0.01" wire:model="state.space_to_sell" required />
            </div>
            {{-- <div class="w-1/3 px-4 py-2">
                <x-input-label for="free_space">Total Free Space</x-input-label>
                <x-text-input id="free_space" type="number" step="0.01" wire:model="state.free_space" required />
            </div>
            <div class="w-1/3 px-4 py-2">
            </div>
            <div class="w-1/3 px-4 py-2">
                <x-input-label for="remaining_space_to_sell">Remaining To Sell Space</x-input-label>
                <x-text-input id="remaining_space_to_sell" type="number" step="0.01" wire:model="state.remaining_space_to_sell" required />
            </div>
            <div class="w-1/3 px-4 py-2">
                <x-input-label for="remaining_free_space">Remianing Free Space</x-input-label>
                <x-text-input id="remaining_free_space" type="number" step="0.01" wire:model="state.remaining_free_space" required />
            </div> --}}
        </div>
    </div>
</div>
