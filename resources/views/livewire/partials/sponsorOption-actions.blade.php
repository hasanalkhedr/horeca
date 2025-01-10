<div class="flex space-x-2">
    <x-secondary-button @click="openModal('edit', '{{ json_encode($sponsorOption) }}')">Edit</x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($sponsorOption) }}')">Delete</x-danger-button>
</div>
