<?php

// File: app/Models/ProductFeature.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'feature_key',
        'feature_name',
        'feature_config',
        'is_enabled',
    ];

    protected $casts = [
        'feature_config' => 'array',
        'is_enabled' => 'boolean',
    ];

    // RELATIONSHIPS

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // HELPER METHODS

    // Get a specific config value
    public function getConfigValue(string $key, $default = null)
    {
        return $this->feature_config[$key] ?? $default;
    }

    // Set a specific config value
    public function setConfigValue(string $key, $value): void
    {
        $config = $this->feature_config ?? [];
        $config[$key] = $value;
        $this->feature_config = $config;
    }

    // Check if feature has a specific config key
    public function hasConfigKey(string $key): bool
    {
        return isset($this->feature_config[$key]);
    }

    // SCOPES

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    public function scopeByKey($query, $featureKey)
    {
        return $query->where('feature_key', $featureKey);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
