<?php

namespace BitbossHub\Cashier\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class InvoiceData extends CashierModel
{
  protected $table = "invoice_data";

  /**
   * Get the parent invoiceable model.
   */
  public function invoiceable(): MorphTo
  {
    return $this->morphTo();
  }
}
