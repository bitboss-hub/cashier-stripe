<?php

namespace Laravel\Cashier\Models;

use Illuminate\Database\Eloquent\Model;

class CashierModel extends Model
{
  public function getTable()
  {
    $prefix = config('cashier.database.table_prefix', '');
    return $prefix . parent::getTable();
  }
}
