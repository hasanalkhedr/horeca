<div class="flex space-x-2">
    <x-secondary-button @click="openModal('edit', '{{ json_encode($category) }}')">Edit</x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($category) }}')">Delete</x-danger-button>
</div>
