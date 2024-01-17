<?php

namespace BitbossHub\Cashier\Exceptions\Stripe;

use Exception;

class InvalidStripeCustomer extends Exception
{
    /**
     * Create a new InvalidStripeCustomer instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public static function notYetCreated($owner)
    {
        return new static(class_basename($owner).' is not a Stripe customer yet. See the createAsStripeCustomer method.');
    }
}
