<?php

namespace BitbossHub\Cashier\Exceptions;

use BitbossHub\Cashier\Subscription;
use Exception;

class SubscriptionUpdateFailure extends Exception
{
    /**
     * Create a new SubscriptionUpdateFailure instance.
     *
     * @return static
     */
    public static function incompleteSubscription(Subscription $subscription)
    {
        return new static(
            "The subscription \"{$subscription->stripe_id}\" cannot be updated because its payment is incomplete."
        );
    }

    /**
     * Create a new SubscriptionUpdateFailure instance.
     *
     * @param  string  $price
     * @return static
     */
    public static function duplicatePrice(Subscription $subscription, $price)
    {
        return new static(
            "The price \"$price\" is already attached to subscription \"{$subscription->stripe_id}\"."
        );
    }

    /**
     * Create a new SubscriptionUpdateFailure instance.
     *
     * @return static
     */
    public static function cannotDeleteLastPrice(Subscription $subscription)
    {
        return new static(
            "The price on subscription \"{$subscription->stripe_id}\" cannot be removed because it is the last one."
        );
    }
}
