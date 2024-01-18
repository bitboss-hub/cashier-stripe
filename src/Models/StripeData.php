<?php

namespace BitbossHub\Cashier\Models;

use BitbossHub\Cashier\Traits\CrudQuietlyTrait;
use BitbossHub\Cashier\Utilities\Gateways\Stripe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;

class StripeData extends Model
{
    use CrudQuietlyTrait;

    public function getTable()
    {
        $prefix = config('cashier.database.table_prefix', '');

        return $prefix.'stripe_data';
    }

    protected $fillable = [
        'stripe_id',
        'address',
        'description',
        'email',
        'metadata',
        'name',
        'phone',
    ];

    protected $casts = [
        'address' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the parent stripeable model.
     */
    public function stripeable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @throws ApiErrorException
     */
    public function deleteOnStripe(): Customer
    {
        return Stripe::deleteStripeCustomer($this);
    }

    public function stripePayload(): array
    {
        $payload = $this->attributesToArray();
        $except = [
            'id',
            'stripe_id',
            'stripeable_type',
            'stripeable_id',
            'created_at',
            'updated_at',
        ];

        return Arr::except($payload, $except);
    }
}
