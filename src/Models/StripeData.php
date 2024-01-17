<?php

namespace BitbossHub\Cashier\Models;

use BitbossHub\Cashier\Traits\CrudQuietlyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StripeData extends Model
{
    use CrudQuietlyTrait;

    public function getTable()
    {
        $prefix = config('cashier.database.table_prefix', '');
        return $prefix . 'stripe_data';
    }

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

//    /**
//     * Create the model without firing any model events
//     *
//     * @param array $attributes
//     * @param array $options
//     *
//     * @return mixed
//     */
//    public function createQuietly(array $attributes, array $options = [])
//    {
//        return static::withoutEvents(function () use ($attributes) {
//            return $this->create($attributes);
//        });
//    }
//
//
//    /**
//     * Update the model without firing any model events
//     *
//     * @param array $attributes
//     * @param array $options
//     *
//     * @return mixed
//     */
//    public function updateQuietly(array $attributes = [], array $options = [])
//    {
//        return static::withoutEvents(function () use ($attributes, $options) {
//            return $this->update($attributes, $options);
//        });
//    }
//
//    /**
//     * Delete the model without firing any model events
//     *
//     * @return mixed
//     */
//    public function deleteQuietly()
//    {
//        return static::withoutEvents(function () {
//            return $this->delete();
//        });
//    }
}
