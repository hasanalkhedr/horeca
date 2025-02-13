<div class="flex space-x-2">
    {{-- <div class="w-8 h-8">
        <a href="/storage/{{ $contract->path }}" target="_blank">
            <img src="{{ asset('/images/pdf.svg') }}" />
        </a>
    </div> --}}
    <div class="w-8 h-8">
        <a href="{{route('contracts.preview', $contract)}}" target="_blank">
            <img src="{{ asset('/images/pdf.svg') }}" />
        </a>
    </div>
    <a href="{{ route('contracts.edit', $contract) }}"><x-secondary-button>Edit</x-secondary-button></a>
    <x-danger-button @click="openModal('delete', '{{ json_encode($contract->id) }}')">Delete</x-danger-button>
</div>
