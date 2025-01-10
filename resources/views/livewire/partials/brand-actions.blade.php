<div class="flex space-x-2">
    <x-secondary-button @click="openModal('edit', '{{ json_encode($brand) }}')">Edit</x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($brand) }}')">Delete</x-danger-button>
</div>
