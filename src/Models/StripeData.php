<?php

namespace Laravel\Cashier\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class StripeData extends CashierModel
{
  protected $table = "stripe_data";

  /**
   * Get the parent stripeable model.
   */
  public function stripeable(): MorphTo
  {
    return $this->morphTo();
  }
}
