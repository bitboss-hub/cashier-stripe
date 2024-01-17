<?php

namespace BitbossHub\Cashier\Models;

use BitbossHub\Cashier\Traits\CanUpdateQuietlyTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StripeData extends CashierModel
{
    use CanUpdateQuietlyTrait;

    protected $table = "stripe_data";

    protected $fillable = [
        'stripe_id',
        'address',
        'description',
        'email',
        'metadata',
        'name',
        'phone'
    ];

    protected $casts = [
        'address' => 'array',
        'metadata' => 'array'
    ];

    /**
    * Get the parent stripeable model.
    */
    public function stripeable(): MorphTo
    {
        return $this->morphTo();
    }
}
