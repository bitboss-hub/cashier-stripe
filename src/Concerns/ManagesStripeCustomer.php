<?php

namespace BitbossHub\Cashier\Concerns;

use BitbossHub\Cashier\Cashier;
use BitbossHub\Cashier\CustomerBalanceTransaction;
use BitbossHub\Cashier\Discount;
use BitbossHub\Cashier\Exceptions\Stripe\InvalidStripeCustomer;
use BitbossHub\Cashier\Exceptions\Stripe\InvalidStripeData;
use BitbossHub\Cashier\Exceptions\Stripe\StripeCustomerAlreadyCreated;
use BitbossHub\Cashier\Models\StripeData;
use BitbossHub\Cashier\PromotionCode;
use BitbossHub\Cashier\Rules\Stripe\StripeDataUpsertRule;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Stripe\Customer as StripeCustomer;
use Stripe\Exception\InvalidRequestException as StripeInvalidRequestException;

trait ManagesStripeCustomer
{
    /**
     * Get the model's Stripe Data.
     */
    public function stripeData(): MorphOne
    {
        return $this->morphOne(StripeData::class, 'stripeable');
    }

    /**
     * Creates stripe data
     *
     * @param  StripeCustomer|string  $customer
     */
    public function createLocalStripeData($customer, array $options = []): StripeData
    {
        $customer_id = $customer instanceof StripeCustomer ? $customer->id : $customer;
        $attributes = array_merge($options, ['stripe_id' => $customer_id]);
        $validator = Validator::make($attributes, StripeDataUpsertRule::rule());
        if ($validator->fails()) {
            throw InvalidStripeData::message($validator->errors()->getMessages());
        }
        $stripeData = new StripeData($attributes);
        $this->stripeData()->save($stripeData);

        return $stripeData;
    }

    /**
     * Retrieve the Stripe customer ID.
     *
     * @return string|null
     */
    public function stripeId()
    {
        return $this->stripeData?->stripe_id;
    }

    /**
     * Determine if the customer has a Stripe customer ID.
     *
     * @return bool
     */
    public function hasStripeId()
    {
        return ! is_null($this->stripeData?->stripe_id);
    }

    /**
     * Determine if the customer has a Stripe customer ID and throw an exception if not.
     *
     * @return void
     *
     * @throws \BitbossHub\Cashier\Exceptions\Stripe\InvalidStripeCustomer
     */
    protected function assertCustomerExists()
    {
        if (! $this->hasStripeId()) {
            throw InvalidStripeCustomer::notYetCreated($this);
        }
    }

    /**
     * Create a Stripe customer for the given model.
     *
     * @return \Stripe\Customer
     *
     * @throws \BitbossHub\Cashier\Exceptions\StripeCustomerAlreadyCreated
     */
    public function createAsStripeCustomer(array $options = [])
    {
        if ($this->hasStripeId()) {
            throw StripeCustomerAlreadyCreated::exists($this);
        }

        if (! array_key_exists('name', $options) && $name = $this->stripeName()) {
            $options['name'] = $name;
        }

        if (! array_key_exists('email', $options) && $email = $this->stripeEmail()) {
            $options['email'] = $email;
        }

        if (! array_key_exists('phone', $options) && $phone = $this->stripePhone()) {
            $options['phone'] = $phone;
        }

        if (! array_key_exists('description', $options) && $description = $this->stripeDescription()) {
            $options['description'] = $description;
        }

        if (! array_key_exists('address', $options) && $address = $this->stripeAddress()) {
            $options['address'] = $address;
        }

        if (! array_key_exists('preferred_locales', $options) && $locales = $this->stripePreferredLocales()) {
            $options['preferred_locales'] = $locales;
        }

        $modelMetaData = [
            'model_type' => get_class($this),
            'model_id' => $this->id,
        ];

        if (! array_key_exists('metadata', $options) && $metadata = $this->stripeMetadata()) {
            $options['metadata'] = array_merge($metadata, $modelMetaData);
        } else {
            $options['metadata'] = array_merge($options['metadata'], $modelMetaData);
        }

        // Here we will create the customer instance on Stripe and store the ID of the
        // user from Stripe. This ID will correspond with the Stripe user instances
        // and allow us to retrieve users from Stripe later when we need to work.
        $customer = static::stripe()->customers->create($options);
        $this->createLocalStripeData($customer, $options);

        return $customer;
    }

    /**
     * Update the underlying Stripe customer information for the model.
     *
     * @return \Stripe\Customer
     */
    public function updateStripeCustomer(array $options = [])
    {
        return static::stripe()->customers->update(
            $this->stripeData?->stripe_id, $options
        );
    }

    /**
     * Get the Stripe customer instance for the current user or create one.
     *
     * @return \Stripe\Customer
     */
    public function createOrGetStripeCustomer(array $options = [])
    {
        if ($this->hasStripeId()) {
            return $this->asStripeCustomer($options['expand'] ?? []);
        }

        return $this->createAsStripeCustomer($options);
    }

    /**
     * Get the Stripe customer for the model.
     *
     * @return \Stripe\Customer
     */
    public function asStripeCustomer(array $expand = [])
    {
        $this->assertCustomerExists();

        return static::stripe()->customers->retrieve(
            $this->stripeData?->stripe_id, ['expand' => $expand]
        );
    }

    /**
     * Get the name that should be synced to Stripe.
     *
     * @return string|null
     */
    public function stripeName()
    {
        return $this->stripeData?->name ?? null;
    }

    /**
     * Get the email address that should be synced to Stripe.
     *
     * @return string|null
     */
    public function stripeEmail()
    {
        return $this->stripeData?->email ?? null;
    }

    /**
     * Get the phone number that should be synced to Stripe.
     *
     * @return string|null
     */
    public function stripePhone()
    {
        return $this->stripeData?->phone ?? null;
    }

    /**
     * Get the description that should be synced to Stripe.
     *
     * @return string|null
     */
    public function stripeDescription()
    {
        return $this->stripeData?->description ?? null;
    }

    /**
     * Get the address that should be synced to Stripe.
     *
     * @return array|null
     */
    public function stripeAddress()
    {
        return $this->stripeData?->address;
    }

    /**
     * Get the locales that should be synced to Stripe.
     *
     * @return array|null
     */
    public function stripePreferredLocales()
    {
        return [];

        // return ['en'];
    }

    /**
     * Get the metadata that should be synced to Stripe.
     *
     * @return array|null
     */
    public function stripeMetadata()
    {
        return $this->stripeData?->metadata;
    }

    /**
     * Sync the customer's information to Stripe.
     *
     * @return \Stripe\Customer
     */
    public function syncStripeCustomerDetails()
    {
        return $this->updateStripeCustomer([
            'name' => $this->stripeName(),
            'email' => $this->stripeEmail(),
            'phone' => $this->stripePhone(),
            'address' => $this->stripeAddress(),
            'preferred_locales' => $this->stripePreferredLocales(),
            'metadata' => $this->stripeMetadata(),
            'description' => $this->stripeDescription(),
        ]);
    }

    /**
     * Sync the local customer's information from Stripe.
     */
    public function syncLocalStripeCustomerDetails(array $attributes): StripeData
    {
        $stripeData = $this->stripeData;
        $stripeData->updateQuietly($attributes);

        return $stripeData;
    }

    /**
     * The discount that applies to the customer, if applicable.
     *
     * @return \BitbossHub\Cashier\Discount|null
     */
    public function discount()
    {
        $customer = $this->asStripeCustomer(['discount.promotion_code']);

        return $customer->discount
            ? new Discount($customer->discount)
            : null;
    }

    /**
     * Apply a coupon to the customer.
     *
     * @param  string  $coupon
     * @return void
     */
    public function applyCoupon($coupon)
    {
        $this->assertCustomerExists();

        $this->updateStripeCustomer([
            'coupon' => $coupon,
        ]);
    }

    /**
     * Apply a promotion code to the customer.
     *
     * @param  string  $promotionCodeId
     * @return void
     */
    public function applyPromotionCode($promotionCodeId)
    {
        $this->assertCustomerExists();

        $this->updateStripeCustomer([
            'promotion_code' => $promotionCodeId,
        ]);
    }

    /**
     * Retrieve a promotion code by its code.
     *
     * @param  string  $code
     * @return \BitbossHub\Cashier\PromotionCode|null
     */
    public function findPromotionCode($code, array $options = [])
    {
        $codes = static::stripe()->promotionCodes->all(array_merge([
            'code' => $code,
            'limit' => 1,
        ], $options));

        if ($codes && $promotionCode = $codes->first()) {
            return new PromotionCode($promotionCode);
        }
    }

    /**
     * Retrieve a promotion code by its code.
     *
     * @param  string  $code
     * @return \BitbossHub\Cashier\PromotionCode|null
     */
    public function findActivePromotionCode($code, array $options = [])
    {
        return $this->findPromotionCode($code, array_merge($options, ['active' => true]));
    }

    /**
     * Get the total balance of the customer.
     *
     * @return string
     */
    public function balance()
    {
        return $this->formatAmount($this->rawBalance());
    }

    /**
     * Get the raw total balance of the customer.
     *
     * @return int
     */
    public function rawBalance()
    {
        if (! $this->hasStripeId()) {
            return 0;
        }

        return $this->asStripeCustomer()->balance;
    }

    /**
     * Return a customer's balance transactions.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function balanceTransactions($limit = 10, array $options = [])
    {
        if (! $this->hasStripeId()) {
            return new Collection();
        }

        $transactions = static::stripe()
            ->customers
            ->allBalanceTransactions($this->stripe_id, array_merge(['limit' => $limit], $options));

        return Collection::make($transactions->data)->map(function ($transaction) {
            return new CustomerBalanceTransaction($this, $transaction);
        });
    }

    /**
     * Credit a customer's balance.
     *
     * @param  int  $amount
     * @param  string|null  $description
     * @return \BitbossHub\Cashier\CustomerBalanceTransaction
     */
    public function creditBalance($amount, $description = null, array $options = [])
    {
        return $this->applyBalance(-$amount, $description, $options);
    }

    /**
     * Debit a customer's balance.
     *
     * @param  int  $amount
     * @param  string|null  $description
     * @return \BitbossHub\Cashier\CustomerBalanceTransaction
     */
    public function debitBalance($amount, $description = null, array $options = [])
    {
        return $this->applyBalance($amount, $description, $options);
    }

    /**
     * Apply a new amount to the customer's balance.
     *
     * @param  int  $amount
     * @param  string|null  $description
     * @return \BitbossHub\Cashier\CustomerBalanceTransaction
     */
    public function applyBalance($amount, $description = null, array $options = [])
    {
        $this->assertCustomerExists();

        $transaction = static::stripe()
            ->customers
            ->createBalanceTransaction($this->stripe_id, array_filter(array_merge([
                'amount' => $amount,
                'currency' => $this->preferredCurrency(),
                'description' => $description,
            ], $options)));

        return new CustomerBalanceTransaction($this, $transaction);
    }

    /**
     * Get the Stripe supported currency used by the customer.
     *
     * @return string
     */
    public function preferredCurrency()
    {
        return config('cashier.currency');
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @return string
     */
    protected function formatAmount($amount)
    {
        return Cashier::formatAmount($amount, $this->preferredCurrency());
    }

    /**
     * Get the Stripe billing portal for this customer.
     *
     * @param  string|null  $returnUrl
     * @return string
     */
    public function billingPortalUrl($returnUrl = null, array $options = [])
    {
        $this->assertCustomerExists();

        return static::stripe()->billingPortal->sessions->create(array_merge([
            'customer' => $this->stripeId(),
            'return_url' => $returnUrl ?? route('home'),
        ], $options))['url'];
    }

    /**
     * Generate a redirect response to the customer's Stripe billing portal.
     *
     * @param  string|null  $returnUrl
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToBillingPortal($returnUrl = null, array $options = [])
    {
        return new RedirectResponse(
            $this->billingPortalUrl($returnUrl, $options)
        );
    }

    /**
     * Get a collection of the customer's TaxID's.
     *
     * @return \Illuminate\Support\Collection|\Stripe\TaxId[]
     */
    public function taxIds(array $options = [])
    {
        $this->assertCustomerExists();

        return new Collection(
            static::stripe()->customers->allTaxIds($this->stripe_id, $options)->data
        );
    }

    /**
     * Find a TaxID by ID.
     *
     * @return \Stripe\TaxId|null
     */
    public function findTaxId($id)
    {
        $this->assertCustomerExists();

        try {
            return static::stripe()->customers->retrieveTaxId(
                $this->stripe_id, $id, []
            );
        } catch (StripeInvalidRequestException $exception) {
            //
        }
    }

    /**
     * Create a TaxID for the customer.
     *
     * @param  string  $type
     * @param  string  $value
     * @return \Stripe\TaxId
     */
    public function createTaxId($type, $value)
    {
        $this->assertCustomerExists();

        return static::stripe()->customers->createTaxId($this->stripe_id, [
            'type' => $type,
            'value' => $value,
        ]);
    }

    /**
     * Delete a TaxID for the customer.
     *
     * @param  string  $id
     * @return void
     */
    public function deleteTaxId($id)
    {
        $this->assertCustomerExists();

        try {
            static::stripe()->customers->deleteTaxId($this->stripe_id, $id);
        } catch (StripeInvalidRequestException $exception) {
            //
        }
    }

    /**
     * Determine if the customer is not exempted from taxes.
     *
     * @return bool
     */
    public function isNotTaxExempt()
    {
        return $this->asStripeCustomer()->tax_exempt === StripeCustomer::TAX_EXEMPT_NONE;
    }

    /**
     * Determine if the customer is exempted from taxes.
     *
     * @return bool
     */
    public function isTaxExempt()
    {
        return $this->asStripeCustomer()->tax_exempt === StripeCustomer::TAX_EXEMPT_EXEMPT;
    }

    /**
     * Determine if reverse charge applies to the customer.
     *
     * @return bool
     */
    public function reverseChargeApplies()
    {
        return $this->asStripeCustomer()->tax_exempt === StripeCustomer::TAX_EXEMPT_REVERSE;
    }

    /**
     * Get the Stripe SDK client.
     *
     * @return \Stripe\StripeClient
     */
    public static function stripe(array $options = [])
    {
        return Cashier::stripe($options);
    }
}