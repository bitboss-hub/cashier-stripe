<?php

namespace BitbossHub\Cashier\Events\StripeData;

use BitbossHub\Cashier\Models\StripeData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StripeDataCreatedEvent
{
  use Dispatchable, SerializesModels;

  public StripeData $stripeData;

  /**
   * Create a new event instance.
   *
   * @param StripeData $stripeData
   * @return void
   */
  public function __construct(StripeData $stripeData)
  {
    $this->stripeData = $stripeData;
  }
}
