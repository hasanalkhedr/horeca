<div class="flex space-x-2">
    <a target="_blank" href="{{route('reports.show', $report)}}"><x-primary-button>Preview</x-primary-button></a>
    {{-- <x-secondary-button @click="openModal('edit', '{{ json_encode($contract_type) }}')">Edit</x-secondary-button> --}}
    <x-danger-button @click="openModal('delete', '{{ json_encode($report) }}')">Delete</x-danger-button>
</div>
