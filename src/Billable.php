<?php

namespace BitbossHub\Cashier;

use BitbossHub\Cashier\Concerns\HandlesTaxes;
use BitbossHub\Cashier\Concerns\HasInvoiceData;
use BitbossHub\Cashier\Concerns\ManagesStripeCustomer;
use BitbossHub\Cashier\Concerns\ManagesInvoices;
use BitbossHub\Cashier\Concerns\ManagesPaymentMethods;
use BitbossHub\Cashier\Concerns\ManagesSubscriptions;
use BitbossHub\Cashier\Concerns\PerformsCharges;

trait Billable
{
    use HasInvoiceData;
//    use HandlesTaxes;
    use ManagesStripeCustomer;
//    use ManagesInvoices;
//    use ManagesPaymentMethods;
//    use ManagesSubscriptions;
//    use PerformsCharges;
}
