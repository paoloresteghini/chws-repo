<table class="kt-table align-start text-sm text-muted-foreground">
    <tr>
        <td class="text-secondary-foreground font-normal">
            Name
        </td>
        <td class="text-foreground font-normal">
            <div class="kt-input max-w-[400px]">
                <input class="input" id="name" type="text" name="name"
                       value="{{ old('name', $product->name ?? '') }}" required>
            </div>
        </td>
    </tr>
    <tr>
        <td class="text-secondary-foreground font-normal">
            Description
        </td>
        <td class="text-foreground font-normal">
                <textarea class="kt-textarea" id="description" name="description" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
        </td>
    </tr>
    <tr>
        <td class="text-secondary-foreground font-normal">
            Image
        </td>
        <td class="text-foreground font-normal">
            <div class="max-w-[400px]">
                @if(isset($product) && $product->image)
                    <div class="mb-3">
                        <p class="text-sm text-muted-foreground mb-2">Current image:</p>
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover rounded">
                    </div>
                @endif
                <div class="kt-input">
                    <input class="input" id="image" type="file" name="image" accept="image/*">
                </div>
                @if(Route::currentRouteName() === 'products.edit')
                    <p class="text-xs text-muted-foreground mt-1">Leave empty to keep current image</p>
                @endif
            </div>
        </td>
    </tr>
    <tr>
        <td class="text-secondary-foreground font-normal">
            Attachments
        </td>
        <td class="text-foreground font-normal">
            <div class="max-w-[600px]">
                @if(isset($product) && $product->attachments->count() > 0)
                    <div class="mb-4">
                        <p class="text-sm text-muted-foreground mb-2">Current attachments:</p>
                        <div class="space-y-2">
                            @foreach($product->attachments as $attachment)
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
                                        <a href="{{ $attachment->url }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-secondary" title="Download">
                                            <i class="ki-filled ki-download"></i>
                                        </a>
                                        <button type="button" onclick="deleteAttachment({{ $attachment->id }})" class="kt-btn kt-btn-sm kt-btn-danger" title="Delete">
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
        </td>
    </tr>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
