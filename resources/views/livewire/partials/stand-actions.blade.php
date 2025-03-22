<div class="flex space-x-2">
    <x-secondary-button @click="openModal('edit', '{{ json_encode($stand) }}')"><i
            class="fas fa-edit"></i></x-secondary-button>
    <x-danger-button @click="openModal('delete', '{{ json_encode($stand) }}')"><i
            class="fas fa-trash"></i></x-danger-button>
</div>
