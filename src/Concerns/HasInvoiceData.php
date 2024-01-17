<?php

namespace BitbossHub\Cashier\Concerns;

use BitbossHub\Cashier\Models\InvoiceData;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasInvoiceData
{
  /**
   * Get the model's invoiceData.
   */
  public function invoiceData(): MorphOne
  {
    return $this->morphOne(InvoiceData::class, 'invoiceable');
  }
}
