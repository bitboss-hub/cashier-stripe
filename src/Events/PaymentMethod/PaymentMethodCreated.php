<?php

namespace BitbossHub\Cashier\Events\PaymentMethod;

use BitbossHub\Cashier\Models\PaymentMethod;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentMethodCreated
{
    use Dispatchable, SerializesModels;

    public PaymentMethod $paymentMethod;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }
}
