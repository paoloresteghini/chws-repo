<?php

// File: app/Models/VesselConfiguration.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VesselConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_id',
        'name',
        'capacity',
        'capacity_unit',
        'description',
        'specifications',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'specifications' => 'array',
    ];

    // RELATIONSHIPS

    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }

    public function performanceData(): HasMany
    {
        return $this->hasMany(PerformanceData::class);
    }

    // ACCESSORS

    // Format capacity for display
    public function getFormattedCapacityAttribute(): string
    {
        return $this->capacity ? "{$this->capacity}{$this->capacity_unit}" : $this->name;
    }

    // SCOPES

    public function scopeByCapacityRange($query, $minCapacity, $maxCapacity)
    {
        return $query->whereBetween('capacity', [$minCapacity, $maxCapacity]);
    }

    public function scopeOrderByCapacity($query, $direction = 'asc')
    {
        return $query->orderBy('capacity', $direction);
    }
}
