<?php

namespace BitbossHub\Cashier\Observers;

use BitbossHub\Cashier\Events\StripeData\StripeDataCreated;
use BitbossHub\Cashier\Events\StripeData\StripeDataDeleted;
use BitbossHub\Cashier\Events\StripeData\StripeDataUpdated;
use BitbossHub\Cashier\Models\StripeData;

class StripeDataObserver
{
    public function created(StripeData $stripeData)
    {
        event(new StripeDataCreated($stripeData));
    }

    public function updated(StripeData $stripeData)
    {
        if (config('cashier.observers')) {
            $stripeData->stripeable?->updateStripeCustomer($stripeData->stripePayload());
        }
        event(new StripeDataUpdated($stripeData));
    }

    public function deleted(StripeData $stripeData)
    {
        if (config('cashier.observers')) {
            $stripeData->deleteOnStripe();
        }
        event(new StripeDataDeleted($stripeData));
    }
}
