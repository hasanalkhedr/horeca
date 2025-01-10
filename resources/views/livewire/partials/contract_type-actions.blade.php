<div class="flex space-x-2">
    <a href="{{route('events.contract_types.view_fields', $contract_type)}}"><x-primary-button>Fields</x-primary-button></a>
    <x-secondary-button @click="openModal('edit', '{{ json_encode($contract_type) }}')">Edit</x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($contract_type) }}')">Delete</x-danger-button>
</div>
