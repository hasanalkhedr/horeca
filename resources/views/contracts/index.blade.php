@extends('layouts.app')
@section('content')
    <div x-data="contractModal()" class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Contracts {{$event?': Event '.$event->name : ''}}</h1>

        <!-- Button to Add Event -->
        {{-- <x-primary-button><a href="{{ route('events.create') }}">Add Event</a></x-primary-button> --}}

        <!-- Table of Events -->
        @if ($event)
            @livewire('contract-table', ['event' => $event])
        @else
            @livewire('contract-table')
        @endif

        <div x-show="isOpen" @click.away="closeModal()" @keydown.escape.window="closeModal()" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold" x-text="modalTitle"></h2>
                    <button @click="closeModal()"
                        class="text-gray-600 text-3xl hover:text-gray-800 transition-colors duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div>
                    <p class="mb-4">Are you sure you want to delete this Contract?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function contractModal() {
            return {
                isOpen: false,
                selectedContractId: null,
                modalTitle: '',


                openModal(action, contract = null) {
                    this.isOpen = true;
                    if (contract) {
                        this.modalTitle = 'Confirm Delete: Contract '
                        this.selectedContractId = contract;
                    }
                },

                closeModal() {
                    this.isOpen = false;
                },
                confirmDelete() {
                    fetch(`{{ route('contracts.destroy', '') }}/${this.selectedContractId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(() => {
                            this.closeModal();
                            location.reload();
                        });
                }
            };
        }
    </script>
@endsection
