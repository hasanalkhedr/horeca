@extends('layouts.app')
@section('content')
    <div x-data="contractType()" class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Contract Templates {{ 'of event: ' . $event->name }}</h1>

        <!-- Button to Add Person -->
        <a href="{{ route('report.builder') }}"><x-primary-button>Add Contract Template</x-primary-button></a>

        <!-- Table of Persons -->
        @if ($event)
            @livewire('report-table', ['event' => $event])
        @else
            @livewire('report-table')
        @endif

        <!-- Modal -->
        <div x-show="isOpen" @click.away="closeModal()" @keydown.escape.window="closeModal()" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" x-transition>
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-4xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold" x-text="modalTitle"></h2>
                    <button @click="closeModal()"
                        class="text-gray-600 text-3xl hover:text-gray-800 transition-colors duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this contract Template?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function contractType() {
            return {
                isOpen: false,
                action: '',
                modalTitle: '',
                selectedContractTypeID: null,
                selectedContractType: null,
                openModal(action, contract_type = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = 'Delete Contract Template';
                    if (contract_type) {
                        this.selectedContractType = JSON.parse(contract_type);
                        this.selectedContractTypeID = this.selectedContractType.id;

                        if (action === 'delete') {
                            this.modalTitle = 'Delete Contract Template: ' + this.selectedContractType.name;
                        }
                    }
                },
                closeModal() {
                    this.isOpen = false;
                    this.selectedContractTypeID = null;
                },

                confirmDelete() {
                    fetch(`{{ route('reports.destroy', '') }}/${this.selectedContractTypeID}`, {
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
