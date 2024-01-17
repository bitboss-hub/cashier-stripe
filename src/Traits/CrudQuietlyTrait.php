<?php

namespace BitbossHub\Cashier\Traits;

trait CrudQuietlyTrait
{
    /**
     * Create the model without firing any model events
     *
     * @param array $attributes
     * @param array $options
     *
     * @return mixed
     */
    public function createQuietly(array $attributes, array $options = [])
    {
        return static::withoutEvents(function () use ($attributes) {
            return $this->create($attributes);
        });
    }

    /**
     * Update the model without firing any model events
     *
     * @param array $attributes
     * @param array $options
     *
     * @return mixed
     */
    public function updateQuietly(array $attributes = [], array $options = [])
    {
        return static::withoutEvents(function () use ($attributes, $options) {
            return $this->update($attributes, $options);
        });
    }

    /**
     * Delete the model without firing any model events
     *
     * @return mixed
     */
    public function deleteQuietly()
    {
        return static::withoutEvents(function () {
            return $this->delete();
        });
    }
}
