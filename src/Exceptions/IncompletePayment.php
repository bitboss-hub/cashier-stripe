<?php

namespace BitbossHub\Cashier\Exceptions;

use BitbossHub\Cashier\Payment;
use Exception;
use Throwable;

class IncompletePayment extends Exception
{
    /**
     * The Cashier Payment object.
     *
     * @var \BitbossHub\Cashier\Payment
     */
    public $payment;

    /**
     * Create a new IncompletePayment instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @return void
     */
    public function __construct(Payment $payment, $message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->payment = $payment;
    }

    /**
     * Create a new IncompletePayment instance with a `payment_action_required` type.
     *
     * @return static
     */
    public static function paymentMethodRequired(Payment $payment)
    {
        return new static(
            $payment,
            'The payment attempt failed because of an invalid payment method.'
        );
    }

    /**
     * Create a new IncompletePayment instance with a `requires_action` type.
     *
     * @return static
     */
    public static function requiresAction(Payment $payment)
    {
        return new static(
            $payment,
            'The payment attempt failed because additional action is required before it can be completed.'
        );
    }

    /**
     * Create a new IncompletePayment instance with a `requires_confirmation` type.
     *
     * @return static
     */
    public static function requiresConfirmation(Payment $payment)
    {
        return new static(
            $payment,
            'The payment attempt failed because it needs to be confirmed before it can be completed.'
        );
    }
}
