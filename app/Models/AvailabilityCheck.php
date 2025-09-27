<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityCheck extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'was_available',
        'is_available',
        'price_at_check',
        'price',
        'checked_at',
    ];

    protected $casts = [
        'was_available' => 'boolean',
        'is_available' => 'boolean',
        'price_at_check' => 'decimal:2',
        'price' => 'decimal:2',
        'checked_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
