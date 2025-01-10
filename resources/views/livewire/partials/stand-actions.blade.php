<div class="flex space-x-2">
    <x-secondary-button @click="openModal('edit', '{{ json_encode($stand) }}')">Edit</x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($stand) }}')">Delete</x-danger-button>
</div>
