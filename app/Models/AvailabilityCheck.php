<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityCheck extends Model
{
    protected $fillable = [
        'product_variant_id',
        'was_available',
        'price_at_check',
        'checked_at',
    ];

    protected $casts = [
        'was_available' => 'boolean',
        'price_at_check' => 'decimal:2',
        'checked_at' => 'datetime',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
