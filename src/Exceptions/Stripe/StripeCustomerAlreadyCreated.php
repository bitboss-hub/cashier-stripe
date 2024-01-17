<?php

namespace BitbossHub\Cashier\Exceptions\Stripe;

use Exception;

class StripeCustomerAlreadyCreated extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public static function exists($owner)
    {
        return new static(class_basename($owner)." is already a Stripe customer with ID {$owner->stripeData?->stripe_id}.");
    }
}
