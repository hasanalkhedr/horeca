<div class="flex space-x-2">
    <x-secondary-button @click="openModal('edit', '{{ json_encode($currency) }}')">Edit</x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($currency) }}')">Delete</x-danger-button>
</div>
