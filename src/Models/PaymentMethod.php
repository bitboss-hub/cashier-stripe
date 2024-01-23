<?php

namespace BitbossHub\Cashier\Models;

use BitbossHub\Cashier\Enums\GatewaysEnum;
use BitbossHub\Cashier\Traits\CrudQuietlyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;

class PaymentMethod extends Model
{
    use CrudQuietlyTrait;

    public function getTable()
    {
        $prefix = config('cashier.database.table_prefix', '');

        return $prefix.'payment_methods';
    }

    protected $fillable = [
        'stripe_id',
        'customer_id',
        'gateway',
        'type',
        'pm_type',
        'pm_last_four',
        'default',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'default' => 'boolean',
        'gateway' => GatewaysEnum::class,
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
