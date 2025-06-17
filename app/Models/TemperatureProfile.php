<?php

// File: app/Models/TemperatureProfile.php
namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemperatureProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'primary_flow_temp',
        'primary_return_temp',
        'secondary_flow_temp',
        'secondary_return_temp',
        'description',
        'is_active',
    ];

    protected $casts = [
        'primary_flow_temp' => 'decimal:2',
        'primary_return_temp' => 'decimal:2',
        'secondary_flow_temp' => 'decimal:2',
        'secondary_return_temp' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // RELATIONSHIPS

    public function performanceData(): HasMany
    {
        return $this->hasMany(PerformanceData::class);
    }

    // Get products that use this temperature profile
    public function products(): Collection
    {
        return Product::whereHas('versions.performanceData', function ($query) {
            $query->where('temperature_profile_id', $this->id);
        })->distinct()->get();
    }

    // Get versions that use this temperature profile
    public function versions(): Collection
    {
        return Version::whereHas('performanceData', function ($query) {
            $query->where('temperature_profile_id', $this->id);
        })->distinct()->get();
    }

    // ACCESSORS

    // Generate display name for the profile
    public function getDisplayNameAttribute(): string
    {
        return "Primary: {$this->primary_flow_temp}°→{$this->primary_return_temp}°, Secondary: {$this->secondary_flow_temp}°→{$this->secondary_return_temp}°";
    }

    // Get primary temperature difference
    public function getPrimaryTempDifferenceAttribute(): float
    {
        return $this->primary_flow_temp - $this->primary_return_temp;
    }

    // Get secondary temperature difference
    public function getSecondaryTempDifferenceAttribute(): float
    {
        return $this->secondary_return_temp - $this->secondary_flow_temp;
    }

    // SCOPES

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPrimaryTempRange($query, $minTemp, $maxTemp)
    {
        return $query->whereBetween('primary_flow_temp', [$minTemp, $maxTemp]);
    }

    public function scopeBySecondaryTempRange($query, $minTemp, $maxTemp)
    {
        return $query->whereBetween('secondary_flow_temp', [$minTemp, $maxTemp]);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->whereHas('performanceData.version', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        });
    }
}
