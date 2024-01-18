<?php

namespace BitbossHub\Cashier;

use Psr\Log\LoggerInterface;
use Stripe\Util\LoggerInterface as StripeLogger;

class Logger implements StripeLogger
{
    /**
     * The Logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create a new Logger instance.
     *
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = [])
    {
        $this->logger->error($message, $context);
    }
}
