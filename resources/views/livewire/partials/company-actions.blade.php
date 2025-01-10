<div class="flex space-x-2">
    <a href="{{route('companies.clients', $company)}}"><x-primary-button>Persons Management</x-primary-button></a>
    <a href="{{route('companies.brands', $company)}}"><x-primary-button>Brands Management</x-primary-button></a>
    <x-secondary-button @click="openModal('edit', '{{ json_encode($company) }}')">Edit</x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($company) }}')">Delete</x-danger-button>
</div>
