@extends('layouts.app')
@vite(['resources/js/extractFields.js'])
@section('content')
    <div x-data="contractType()" class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Contract Types {{ 'of event: ' . $event->name }}</h1>

        <!-- Button to Add Person -->
        <x-primary-button @click="openModal('add')">Add Contract Type</x-primary-button>

        <!-- Table of Persons -->
        @if ($event)
            @livewire('contract-type-table', ['event' => $event])
        @else
            @livewire('contract-type-table')
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
                    <form @submit.prevent="submitForm" enctype="multipart/form-data">
                        <div>
                            <div class="grid grid-cols-2 gap-1 w-9/12">
                                <div>
                                    <x-input-label for="name">Type Name (Application Form, Sponsor Form,
                                        ...)</x-input-label>
                                    <x-text-input id="name" name="name" x-model="formData.name" required />
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
                                    <x-input-label for="description">Description</x-input-label>
                                    <x-textarea-input rows="4" id="description" name="description"
                                        x-model="formData.description" />
                                </div>
                                <div>
                                    <x-input-label for="path">Upload Template of the Contract Form:</x-input-label>
                                    <x-text-input type="file" @change="handleFile" accept="application/pdf"
                                        id="path" name="path" />
                                </div>

                            </div>
                            {{-- <embed id="pdfPreview" src="" type="application/pdf" width="100%" height="350px"
                                    style="display:none;">
                                <script>
                                    document.getElementById('path').addEventListener('change', function(event) {
                                        const file = event.target.files[0];

                                        if (file && file.type === 'application/pdf') {
                                            const fileURL = URL.createObjectURL(file);
                                            const pdfPreview = document.getElementById('pdfPreview');
                                            pdfPreview.src = fileURL;
                                            pdfPreview.style.display = 'block';
                                        } else {
                                            alert('Please select a valid PDF file.');
                                        }
                                    });
                                </script> --}}
                            <div id="pdfPreviewContainer" x-show="previewUrl">
                                <embed id="pdfPreview" :src="previewUrl" type="application/pdf" width="100%"
                                    height="350px">
                            </div>
                            <div class="mt-4 w-full text-center">
                                <x-primary-button type="submit"
                                    x-text="action === 'add' ? 'Create' : 'Update'"></x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
                <div x-show="action === 'delete'">
                    <p class="mb-4">Are you sure you want to delete this contract type?</p>
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
                errors: null,
                formData: {
                    name: '',
                    description: '',
                    event_id: '',
                    path: '',
                },
                previewUrl: '',
                selectedContractTypeID: null,
                selectedContractType: null,
                getBaseUrl() {
                    return `${window.location.protocol}//${window.location.host}`;
                },
                convertPathToUrl(filePath) {
                    const baseUrl = this.getBaseUrl();
                    return `${baseUrl}/storage/${filePath.replace(/\\/g, '/')}`;
                },
                handleFile(event) {
                    this.formData.path = event.target.files[0];
                    if (this.formData.path && this.formData.path.type === 'application/pdf') {
                        this.previewUrl = URL.createObjectURL(this.formData.path);
                    } else {
                        alert('Please upload a valid PDF file.');
                    }
                },
                openModal(action, contract_type = null) {
                    this.action = action;
                    this.isOpen = true;
                    this.errors = null;
                    this.modalTitle = action === 'add' ? 'Add Contract Type' : action === 'edit' ? 'Edit Contract Type' :
                        'Delete Contract Type';
                    if (contract_type) {
                        this.selectedContractType = JSON.parse(contract_type);
                        this.selectedContractTypeID = this.selectedContractType.id;
                        this.formData = {
                            ...this.selectedContractType
                        };
                        if (action === 'edit') {
                            this.modalTitle = 'Edit Contract Type: ' + this.selectedContractType.name;
                            this.previewUrl = this.convertPathToUrl(this.selectedContractType.path);
                        } else if (action === 'delete') {
                            this.modalTitle = 'Delete Contract Type: ' + this.selectedContractType.name;
                        }
                    }
                },
                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                    this.selectedContractTypeID = null;
                    this.previewUrl = null;
                    document.getElementById('path').value = '';
                },
                resetForm() {
                    this.formData = {
                        name: '',
                        description: '',
                        event_id: '',
                        path: '',
                    };
                    this.errors = null;
                },
                submitForm() {
                    if (!this.formData.path) {
                        alert('Please select a file!');
                        return;
                    }
                    const bodyData = new FormData();
                    if (this.action === 'edit') {
                        bodyData.append('_method', 'PUT');
                    }
                    bodyData.append('name', this.formData.name);
                    bodyData.append('description', this.formData.description);
                    bodyData.append('event_id', this.formData.event_id);
                    bodyData.append('path', this.formData.path);
                    const method = this.action === 'add' ? 'POST' : 'POST';
                    const url = this.action === 'add' ?
                        `{{ route('contract_types.store') }}` :
                        `{{ route('contract_types.update', '') }}/${this.selectedContractTypeID}`;
                    fetch(url, {
                            method: method,
                            headers: {
                                //'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: bodyData,
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(data => {
                                    throw data;
                                });
                            }

                            return response.json();
                        })
                        .then((data) => {
                            handleExtractFields(this.formData.path, this.formData.event_id, data.id);
                            this.closeModal();
                            location.reload();
                        })
                        .catch(error => {
                            this.errors = error;
                        });
                },
                confirmDelete() {
                    fetch(`{{ route('contract_types.destroy', '') }}/${this.selectedContractTypeID}`, {
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
        async function handleExtractFields(path, event_id, contract_type_id) {
            try {
                const fieldData = await window.extractFieldsFromPDF(path);
                console.log(event_id, contract_type_id, fieldData); // Now this should print the actual field data
                const url = `{{ route('events.contract_types.fields', ['id' => '__id__']) }}`;
                const formattedUrl = url.replace('__id__', contract_type_id);
                bodyData = new FormData();
                bodyData.append('fieldData', JSON.stringify(fieldData));
                fetch(formattedUrl, {
                    method: 'POST',
                    headers: {
                        //'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: bodyData,
                });
            } catch (error) {
                console.error('Error extracting fields:', error);
            }
        }
    </script>
@endsection
