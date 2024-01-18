<?php

namespace BitbossHub\Cashier\Observers;
use BitBoss\Scrooge\Utilities\Gateways\Stripe;
use BitbossHub\Cashier\Models\StripeData;
class StripeDataObserver
{
  public function created(StripeData $stripeData)
  {
    //
  }

  public function updated(StripeData $stripeData)
  {
    //
  }

  public function deleted(StripeData $stripeData)
  {
    Stripe::deleteStripeCustomer($stripeData);
  }
}
