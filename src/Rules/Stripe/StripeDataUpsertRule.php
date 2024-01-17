<?php

namespace BitbossHub\Cashier\Rules\Stripe;

class StripeDataUpsertRule
{
    public static function rule(): array
    {
        return [
            'stripe_id' => 'required|string',
            'address' => 'array|nullable',
            'address.city' => 'string|nullable',
            'address.country' => 'string|nullable',
            'address.line1' => 'string|nullable',
            'address.line2' => 'string|nullable',
            'address.postal_code' => 'string|nullable',
            'address.state' => 'string|nullable',
            'description' => 'string|nullable',
            'email' => 'email|nullable',
            'metadata' => 'array|nullable',
            'name' => 'string|nullable',
            'phone' => 'string|nullable'
        ];
    }
}
