<?php

namespace BitbossHub\Cashier\Utilities\Gateways;

use BitbossHub\Cashier\Models\StripeData;
use Stripe\BaseStripeClient;
use Stripe\Collection;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class Stripe
{
  public static $apiBaseUrl = BaseStripeClient::DEFAULT_API_BASE;

  /**
   * The Stripe API version.
   *
   * @var string
   */
  const STRIPE_VERSION = '2022-11-15';

  /**
   * Get the Stripe SDK client.
   *
   * @param  array  $options
   * @return \Stripe\StripeClient
   */
  public static function stripe(array $options = [])
  {
    $config = array_merge([
      'api_key' => $options['api_key'] ?? config('cashier.secret'),
      'stripe_version' => static::STRIPE_VERSION,
      'api_base' => static::$apiBaseUrl,
    ], $options);

    return app(StripeClient::class, ['config' => $config]);
  }

  /**
   * Returns a list of your customers. The customers are returned sorted by creation
   * date, with the most recent customers appearing first.
   *
   * @param null|array $params
   * @param null|array|\Stripe\Util\RequestOptions $opts
   * @throws \Stripe\Exception\ApiErrorException if the request fails
   * @return \Stripe\Collection<\Stripe\Customer>
   */
  public static function customers($params = null, $opts = null): Collection
  {
    $client = self::stripe();
    return $client->customers->all($params, $opts);
  }

  /**
   * Deletes remote stripe customer
   * @param StripeData $stripeData
   * @return Customer
   * @throws ApiErrorException
   */
  public static function deleteStripeCustomer(StripeData $stripeData): Customer
  {
    $client = self::stripe();
    return $client->customers->delete($stripeData->stripe_id);
  }
}
