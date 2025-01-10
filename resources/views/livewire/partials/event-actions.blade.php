<div class="flex space-x-2">
    <a href="{{route('events.dashboard', $event)}}"><x-primary-button>Event Dashboard</x-primary-button></a>
    <a href="{{route('events.stands', $event)}}"><x-primary-button>Stand Management</x-primary-button></a>
    <a href="{{route('events.contract_types', $event)}}"><x-primary-button>Contract Types</x-primary-button></a>
    <a href="{{route('events.edit', $event)}}"><x-secondary-button>Edit</x-secondary-button></a>
    <x-danger-button @click="openModal('delete', '{{ json_encode($event) }}')">Delete</x-danger-button>
</div>
