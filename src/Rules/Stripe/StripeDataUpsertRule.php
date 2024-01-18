<?php

namespace BitbossHub\Cashier\Rules\Stripe;

class StripeDataUpsertRule
{
    public static function rule(): array
    {
        return [
            'stripe_id' => 'required|string|max:255',
            'address' => 'array|nullable',
            'address.city' => 'string|nullable|max:255',
            'address.country' => 'string|nullable|max:2',
            'address.line1' => 'string|nullable|max:255',
            'address.line2' => 'string|nullable|max:255',
            'address.postal_code' => 'string|nullable|max:255',
            'address.state' => 'string|nullable|max:255',
            'description' => 'string|nullable|max:255',
            'email' => 'email|nullable|max:255',
            'metadata' => 'array|nullable',
            'name' => 'string|nullable|max:255',
            'phone' => 'string|nullable|max:255',
        ];
    }
}
