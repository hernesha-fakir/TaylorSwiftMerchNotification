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
        'price',
        'image_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function trackedItems(): HasManyThrough
    {
        return $this->hasManyThrough(UserTrackedItem::class, ProductVariant::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereHas('variants', function (Builder $query) {
            $query->where('is_available', true);
        });
    }

    public function scopeUnavailable(Builder $query): Builder
    {
        return $query->whereDoesntHave('variants', function (Builder $query) {
            $query->where('is_available', true);
        });
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->variants()->where('is_available', true)->exists();
            },
        );
    }
}
