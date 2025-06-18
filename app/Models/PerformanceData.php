<?php

// File: app/Models/PerformanceData.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceData extends Model
{
    use HasFactory;

    // Heat transfer constant: kW per l/s
    const HEAT_TRANSFER_CONSTANT = 209.36;

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

    // MODEL EVENTS

    protected static function boot()
    {
        parent::boot();

        // Calculate missing values before saving
        static::saving(function ($performanceData) {
            $performanceData->calculateMissingValues();
        });
    }

    // CALCULATION METHODS

    /**
     * Calculate missing heat input or flow rate based on the relationship
     */
    public function calculateMissingValues()
    {
        // If heat input is missing but primary flow rate is provided, calculate heat input
        if (empty($this->heat_input_kw) && !empty($this->primary_flow_rate_ls)) {
            $this->heat_input_kw = self::calculateHeatInputFromFlowRate($this->primary_flow_rate_ls);
        }

        // If primary flow rate is missing but heat input is provided, calculate flow rate
        elseif (empty($this->primary_flow_rate_ls) && !empty($this->heat_input_kw)) {
            $this->primary_flow_rate_ls = self::calculateFlowRateFromHeatInput($this->heat_input_kw);
        }
    }

    /**
     * Calculate heat input from primary flow rate (STATIC)
     * Formula: Heat Input (kW) = Flow Rate (l/s) × 209.36
     */
    public static function calculateHeatInputFromFlowRate(float $flowRate): float
    {
        return round($flowRate * self::HEAT_TRANSFER_CONSTANT, 2);
    }

    /**
     * Calculate primary flow rate from heat input (STATIC)
     * Formula: Flow Rate (l/s) = Heat Input (kW) ÷ 209.36
     */
    public static function calculateFlowRateFromHeatInput(float $heatInput): float
    {
        return round($heatInput / self::HEAT_TRANSFER_CONSTANT, 4);
    }

    /**
     * Instance methods for backward compatibility
     */
    public function calculateHeatInputFromFlowRateInstance(float $flowRate): float
    {
        return self::calculateHeatInputFromFlowRate($flowRate);
    }

    public function calculateFlowRateFromHeatInputInstance(float $heatInput): float
    {
        return self::calculateFlowRateFromHeatInput($heatInput);
    }

    /**
     * Get theoretical heat input based on current flow rate
     */
    public function getTheoreticalHeatInputAttribute(): float
    {
        if (!$this->primary_flow_rate_ls) {
            return 0;
        }
        return self::calculateHeatInputFromFlowRate($this->primary_flow_rate_ls);
    }

    /**
     * Get theoretical flow rate based on current heat input
     */
    public function getTheoreticalFlowRateAttribute(): float
    {
        if (!$this->heat_input_kw) {
            return 0;
        }
        return self::calculateFlowRateFromHeatInput($this->heat_input_kw);
    }

    /**
     * Check if heat input matches theoretical calculation (within tolerance)
     */
    public function getIsHeatInputAccurateAttribute(): bool
    {
        if (!$this->primary_flow_rate_ls || !$this->heat_input_kw) {
            return true; // Can't validate without both values
        }

        $theoretical = $this->theoretical_heat_input;
        $tolerance = 5; // 5 kW tolerance

        return abs($this->heat_input_kw - $theoretical) <= $tolerance;
    }

    /**
     * Get the difference between actual and theoretical heat input
     */
    public function getHeatInputVarianceAttribute(): float
    {
        if (!$this->primary_flow_rate_ls || !$this->heat_input_kw) {
            return 0;
        }

        return $this->heat_input_kw - $this->theoretical_heat_input;
    }

    /**
     * Validate the relationship between heat input and flow rate
     */
    public function validateHeatFlowRelationship(): array
    {
        $issues = [];

        if ($this->primary_flow_rate_ls && $this->heat_input_kw) {
            $theoretical = $this->theoretical_heat_input;
            $variance = abs($this->heat_input_kw - $theoretical);

            if ($variance > 10) { // More than 10 kW difference
                $issues[] = "Heat input ({$this->heat_input_kw} kW) doesn't match expected value ({$theoretical} kW) for flow rate ({$this->primary_flow_rate_ls} l/s)";
            }
        }

        return $issues;
    }

    /**
     * Auto-correct heat input based on flow rate
     */
    public function autoCorrectHeatInput(): bool
    {
        if ($this->primary_flow_rate_ls) {
            $correctedHeat = self::calculateHeatInputFromFlowRate($this->primary_flow_rate_ls);

            if (abs($this->heat_input_kw - $correctedHeat) > 1) {
                $this->heat_input_kw = $correctedHeat;
                return true;
            }
        }

        return false;
    }

    /**
     * Auto-correct flow rate based on heat input
     */
    public function autoCorrectFlowRate(): bool
    {
        if ($this->heat_input_kw) {
            $correctedFlow = self::calculateFlowRateFromHeatInput($this->heat_input_kw);

            if (abs($this->primary_flow_rate_ls - $correctedFlow) > 0.01) {
                $this->primary_flow_rate_ls = $correctedFlow;
                return true;
            }
        }

        return false;
    }

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

    // EXISTING ACCESSORS

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

    // EXISTING SCOPES

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

    // NEW SCOPES FOR CALCULATION FUNCTIONALITY

    public function scopeByFlowRange($query, $minFlow, $maxFlow)
    {
        return $query->whereBetween('primary_flow_rate_ls', [$minFlow, $maxFlow]);
    }

    /**
     * Scope to find records with heat/flow mismatches
     */
    public function scopeWithHeatFlowMismatch($query, $tolerance = 5)
    {
        return $query->whereRaw('ABS(heat_input_kw - (primary_flow_rate_ls * ?)) > ?', [
            self::HEAT_TRANSFER_CONSTANT,
            $tolerance
        ]);
    }

    /**
     * Scope to find records with accurate heat/flow relationship
     */
    public function scopeWithAccurateHeatFlow($query, $tolerance = 5)
    {
        return $query->whereRaw('ABS(heat_input_kw - (primary_flow_rate_ls * ?)) <= ?', [
            self::HEAT_TRANSFER_CONSTANT,
            $tolerance
        ]);
    }
}
