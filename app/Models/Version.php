<?php

// File: app/Models/Version.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Version extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'model_number',
        'name',
        'description',
        'image',
        'status',
        'category_id',
        'has_vessel_options',
        'specifications',
    ];

    protected $casts = [
        'status' => 'boolean',
        'has_vessel_options' => 'boolean',
        'specifications' => 'array',
    ];

    // RELATIONSHIPS

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VersionCategory::class, 'category_id');
    }

    public function vesselConfigurations(): HasMany
    {
        return $this->hasMany(VesselConfiguration::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function getPerformanceData($temperatureProfileId = null, $vesselConfigId = null)
    {
        $query = $this->performanceData();

        if ($temperatureProfileId) {
            $query->where('temperature_profile_id', $temperatureProfileId);
        }

        if ($vesselConfigId) {
            $query->where('vessel_configuration_id', $vesselConfigId);
        }

        return $query->get();
    }

    // HELPER METHODS

    // Get performance data with optional filters

    public function performanceData(): HasMany
    {
        return $this->hasMany(PerformanceData::class);
    }

    // Get performance for specific temperature profile

    public function getPerformanceForProfile($profileId, $vesselConfigId = null)
    {
        $query = $this->performanceData()->where('temperature_profile_id', $profileId);

        if ($vesselConfigId) {
            $query->where('vessel_configuration_id', $vesselConfigId);
        }

        return $query->first();
    }

    // Get all available temperature profiles for this version
    public function availableTemperatureProfiles()
    {
        if (!$this->product->has_temperature_profiles) {
            return collect();
        }

        return TemperatureProfile::whereHas('performanceData', function ($query) {
            $query->where('version_id', $this->id);
        })->get();
    }

    // Get series identifier (first 2 digits for numeric models)
    public function getSeriesAttribute(): ?string
    {
        if (is_numeric($this->model_number)) {
            return substr($this->model_number, 0, 2);
        }
        return null;
    }

    // SCOPES

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByProductType($query, $type)
    {
        return $query->whereHas('product', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    public function scopeBySeries($query, $series)
    {
        return $query->where('model_number', 'like', $series . '%');
    }

    public function scopeWithVessels($query)
    {
        return $query->where('has_vessel_options', true);
    }
}
