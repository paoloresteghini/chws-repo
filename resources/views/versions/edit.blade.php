@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Edit Version: ' . $version->model_number,
        'subTitle' => 'Update version information and settings',
        'buttonText' => 'View Version',
        'buttonUrl' => route('versions.show', $version->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('versions.index') }}" class="hover:text-primary">Versions</a>
                <i class="ki-filled ki-right text-xs"></i>
                <a href="{{ route('versions.show', $version->id) }}" class="hover:text-primary">{{ $version->model_number }}</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Edit</span>
            </div>

            <form method="POST" action="{{ route('versions.update', $version->id) }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Basic Information</h3>
                                <div class="text-sm text-gray-500">Required fields are marked with *</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Product Selection -->
                                    <div class="md:col-span-2 kt-form-item">
                                        <label for="product_id" class="kt-form-label">Product *</label>
                                        <div class="kt-form-control">
                                            <select name="product_id" id="product_id"
                                                    class="kt-select @error('product_id') border-danger @enderror"
                                                    required onchange="loadCategories()">
                                                <option value="">Select a product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}"
                                                            data-type="{{ $product->type }}"
                                                            data-has-vessels="{{ $product->has_vessel_options ? 'true' : 'false' }}"
                                                        {{ (old('product_id', $version->product_id) == $product->id) ? 'selected' : '' }}>
                                                        {{ $product->name }} ({{ $product->type }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('product_id')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Model Number -->
                                    <div class="kt-form-item">
                                        <label for="model_number" class="kt-form-label">Model Number *</label>
                                        <div class="kt-form-control">
                                            <input type="text" name="model_number" id="model_number"
                                                   class="kt-input @error('model_number') border-danger @enderror"
                                                   value="{{ old('model_number', $version->model_number) }}"
                                                   required>
                                            @error('model_number')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Unique identifier for this version</div>
                                        </div>
                                    </div>

                                    <!-- Display Name -->
                                    <div class="kt-form-item">
                                        <label for="name" class="kt-form-label">Display Name</label>
                                        <div class="kt-form-control">
                                            <input type="text" name="name" id="name"
                                                   class="kt-input @error('name') border-danger @enderror"
                                                   value="{{ old('name', $version->name) }}">
                                            @error('name')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Human-readable name (optional)</div>
                                        </div>
                                    </div>

                                    <!-- Category -->
                                    <div class="kt-form-item">
                                        <label for="category_id" class="kt-form-label">Category</label>
                                        <div class="kt-form-control">
                                            <select name="category_id" id="category_id"
                                                    class="kt-select @error('category_id') border-danger @enderror">
                                                <option value="">Select a category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ (old('category_id', $version->category_id) == $category->id) ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="kt-form-item">
                                        <label for="status" class="kt-form-label">Status</label>
                                        <div class="kt-form-control">
                                            <select name="status" id="status" class="kt-select @error('status') border-danger @enderror">
                                                <option value="1" {{ old('status', $version->status) == '1' ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ old('status', $version->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('status')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2 kt-form-item">
                                        <label for="description" class="kt-form-label">Description</label>
                                        <div class="kt-form-control">
                                            <textarea name="description" id="description" rows="3"
                                                      class="kt-textarea @error('description') border-danger @enderror"
                                                      placeholder="Optional description of this version">{{ old('description', $version->description) }}</textarea>
                                            @error('description')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Features -->
                        <div class="kt-card" id="features-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Product Features</h3>
                                <div class="text-sm text-gray-500">Configure product-specific options</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <!-- Vessel Options -->
                                <div id="vessel-options">
                                    <div class="kt-form-item">
                                        <div class="kt-form-control">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="has_vessel_options" id="has_vessel_options"
                                                       class="kt-checkbox" value="1"
                                                    {{ old('has_vessel_options', $version->has_vessel_options) ? 'checked' : '' }}>
                                                <label for="has_vessel_options" class="kt-form-label mb-0">
                                                    This version supports vessel configurations
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Enable this if the version can be configured with different vessel sizes
                                    </div>

                                    @if($version->vesselConfigurations->count() > 0)
                                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                            <div class="text-sm text-blue-800">
                                                <strong>Note:</strong> This version currently has {{ $version->vesselConfigurations->count() }} vessel configurations.
                                                Disabling vessel options will not delete existing configurations.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Specifications -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Specifications</h3>
                                <div class="text-sm text-gray-500">Additional technical specifications</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div id="specifications-container">
                                    <div class="space-y-3" id="spec-fields">
                                        @if($version->specifications)
                                            @foreach($version->specifications as $key => $value)
                                                <div class="flex gap-3">
                                                    <input type="text" name="spec_keys[]" value="{{ $key }}"
                                                           placeholder="Key" class="kt-input flex-1" onkeypress="handleSpecKeyPress(event)">
                                                    <input type="text" name="spec_values[]" value="{{ $value }}"
                                                           placeholder="Value" class="kt-input flex-1" onkeypress="handleSpecKeyPress(event)">
                                                    <button type="button" onclick="this.parentElement.remove()"
                                                            class="kt-btn kt-btn-sm kt-btn-danger">
                                                        <i class="ki-filled ki-trash"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" onclick="addSpecification()" class="kt-btn kt-btn-sm kt-btn-secondary mt-3">
                                        <i class="ki-filled ki-plus"></i>
                                        Add Specification
                                    </button>
                                </div>

                                <!-- Raw JSON Input (Advanced) -->
                                <div class="mt-6 pt-6 border-t border-gray-200 kt-form-item">
                                    <label class="kt-form-label">Raw JSON (Advanced)</label>
                                    <div class="kt-form-control">
                                        <textarea name="specifications_json" id="specifications_json" rows="4"
                                                  class="kt-textarea font-mono text-sm"
                                                  placeholder='{"key": "value", "another_key": "another_value"}'>{{ old('specifications_json', $version->specifications ? json_encode($version->specifications, JSON_PRETTY_PRINT) : '') }}</textarea>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Optional: Enter specifications as JSON. This will override individual fields above.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Attachments</h3>
                                <div class="text-sm text-gray-500">Upload documents, PDFs, and other files</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="max-w-full">
                                    @if(isset($version) && $version->attachments->count() > 0)
                                        <div class="mb-4">
                                            <p class="text-sm text-muted-foreground mb-2">Current attachments:</p>
                                            <div class="space-y-2">
                                                @foreach($version->attachments as $attachment)
                                                    <div class="flex items-center justify-between p-3 bg-secondary rounded-lg">
                                                        <div class="flex items-center gap-3">
                                                            @if($attachment->is_image)
                                                                <i class="ki-duotone ki-picture text-lg">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                            @elseif($attachment->is_pdf)
                                                                <i class="ki-duotone ki-file-pdf text-lg">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                            @else
                                                                <i class="ki-duotone ki-file text-lg">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                            @endif
                                                            <div>
                                                                <p class="font-medium">{{ $attachment->name }}</p>
                                                                <p class="text-xs text-muted-foreground">{{ $attachment->file_name }} ({{ number_format($attachment->file_size / 1024, 2) }} KB)</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            @if($attachment->is_image || $attachment->is_pdf)
                                                                <a href="{{ route('attachments.preview', $attachment) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-info" title="Preview">
                                                                    <i class="ki-filled ki-eye"></i>
                                                                </a>
                                                            @endif
{{--                                                            <a href="{{ $attachment->url }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-secondary" title="Download">--}}
{{--                                                                <i class="ki-filled ki-download"></i>--}}
{{--                                                            </a>--}}
                                                            <button type="button" onclick="deleteAttachment({{ $attachment->id }})" class="kt-btn kt-btn-sm kt-btn-destructive" title="Delete">
                                                                <i class="ki-filled ki-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <div class="space-y-3">
                                        <div id="attachment-fields" class="space-y-2">
                                            <div class="attachment-field-group flex gap-2 items-center">
                                                <div class="kt-input flex-1">
                                                    <input class="input" type="text" name="attachment_names[]" placeholder="Attachment name">
                                                </div>
                                                <div class="kt-input flex-1">
                                                    <input class="input" type="file" name="attachments[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.png,.jpg,.jpeg,.gif">
                                                </div>
                                                <button type="button" onclick="removeAttachmentField(this)" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-danger" title="Remove">
                                                    <i class="ki-filled ki-cross"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" id="add-attachment" class="kt-btn kt-btn-sm kt-btn-secondary">
                                            <i class="ki-filled ki-plus-circle"></i>
                                            Add Another Attachment
                                        </button>
                                        <p class="text-xs text-muted-foreground">Supported formats: PDF, DOC, DOCX, XLS, XLSX, TXT, PNG, JPG, JPEG, GIF</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Summary -->
                        @if($version->performanceData->count() > 0 || $version->vesselConfigurations->count() > 0)
                            <div class="kt-card">
                                <div class="kt-card-header">
                                    <h3 class="kt-card-title">Associated Data</h3>
                                    <div class="text-sm text-gray-500">Related data that will be affected by changes</div>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="grid md:grid-cols-2 gap-6">
                                        @if($version->vesselConfigurations->count() > 0)
                                            <div class="p-4 border border-gray-200 rounded-lg">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="ki-filled ki-bucket text-info"></i>
                                                    <h4 class="font-medium">Vessel Configurations</h4>
                                                </div>
                                                <div class="text-sm text-gray-600 mb-3">
                                                    {{ $version->vesselConfigurations->count() }} configurations available
                                                </div>
                                                <div class="space-y-1">
                                                    @foreach($version->vesselConfigurations->take(3) as $vessel)
                                                        <div class="text-xs text-gray-500">• {{ $vessel->name }}</div>
                                                    @endforeach
                                                    @if($version->vesselConfigurations->count() > 3)
                                                        <div class="text-xs text-gray-400">... and {{ $version->vesselConfigurations->count() - 3 }} more</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if($version->performanceData->count() > 0)
                                            <div class="p-4 border border-gray-200 rounded-lg">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="ki-filled ki-chart-simple text-success"></i>
                                                    <h4 class="font-medium">Performance Data</h4>
                                                </div>
                                                <div class="text-sm text-gray-600 mb-3">
                                                    {{ $version->performanceData->count() }} performance records
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <div>• {{ $version->availableTemperatureProfiles()->count() }} temperature profiles</div>
                                                    <div>• Heat range: {{ number_format($version->performanceData->min('heat_input_kw'), 1) }} - {{ number_format($version->performanceData->max('heat_input_kw'), 1) }} kW</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">

                        <div class="kt-card">
                            <div class="kt-card-body px-6 py-6">
                                <div class="flex flex-col gap-3">
                                    <button type="submit" class="kt-btn kt-btn-primary w-full">
                                        <i class="ki-filled ki-check"></i>
                                        Update Version
                                    </button>
                                    <a href="{{ route('versions.show', $version->id) }}" class="kt-btn kt-btn-outline w-full">
                                        <i class="ki-filled ki-eye"></i>
                                        View Version
                                    </a>
                                    <a href="{{ route('versions.index') }}" class="kt-btn kt-btn-outline w-full">
                                        <i class="ki-filled ki-arrow-left"></i>
                                        Back to List
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Current Version Info -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Current Version</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Product:</span>
                                        <span class="text-sm font-medium">{{ $version->product->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Model:</span>
                                        <span class="text-sm font-mono font-medium">{{ $version->model_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Status:</span>
                                        <span class="kt-badge kt-badge-xs {{ $version->status ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                            {{ $version->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Created:</span>
                                        <span class="text-sm">{{ $version->created_at->format('M j, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Updated:</span>
                                        <span class="text-sm">{{ $version->updated_at->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->


                        <!-- Danger Zone -->
                        <div class="kt-card border-danger">
                            <div class="kt-card-header bg-danger-light">
                                <h3 class="kt-card-title text-danger">Danger Zone</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="text-sm text-gray-600">
                                        Delete this version permanently. This action cannot be undone.
                                    </div>
                                    @if($version->performanceData->count() > 0 || $version->vesselConfigurations->count() > 0)
                                        <div class="p-3 bg-red-50 border border-red-200 rounded text-sm text-red-800">
                                            <strong>Warning:</strong> This version has associated data that will also be deleted.
                                        </div>
                                    @endif
                                    <button type="button" onclick="deleteVersion()" class="kt-btn kt-btn-destructive w-full">
                                        <i class="ki-filled ki-trash"></i>
                                        Delete Version
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Separate Delete Form -->
            <form id="delete-form" method="POST" action="{{ route('versions.destroy', $version->id) }}" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </main>

    <script>
        let specificationCount = {{ $version->specifications ? count($version->specifications) : 0 }};

        // Load categories when product changes
        function loadCategories() {
            const productSelect = document.getElementById('product_id');
            const categorySelect = document.getElementById('category_id');
            const vesselOptions = document.getElementById('vessel-options');

            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const currentCategoryId = {{ old('category_id', $version->category_id) ?: 'null' }};

            if (selectedOption.value) {
                // Show/hide vessel options based on product
                const hasVessels = selectedOption.dataset.hasVessels === 'true';
                if (!hasVessels) {
                    document.getElementById('has_vessel_options').checked = false;
                }

                // Load categories via AJAX
                fetch(`/api/categories-for-product?product_id=${selectedOption.value}`)
                    .then(response => response.json())
                    .then(categories => {
                        categorySelect.innerHTML = '<option value="">Select a category</option>';
                        categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            if (category.id == currentCategoryId) {
                                option.selected = true;
                            }
                            categorySelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading categories:', error);
                        categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                    });
            } else {
                categorySelect.innerHTML = '<option value="">Select a category</option>';
            }
        }

        // Add specification field
        function addSpecification() {
            const container = document.getElementById('spec-fields');
            const div = document.createElement('div');
            div.className = 'flex gap-3';
            div.innerHTML = `
                <input type="text" name="spec_keys[]" placeholder="Key" class="kt-input flex-1" onkeypress="handleSpecKeyPress(event)">
                <input type="text" name="spec_values[]" placeholder="Value" class="kt-input flex-1" onkeypress="handleSpecKeyPress(event)">
                <button type="button" onclick="this.parentElement.remove()" class="kt-btn kt-btn-sm kt-btn-danger">
                    <i class="ki-filled ki-trash"></i>
                </button>
            `;
            container.appendChild(div);
            specificationCount++;
        }

        // Handle Enter key in specification fields
        function handleSpecKeyPress(event) {
            if (event.key === 'Enter') {
                event.preventDefault();

                // If we're in a value field and it has content, add a new specification
                if (event.target.name === 'spec_values[]' && event.target.value.trim() !== '') {
                    addSpecification();
                    // Focus on the new key field
                    setTimeout(() => {
                        const newFields = document.querySelectorAll('input[name="spec_keys[]"]');
                        const lastField = newFields[newFields.length - 1];
                        if (lastField) {
                            lastField.focus();
                        }
                    }, 100);
                } else if (event.target.name === 'spec_keys[]') {
                    // If we're in a key field, move to the value field
                    const valueField = event.target.parentElement.querySelector('input[name="spec_values[]"]');
                    if (valueField) {
                        valueField.focus();
                    }
                }
            }
        }

        // Handle delete version
        function deleteVersion() {
            if (confirm('Are you sure you want to delete this version? This action cannot be undone and will delete all associated data.')) {
                document.getElementById('delete-form').submit();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial state based on current product
            loadCategories();

            // Add attachment functionality
            const addButton = document.getElementById('add-attachment');
            const attachmentFields = document.getElementById('attachment-fields');

            addButton.addEventListener('click', function() {
                const newFieldGroup = document.createElement('div');
                newFieldGroup.className = 'attachment-field-group flex gap-2 items-center';
                newFieldGroup.innerHTML = `
                    <div class="kt-input flex-1">
                        <input class="input" type="text" name="attachment_names[]" placeholder="Attachment name">
                    </div>
                    <div class="kt-input flex-1">
                        <input class="input" type="file" name="attachments[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.png,.jpg,.jpeg,.gif">
                    </div>
                    <button type="button" onclick="removeAttachmentField(this)" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-danger" title="Remove">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                `;
                attachmentFields.appendChild(newFieldGroup);
            });
        });

        function removeAttachmentField(button) {
            const fieldGroup = button.closest('.attachment-field-group');
            const attachmentFields = document.getElementById('attachment-fields');

            // Only remove if there's more than one field group
            if (attachmentFields.querySelectorAll('.attachment-field-group').length > 1) {
                fieldGroup.remove();
            } else {
                // Clear the inputs if it's the last one
                fieldGroup.querySelectorAll('input').forEach(input => input.value = '');
            }
        }

        function deleteAttachment(id) {
            if (confirm('Are you sure you want to delete this attachment?')) {
                fetch(`/attachments/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the attachment element from the DOM
                        location.reload();
                    } else {
                        alert('Failed to delete attachment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete attachment');
                });
            }
        }
    </script>
@endsection
