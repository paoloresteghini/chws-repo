<?php

// File: app/Http/Resources/ProductResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'has_temperature_profiles' => $this->has_temperature_profiles,
            'has_vessel_options' => $this->has_vessel_options,
            'features' => $this->when($this->relationLoaded('features'), function () {
                return $this->features->map(function ($feature) {
                    return [
                        'key' => $feature->feature_key,
                        'name' => $feature->feature_name,
                        'enabled' => $feature->is_enabled,
                        'config' => $feature->feature_config,
                    ];
                });
            }),
            'versions_count' => $this->when($this->relationLoaded('versions'), function () {
                return $this->versions->where('status', true)->count();
            }),
            'performance_records_count' => $this->when($this->relationLoaded('versions'), function () {
                return $this->versions->sum(function ($version) {
                    return $version->relationLoaded('performanceData')
                        ? $version->performanceData->count()
                        : 0;
                });
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
