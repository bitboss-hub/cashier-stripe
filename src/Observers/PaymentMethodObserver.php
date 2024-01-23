<?php

namespace BitbossHub\Cashier\Observers;

use BitbossHub\Cashier\Events\PaymentMethod\PaymentMethodCreated;
use BitbossHub\Cashier\Events\PaymentMethod\PaymentMethodDeleted;
use BitbossHub\Cashier\Events\PaymentMethod\PaymentMethodUpdated;
use BitbossHub\Cashier\Models\PaymentMethod;

class PaymentMethodObserver
{
    public function created(PaymentMethod $paymentMethod)
    {
        event(new PaymentMethodCreated($paymentMethod));
    }

    public function updated(PaymentMethod $paymentMethod)
    {
        if (config('cashier.observers')) {
            //            $paymentMethod->stripeable?->updateStripeCustomer($paymentMethod->stripePayload());
        }
        event(new PaymentMethodUpdated($paymentMethod));
    }

    public function deleted(PaymentMethod $paymentMethod)
    {
        if (config('cashier.observers')) {
            //            $paymentMethod->deleteOnStripe();
        }
        event(new PaymentMethodDeleted($paymentMethod));
    }
}
