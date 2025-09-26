<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserTrackedItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_variant_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereHas('productVariant', function (Builder $query) {
            $query->where('is_available', true);
        });
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereHas('productVariant', function (Builder $query) {
            $query->where('is_available', false);
        });
    }

    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->deleted_at === null;
            },
        );
    }
}
