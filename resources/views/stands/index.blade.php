@extends('layouts.app')
@section('content')
    <div x-data="standModal()" class="max-w-7xl mx-auto p-6">
        <div class="flex">
            <h1 class="w-1/2 text-3xl font-semibold mb-4">Stands {{ 'of event: ' . $event->name }}</h1>
            <div class="w-1/2 justify-end text-right">
                <!-- Button to Add Stand -->
                <x-primary-button @click="openModal('add')" title="Add Stand"><i class="fas fa-plus"></i><i class="fas fa-cube"></i></x-primary-button>
                @if ($event->id > 0)
                    <x-primary-button @click="openModal('add-many')" title="Add Multiple Stands"><i class="fas fa-plus"></i><i class="fas fa-cubes"></i></x-primary-button>
                    <x-primary-button @click="openModal('import')" title="Import stands from file"><i class="fas fa-file-upload"></i><i class="fas fa-cubes"></i></x-primary-button>
                @endif
            </div>
        </div>



        <!-- Table of Stands -->
        @if ($event)
            @livewire('stand-table', ['event' => $event])
        @else
            @livewire('stand-table')
        @endif

        <!-- Modal -->
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

                <!-- Validation Errors -->
                <div x-show="errors" class="mb-4">
                    <ul class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <template x-for="(error, field) in errors" :key="field">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>

                <!-- Form based on action type -->
                <div x-show="action === 'add' || action === 'edit'">
                    <form @submit.prevent="submitForm">
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="no">Stand No.</x-input-label>
                                    <x-text-input id="no" x-model="formData.no" required />
                                </div>
                                <div>
                                    <x-input-label for="deductable">Deductable</x-input-label>
                                    <x-select-input name="deductable" id="deductable" x-model="formData.deductable"
                                        required>
                                        <option>-- Select Value--</option>
                                        <option value="1">Deductable</option>
                                        <option value="0">Not Deductable</option>
                                    </x-select-input>
                                </div>

                                <div>
                                    <x-input-label for="space">Space (sq. m)</x-input-label>
                                    <x-text-input type="number" id="space" x-model="formData.space" required />
                                </div>
                                @if ($event->id == null)
                                    <div>
                                        <x-input-label for="event_id">Event</x-input-label>
                                        <x-select-input name="event_id" id="event_id" x-model="formData.event_id" required>
                                            <option value="">-- Select Event --</option>
                                            @foreach ($events as $evt)
                                                <option value="{{ $evt->id }}">{{ $evt->name }}</option>
                                            @endforeach
                                        </x-select-input>
                                    </div>
                                @else
                                    <div>
                                        <x-input-label for="event_id">Event</x-input-label>
                                        <x-select-input name="event_id" id="event_id" x-model="formData.event_id" required>
                                            <option value="">-- Select Event --</option>
                                            <option selected value="{{ $event->id }}">{{ $event->name }}</option>
                                        </x-select-input>
                                    </div>
                                @endif
                                <div>
                                    <x-input-label for="category_id">Category</x-input-label>
                                    <x-select-input id="category_id" x-model="formData.category_id">
                                        <option value="">-- Select Category --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </x-select-input>
                                </div>
                                {{-- <div>
                                    <x-input-label for="stand_type_id">Stand Type</x-input-label>
                                    <x-select-input id="stand_type_id" x-model="formData.stand_type_id">
                                        <option value="">-- Select Stand Type --</option>
                                        @foreach ($stand_types as $stand_type)
                                            <option value="{{ $stand_type->id }}">{{ $stand_type->name }}</option>
                                        @endforeach
                                    </x-select-input>
                                </div> --}}
                            </div>
                            <div class="mt-4 w-full text-center">
                                <x-primary-button type="submit"
                                    x-text="action === 'add' ? 'Create' : 'Update'"></x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this stand?</p>
                    <x-danger-button type="button" @click="confirmDelete()">Delete</x-danger-button>
                </div>
                <div x-show="action === 'add-many'">
                    <form @submit.prevent="submitForm">
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="prefix">Stand No. Prefix</x-input-label>
                                    <x-text-input id="prefix" x-model="formData.multi.prefix" required />
                                </div>
                                <div>
                                    <x-input-label for="num">Number of stands to ADD</x-input-label>
                                    <x-text-input id="num" x-model="formData.multi.num" required />
                                </div>
                                <div>
                                    <x-input-label for="deductable">Deductable</x-input-label>
                                    <x-select-input name="deductable" id="deductable" x-model="formData.multi.deductable"
                                        required>
                                        <option value="">-- Select Value --</option>
                                        <option value="1">Deductable</option>
                                        <option value="0">Not Deductable</option>
                                    </x-select-input>
                                </div>

                                <div>
                                    <x-input-label for="space">Space (sq. m)</x-input-label>
                                    <x-text-input type="number" id="space" x-model="formData.multi.space" required />
                                </div>
                                <input type="hidden" name="event_id" x-model="formData.multi.event_id"
                                    value="{{ $event->id }}">
                                <div>
                                    <x-input-label for="category_id">Category</x-input-label>
                                    <x-select-input id="category_id" x-model="formData.multi.category_id">
                                        <option value="">-- Select Category --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </x-select-input>
                                </div>

                            </div>
                            <div class="mt-4 w-full text-center">
                                <x-primary-button type="submit">Add Stands</x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
                <div x-show="action === 'import'" class="p-6 bg-white shadow-md rounded-lg">
                    <p class="text-gray-700 mb-4">Please download the stands table template, fill it, and then upload it.
                    </p>

                    <div class="flex justify-center mb-6">
                        <a href="/stands-template.xlsx" class="inline-block">
                            <button
                                class="px-4 py-2 bg-blue-500 text-white font-bold rounded hover:bg-blue-600 transition duration-300">
                                Download Stands Table Template
                            </button>
                        </a>
                    </div>

                    <form @submit.prevent="uploadFile" class="space-y-4">
                        <label for="file" class="block text-gray-700 font-medium">
                            Upload Stands File: (file with 4 columns only, titled as: no, space, deductible, category)
                        </label>
                        <div class="flex justify-center">
                            <input type="file" id="file" @change="handleFileUpload"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        </div>
                        <div class="flex justify-center">
                            <button type="submit"
                                class="px-4 py-2 bg-green-500 text-white font-bold rounded hover:bg-green-600 transition duration-300">
                                Upload
                            </button>
                        </div>
                    </form>

                    <!-- Display Messages -->
                    <div x-show="message" x-text="message" class="mt-4 text-center text-sm"
                        :class="{ 'text-green-600': message.includes('success'), 'text-red-600': message.includes('error') }">
                    </div>
                </div>
                <div x-show="action === 'block'">
                    <p class="mb-4">Are you sure you want to BLOCK this stand?</p>
                    <x-danger-button type="button" @click="confirmBlock()">BLOCK</x-danger-button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function standModal() {
            return {
                event_id: {{ $event->id ?? 0 }},
                isOpen: false,
                action: '',
                modalTitle: '',
                errors: null,
                formData: {
                    no: '',
                    space: '',
                    event_id: {{ $event->id ?? 0 }},
                    category_id: '',
                    // stand_type_id: '',
                    deductable: '',
                    multi: {
                        prefix: '',
                        num: '',
                        space: '',
                        event_id: {{ $event->id ?? 0 }},
                        category_id: '',
                        deductable: ''
                    },

                },

                selectedStandId: null,
                selectedStand: null,
                file: null,
                message: '',
                handleFileUpload(event) {
                    this.file = event.target.files[0];
                },
                async uploadFile() {
                    if (!this.file) {
                        this.message = 'Please select a file.';
                        return;
                    }

                    const formData = new FormData();
                    formData.append('file', this.file);
                    try {
                        const response = await fetch(`/stands/import/${this.event_id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: formData,
                        });

                        if (!response.ok) {
                            const errorText = await response.text();
                            throw new Error(errorText);
                        }

                        const result = await response.json();
                        this.message = result.message || 'File uploaded successfully!';
                        this.closeModal();
                        location.reload();
                    } catch (error) {
                        this.message = 'Error: ' + error.message;
                    }
                },

                openModal(action, stand = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Stand' :
                        action === 'edit' ? 'Edit Stand' :
                        action === 'delete' ? 'Delete Stand' :
                        action === 'add-many' ? 'Add Multiple Stands' :
                        action === 'import' ? 'Import Stands data from excel file' :
                        action === 'block' ? 'BLOCK Stand ' : '';
                    if (stand) {
                        this.selectedStand = JSON.parse(stand);
                        this.selectedStandId = this.selectedStand.id;

                        if (action === 'edit') {
                            this.formData = {
                                ...this.selectedStand
                            };
                            this.modalTitle = 'Edit Stand: ' + this.selectedStand.no + '|' + this.selectedStand.CODE;
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Stand: ' + this.selectedStand.no + '|' + this.selectedStand.CODE;
                        }
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedStandId = null;
                },

                resetForm() {
                    this.formData = {
                        no: '',
                        space: '',
                        event_id: '',
                        category_id: '',
                        //   stand_type_id: '',
                        deductable: '',
                        multi: {
                            prefix: '',
                            num: '',
                            space: '',
                            event_id: '',
                            category_id: '',
                            deductable: ''
                        }
                    };
                    this.errors = null;
                },

                submitForm() {
                    const method = this.action === 'add' || this.action === 'add-many' ? 'POST' : 'PUT';
                    const url = this.action === 'add' ?
                        `{{ route('stands.store') }}` : this.action === 'edit' ?
                        `{{ route('stands.update', '') }}/${this.selectedStandId}` :
                        `{{ route('stands.storeMany') }}`;
                    const bodyData = this.action === 'add-many' ? JSON.stringify(this.formData.multi) : JSON.stringify(this
                        .formData);
                    fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: bodyData
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(data => {
                                    throw data;
                                });
                            }
                            return response.json();
                        })
                        .then(() => {
                            this.closeModal();
                            location.reload();
                        })
                        .catch(error => {
                            this.errors = error;
                        });
                },

                confirmDelete() {
                    fetch(`{{ route('stands.destroy', '') }}/${this.selectedStandId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(() => {
                            this.closeModal();
                            location.reload();
                        });
                },

                confirmBlock() {
                    fetch(`/stands/${this.selectedStandId}/block`, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(response => {
                            if (!response.ok) {
                                return response.json().then(data => {
                                    throw data;
                                });
                            }
                            return response.json();
                        })
                        .then(() => {
                            this.closeModal();
                            location.reload();
                        }).catch(error => {
                            console.log(error);
                            this.errors = error;
                        });
                }
            };
        }
    </script>
@endsection
