<?php

// File: app/Models/VersionCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VersionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'prefix',
        'description',
        'sort_order',
        'category_specs',
    ];

    protected $casts = [
        'category_specs' => 'array',
    ];

    // RELATIONSHIPS

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAllPerformanceData()
    {
        return PerformanceData::whereHas('version', function ($query) {
            $query->where('category_id', $this->id);
        });
    }

    public function getVersionCountAttribute(): int
    {
        return $this->versions()->count();
    }

    // HELPER METHODS

    // Get all performance data for versions in this category

    public function versions(): HasMany
    {
        return $this->hasMany(Version::class, 'category_id');
    }

    // Get count of versions in this category

    public function getActiveVersionCountAttribute(): int
    {
        return $this->activeVersions()->count();
    }

    // Get count of active versions in this category

    public function activeVersions(): HasMany
    {
        return $this->hasMany(Version::class, 'category_id')->where('status', true);
    }

    // SCOPES

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeWithPrefix($query, $prefix)
    {
        return $query->where('prefix', $prefix);
    }
}
