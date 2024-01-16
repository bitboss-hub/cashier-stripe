<?php

namespace Laravel\Cashier\Concerns;

use Laravel\Cashier\Models\StripeData;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasStripeData
{
  /**
   * Get the model's Stripe Data.
   */
  public function stripeData(): MorphOne
  {
    return $this->morphOne(StripeData::class, 'stripeable');
  }

  public function saveOnStripe(array $data): self
  {

    return $this;
  }

  /**
   * Retrieve the Stripe customer ID.
   *
   * @return string|null
   */
  public function stripeId(): string|null
  {
    return $this->stripeData?->stripe_id;
  }

  /**
   * Determine if the customer has a Stripe customer ID.
   *
   * @return bool
   */
  public function hasStripeId(): bool
  {
    return ! is_null($this->stripeData?->stripe_id);
  }
}
