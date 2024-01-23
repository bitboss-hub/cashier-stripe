<?php

namespace BitbossHub\Cashier\Concerns;

use BitbossHub\Cashier\Models\PaymentMethod;
use BitbossHub\Cashier\StripePaymentMethod;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Stripe\BankAccount as StripeBankAccount;
use Stripe\Card as StripeCard;

trait ManagesPaymentMethods
{
    /**
     * Get the model's Stripe Data.
     */
    public function paymentMethods(): MorphToMany
    {
        return $this->morphToMany(PaymentMethod::class, 'stripeable');
    }

    public function createLocalPaymentMethod(array $data): PaymentMethod
    {
        $paymentMethod = new PaymentMethod($data);
        $cus = $data['customer'];
        //        if (array_key_exists('customer', $data)->hasStripeId()) {
        //            $paymentMethod->customer_id = $this->stripeData->stripe_id;
        //        }
        $this->paymentMethods()->save($paymentMethod);

        return $paymentMethod;
    }

    public function updateStripePaymentMethod(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $paymentMethod->update($data);

        return $paymentMethod;
    }

    public function createStripePaymentMethod(array $options): PaymentMethod
    {

    }

    /**
     * Create a new SetupIntent instance.
     *
     * @return \Stripe\SetupIntent
     */
    public function createSetupIntent(array $options = [])
    {
        if ($this->hasStripeId()) {
            $options['customer'] = $this->stripeData->stripe_id;
        }

        return static::stripe()->setupIntents->create($options);
    }

    /**
     * Retrieve a SetupIntent from Stripe.
     *
     * @return \Stripe\SetupIntent
     */
    public function findSetupIntent(string $id, array $params = [], array $options = [])
    {
        return static::stripe()->setupIntents->retrieve($id, $params, $options);
    }

    /**
     * Determines if the customer currently has a default payment method.
     *
     * @return bool
     */
    public function hasDefaultPaymentMethod()
    {
        return (bool) $this->paymentMethods()->default()->count() > 1;
    }

    /**
     * Determines if the customer currently has at least one payment method of an optional type.
     *
     * @param  string|null  $type
     * @return bool
     */
    public function hasPaymentMethod($type = null)
    {
        return $this->stripePaymentMethods($type)->isNotEmpty();
    }

    /**
     * Get a collection of the customer's payment methods of an optional type.
     *
     * @param  string|null  $type
     * @param  array  $parameters
     * @return \Illuminate\Support\Collection|\BitbossHub\Cashier\StripePaymentMethod[]
     */
    public function stripePaymentMethods($type = null, $parameters = [])
    {
        if (! $this->hasStripeId()) {
            return new Collection();
        }

        $parameters = array_merge(['limit' => 24], $parameters);

        // "type" is temporarily required by Stripe...
        $paymentMethods = static::stripe()->paymentMethods->all(
            array_filter(['customer' => $this->stripeData?->stripe_id, 'type' => $type]) + $parameters
        );

        return Collection::make($paymentMethods->data)->map(function ($paymentMethod) {
            return new StripePaymentMethod($this, $paymentMethod);
        });
    }

    /**
     * Add a payment method to the customer.
     *
     * @param  \Stripe\PaymentMethod|string  $paymentMethod
     * @return \BitbossHub\Cashier\StripePaymentMethod
     */
    public function addPaymentMethod($paymentMethod)
    {
        $this->assertCustomerExists();

        $stripePaymentMethod = $this->resolveStripePaymentMethod($paymentMethod);

        if ($stripePaymentMethod->customer !== $this->stripe_id) {
            $stripePaymentMethod = $stripePaymentMethod->attach(
                ['customer' => $this->stripeData?->stripe_id]
            );
        }

        return new StripePaymentMethod($this, $stripePaymentMethod);
    }

    /**
     * Delete a payment method from the customer.
     *
     * @param  \Stripe\PaymentMethod|string  $paymentMethod
     * @return void
     */
    public function deletePaymentMethod($paymentMethod)
    {
        $this->assertCustomerExists();

        $stripePaymentMethod = $this->resolveStripePaymentMethod($paymentMethod);

        if ($stripePaymentMethod->customer !== $this->stripe_id) {
            return;
        }

        $customer = $this->asStripeCustomer();

        $defaultPaymentMethod = $customer->invoice_settings->default_payment_method;

        $stripePaymentMethod->detach();

        // If the payment method was the default payment method, we'll remove it manually...
        if ($stripePaymentMethod->id === $defaultPaymentMethod) {
            $this->forceFill([
                'pm_type' => null,
                'pm_last_four' => null,
            ])->save();
        }
    }

    /**
     * Get the default payment method for the customer.
     *
     * @return \BitbossHub\Cashier\StripePaymentMethod|\Stripe\Card|\Stripe\BankAccount|null
     */
    public function defaultPaymentMethod()
    {
        if (! $this->hasStripeId()) {
            return;
        }

        /** @var \Stripe\Customer */
        $customer = $this->asStripeCustomer(['default_source', 'invoice_settings.default_payment_method']);

        if ($customer->invoice_settings->default_payment_method) {
            return new StripePaymentMethod($this, $customer->invoice_settings->default_payment_method);
        }

        // If we can't find a payment method, try to return a legacy source...
        return $customer->default_source;
    }

    /**
     * Update customer's default payment method.
     *
     * @param  \Stripe\PaymentMethod|string  $paymentMethod
     * @return \BitbossHub\Cashier\StripePaymentMethod
     */
    public function updateDefaultPaymentMethod($paymentMethod)
    {
        $this->assertCustomerExists();

        $customer = $this->asStripeCustomer();

        $stripePaymentMethod = $this->resolveStripePaymentMethod($paymentMethod);

        // If the customer already has the payment method as their default, we can bail out
        // of the call now. We don't need to keep adding the same payment method to this
        // model's account every single time we go through this specific process call.
        if ($stripePaymentMethod->id === $customer->invoice_settings->default_payment_method) {
            return;
        }

        $paymentMethod = $this->addPaymentMethod($stripePaymentMethod);

        $this->updateStripeCustomer([
            'invoice_settings' => ['default_payment_method' => $paymentMethod->id],
        ]);

        // Next we will get the default payment method for this user so we can update the
        // payment method details on the record in the database. This will allow us to
        // show that information on the front-end when updating the payment methods.
        $this->fillPaymentMethodDetails($paymentMethod);

        $this->save();

        return $paymentMethod;
    }

    /**
     * Synchronises the customer's default payment method from Stripe back into the database.
     *
     * @return $this
     */
    public function updateDefaultPaymentMethodFromStripe()
    {
        $defaultPaymentMethod = $this->defaultPaymentMethod();

        if ($defaultPaymentMethod) {
            if ($defaultPaymentMethod instanceof StripePaymentMethod) {
                $this->fillPaymentMethodDetails(
                    $defaultPaymentMethod->asStripePaymentMethod()
                )->save();
            } else {
                $this->fillSourceDetails($defaultPaymentMethod)->save();
            }
        } else {
            $this->forceFill([
                'pm_type' => null,
                'pm_last_four' => null,
            ])->save();
        }

        return $this;
    }

    /**
     * Fills the model's properties with the payment method from Stripe.
     *
     * @param  \BitbossHub\Cashier\StripePaymentMethod|\Stripe\PaymentMethod|null  $paymentMethod
     * @return $this
     */
    protected function fillPaymentMethodDetails($paymentMethod)
    {
        if ($paymentMethod->type === 'card') {
            $this->pm_type = $paymentMethod->card->brand;
            $this->pm_last_four = $paymentMethod->card->last4;
        } else {
            $this->pm_type = $type = $paymentMethod->type;
            $this->pm_last_four = $paymentMethod?->$type->last4 ?? null;
        }

        return $this;
    }

    /**
     * Fills the model's properties with the source from Stripe.
     *
     * @param  \Stripe\Card|\Stripe\BankAccount|null  $source
     * @return $this
     *
     * @deprecated Will be removed in a future Cashier update. You should use the new payment methods API instead.
     */
    protected function fillSourceDetails($source)
    {
        if ($source instanceof StripeCard) {
            $this->pm_type = $source->brand;
            $this->pm_last_four = $source->last4;
        } elseif ($source instanceof StripeBankAccount) {
            $this->pm_type = 'Bank Account';
            $this->pm_last_four = $source->last4;
        }

        return $this;
    }

    /**
     * Deletes the customer's payment methods of the given type.
     *
     * @param  string|null  $type
     * @return void
     */
    public function deletePaymentMethods($type = null)
    {
        $this->paymentMethods($type)->each(function (StripePaymentMethod $paymentMethod) {
            $paymentMethod->delete();
        });

        $this->updateDefaultPaymentMethodFromStripe();
    }

    /**
     * Find a StripePaymentMethod by ID.
     *
     * @param  string  $paymentMethod
     * @return \BitbossHub\Cashier\StripePaymentMethod|null
     */
    public function findPaymentMethod($paymentMethod)
    {
        $stripePaymentMethod = null;

        try {
            $stripePaymentMethod = $this->resolveStripePaymentMethod($paymentMethod);
        } catch (Exception $exception) {
            //
        }

        return $stripePaymentMethod ? new StripePaymentMethod($this, $stripePaymentMethod) : null;
    }

    /**
     * Resolve a StripePaymentMethod ID to a Stripe StripePaymentMethod object.
     *
     * @param  \Stripe\PaymentMethod|string  $paymentMethod
     * @return \Stripe\PaymentMethod
     */
    protected function resolveStripePaymentMethod($paymentMethod)
    {
        if ($paymentMethod instanceof StripePaymentMethod) {
            return $paymentMethod;
        }

        return static::stripe()->paymentMethods->retrieve($paymentMethod);
    }
}
