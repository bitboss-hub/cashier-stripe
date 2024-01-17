<?php

namespace BitbossHub\Cashier\Exceptions\Stripe;

use Exception;

class InvalidStripeData extends Exception
{
    public static function message(array $messages): self
    {
        $bag = json_encode($messages);
        return new static("Invalid stripe data: {$bag}");
    }
}
