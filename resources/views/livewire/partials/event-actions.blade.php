<div>
    <div class="flex space-x-2 pb-2">
        <a href="{{ route('events.dashboard', $event) }}"><x-primary-button>Event Dashboard</x-primary-button></a>
        <a href="{{ route('events.edit', $event) }}"><x-secondary-button>Edit</x-secondary-button></a>
        <x-danger-button @click="openModal('delete', '{{ json_encode($event) }}')">Delete</x-danger-button>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('events.stands', $event) }}"><x-primary-button>Stand Management</x-primary-button></a>
        <a href="{{ route('events.reports', $event) }}"><x-primary-button>Contract Templates</x-primary-button></a>
        <a href="{{ route('events.contracts', $event) }}"><x-primary-button>Contracts</x-primary-button></a>
    </div>
</div>
