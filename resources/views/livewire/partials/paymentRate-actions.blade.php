<div class="flex space-x-2">
    <x-secondary-button @click="openModal('edit', '{{ json_encode($paymentRate) }}')">Edit</x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($paymentRate) }}')">Delete</x-danger-button>
</div>
