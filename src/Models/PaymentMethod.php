<?php

namespace BitbossHub\Cashier\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;

class PaymentMethod
{
    public function getTable()
    {
        $prefix = config('cashier.database.table_prefix', '');

        return $prefix.'payment_method';
    }

    protected $fillable = [
        'stripe_id',
        'gateway',
        'pm_type',
        'pm_last_four',
        'default',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'default' => 'boolean'
    ];

    public function scopeDefault(Builder $query)
    {
        return $query->where('default', true);
    }

    /**
     * Get the parent stripeable model.
     */
    public function stripeable(): MorphTo
    {
        return $this->morphTo();
    }
}
