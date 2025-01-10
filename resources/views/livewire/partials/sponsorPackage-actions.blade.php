<div class="flex space-x-2">
    <x-secondary-button @click="openModal('edit', '{{ json_encode($sponsorPackage) }}')">Edit</x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($sponsorPackage) }}')">Delete</x-danger-button>
    <x-primary-button @click="openModal('manageOption', '{{ json_encode($sponsorPackage) }}')">Manage
        Options</x-primary-button>
</div>
