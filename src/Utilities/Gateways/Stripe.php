<?php

namespace BitbossHub\Cashier\Utilities\Gateways;

use Stripe\BaseStripeClient;
use Stripe\Collection;
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
  public static function stripe(array $options = []): StripeClient
  {
    return new StripeClient(array_merge([
      'api_key' => $options['api_key'] ?? config('cashier.secret'),
      'stripe_version' => static::STRIPE_VERSION,
      'api_base' => static::$apiBaseUrl,
    ], $options));
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
}
