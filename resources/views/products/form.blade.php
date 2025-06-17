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
</table>
