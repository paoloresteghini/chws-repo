<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model_number' => $this->model_number,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'has_vessel_options' => $this->has_vessel_options,
            'specifications' => $this->specifications,
            'category' => $this->when($this->relationLoaded('category'), function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'attachments' => $this->when($this->relationLoaded('attachments'), function () {
                return $this->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'name' => $attachment->name,
                        'file_name' => $attachment->file_name,
                        'mime_type' => $attachment->mime_type,
                        'file_size' => $attachment->file_size,
                        'url' => $attachment->url,
                        'is_image' => $attachment->is_image,
                        'is_pdf' => $attachment->is_pdf,
                        'is_document' => $attachment->is_document,
                        'created_at' => $attachment->created_at->toISOString(),
                    ];
                });
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
