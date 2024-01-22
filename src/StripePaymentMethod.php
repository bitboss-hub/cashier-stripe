<?php

namespace BitbossHub\Cashier;

use BitbossHub\Cashier\Exceptions\InvalidPaymentMethod;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use LogicException;
use Stripe\PaymentMethod as StripePackagePaymentMethod;

class StripePaymentMethod implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * The Stripe model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The Stripe StripePaymentMethod instance.
     *
     * @var \Stripe\PaymentMethod
     */
    protected $paymentMethod;

    /**
     * Create a new StripePaymentMethod instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return void
     *
     * @throws \BitbossHub\Cashier\Exceptions\InvalidPaymentMethod
     */
    public function __construct($owner, StripePackagePaymentMethod $paymentMethod)
    {
        if (is_null($paymentMethod->customer)) {
            throw new LogicException('The payment method is not attached to a customer.');
        }

        if ($owner->stripe_id !== $paymentMethod->customer) {
            throw InvalidPaymentMethod::invalidOwner($paymentMethod, $owner);
        }

        $this->owner = $owner;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Delete the payment method.
     *
     * @return void
     */
    public function delete()
    {
        $this->owner->deletePaymentMethod($this->paymentMethod);
    }

    /**
     * Get the Stripe model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function owner()
    {
        return $this->owner;
    }

    /**
     * Get the Stripe StripePaymentMethod instance.
     *
     * @return \Stripe\PaymentMethod
     */
    public function asStripePaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->asStripePaymentMethod()->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Dynamically get values from the Stripe object.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->paymentMethod->{$key};
    }
}
