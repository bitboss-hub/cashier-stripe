<?php

namespace BitbossHub\Cashier\Exceptions;

use Exception;
use Stripe\Invoice as StripeInvoice;

class InvalidInvoice extends Exception
{
    /**
     * Create a new InvalidInvoice instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public static function invalidOwner(StripeInvoice $invoice, $owner)
    {
        return new static("The invoice `{$invoice->id}` does not belong to this customer `$owner->stripe_id`.");
    }
}
