<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'url',
        'external_product_id',
        'product_variant_name',
        'product_variant_id',
        'price',
        'image_url',
        'is_tracked',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_tracked' => 'boolean'
    ];

    public function availabilityChecks(): HasMany
    {
        return $this->hasMany(AvailabilityCheck::class);
    }

     protected function variantUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->url . '?variant=' . $this->product_variant_id;
            },
        );
    }

    protected function lastChecked(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->availabilityChecks()->latest()->first()?->created_at;
            },
        );
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->availabilityChecks()->latest()->first()?->is_available;
            },
        );
    }
}
