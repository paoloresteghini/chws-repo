<?php

// File: app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'image',
        'has_temperature_profiles',
        'has_vessel_options',
        'product_specific_fields',
    ];

    protected $casts = [
        'has_temperature_profiles' => 'boolean',
        'has_vessel_options' => 'boolean',
        'product_specific_fields' => 'array',
    ];

    protected $appends = ['image_url'];

    // RELATIONSHIPS

    public function versions(): HasMany
    {
        return $this->hasMany(Version::class);
    }

    public function activeVersions(): HasMany
    {
        return $this->hasMany(Version::class)->where('status', true);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(VersionCategory::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function hasFeature(string $featureKey): bool
    {
        return $this->features()->where('feature_key', $featureKey)->where('is_enabled', true)->exists();
    }

    // HELPER METHODS

    // Check if product supports a specific feature

    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class);
    }

    // Get feature configuration

    public function getFeatureConfig(string $featureKey): ?array
    {
        $feature = $this->features()->where('feature_key', $featureKey)->first();
        return $feature?->feature_config;
    }

    // Get all performance data for this product
    public function getAllPerformanceData()
    {
        return PerformanceData::whereHas('version', function ($query) {
            $query->where('product_id', $this->id);
        });
    }

    // SCOPES

    public function scopeWithTemperatureProfiles($query)
    {
        return $query->where('has_temperature_profiles', true);
    }

    public function scopeWithVesselOptions($query)
    {
        return $query->where('has_vessel_options', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // ACCESSORS

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return \Storage::disk('public')->url($this->image);
        }
        return null;
    }
}
