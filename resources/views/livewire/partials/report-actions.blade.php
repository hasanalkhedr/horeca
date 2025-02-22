<div class="flex space-x-2">
    <a target="_blank" href="{{route('reports.show', $report)}}"><x-primary-button>Preview</x-primary-button></a>
    <a target="_blank" href="{{route('report.editor', $report)}}"><x-secondary-button>Edit</x-secondary-button></a>
    <x-danger-button @click="openModal('delete', '{{ json_encode($report) }}')">Delete</x-danger-button>
</div>
