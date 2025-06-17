<?php

// File: app/Models/PerformanceData.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceData extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_id',
        'temperature_profile_id',
        'vessel_configuration_id',
        'heat_input_kw',
        'primary_flow_rate_ls',
        'secondary_flow_rate_ls',
        'pressure_drop_kpa',
        'first_hour_dhw_supply',
        'subsequent_hour_dhw_supply',
        'additional_metrics',
    ];

    protected $casts = [
        'heat_input_kw' => 'decimal:2',
        'primary_flow_rate_ls' => 'decimal:4',
        'secondary_flow_rate_ls' => 'decimal:4',
        'pressure_drop_kpa' => 'decimal:2',
        'first_hour_dhw_supply' => 'decimal:2',
        'subsequent_hour_dhw_supply' => 'decimal:2',
        'additional_metrics' => 'array',
    ];

    // RELATIONSHIPS

    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }

    public function temperatureProfile(): BelongsTo
    {
        return $this->belongsTo(TemperatureProfile::class);
    }

    public function vesselConfiguration(): BelongsTo
    {
        return $this->belongsTo(VesselConfiguration::class);
    }

    // ACCESSORS

    // Check if this is DHW data
    public function getIsDhwDataAttribute(): bool
    {
        return !is_null($this->first_hour_dhw_supply) || !is_null($this->subsequent_hour_dhw_supply);
    }

    // Calculate efficiency metrics
    public function getEfficiencyRatioAttribute(): float
    {
        if ($this->primary_flow_rate_ls && $this->primary_flow_rate_ls > 0) {
            return $this->heat_input_kw / $this->primary_flow_rate_ls;
        }
        return 0;
    }

    // Get heat transfer rate per liter
    public function getHeatTransferRateAttribute(): float
    {
        return $this->heat_input_kw;
    }

    // Get primary temperature difference
    public function getPrimaryTempDiffAttribute(): ?float
    {
        if ($this->temperatureProfile) {
            return $this->temperatureProfile->primary_flow_temp - $this->temperatureProfile->primary_return_temp;
        }
        return null;
    }

    // Get secondary temperature difference
    public function getSecondaryTempDiffAttribute(): ?float
    {
        if ($this->temperatureProfile) {
            return $this->temperatureProfile->secondary_return_temp - $this->temperatureProfile->secondary_flow_temp;
        }
        return null;
    }

    // SCOPES

    public function scopeForProduct($query, $productId)
    {
        return $query->whereHas('version', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        });
    }

    public function scopeForTemperatureProfile($query, $profileId)
    {
        return $query->where('temperature_profile_id', $profileId);
    }

    public function scopeWithDhwData($query)
    {
        return $query->whereNotNull('first_hour_dhw_supply');
    }

    public function scopeByHeatRange($query, $minHeat, $maxHeat)
    {
        return $query->whereBetween('heat_input_kw', [$minHeat, $maxHeat]);
    }
}
