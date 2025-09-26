<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'variant_price',
        'sku',
        'is_available',
    ];

    protected $casts = [
        'variant_price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function trackedItems(): HasMany
    {
        return $this->hasMany(UserTrackedItem::class);
    }

    public function availabilityChecks(): HasMany
    {
        return $this->hasMany(AvailabilityCheck::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('is_available', false);
    }

    public function scopeRecentlyChanged(Builder $query, int $hours = 24): Builder
    {
        return $query->whereHas('availabilityChecks', function (Builder $query) use ($hours) {
            $query->where('checked_at', '>=', Carbon::now()->subHours($hours));
        });
    }

    protected function isInStock(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->is_available;
            },
        );
    }

    protected function isOutOfStock(): Attribute
    {
        return Attribute::make(
            get: function () {
                return !$this->is_available;
            },
        );
    }
}
