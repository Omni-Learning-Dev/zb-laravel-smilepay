<?php

namespace Emmanuelsiziba\SmilePay;

use Emmanuelsiziba\SmilePay\Client\SmilePayClient;
use Emmanuelsiziba\SmilePay\Services\StandardCheckout;
use Emmanuelsiziba\SmilePay\Services\ExpressCheckout;
use Emmanuelsiziba\SmilePay\Services\PaymentUtility;

class SmilePay
{
    protected SmilePayClient $client;
    protected StandardCheckout $standardCheckout;
    protected ExpressCheckout $expressCheckout;
    protected PaymentUtility $utility;

    public function __construct(SmilePayClient $client)
    {
        $this->client = $client;
        $this->standardCheckout = new StandardCheckout($client);
        $this->expressCheckout = new ExpressCheckout($client);
        $this->utility = new PaymentUtility($client);
    }

    /**
     * Get Standard Checkout service
     *
     * @return StandardCheckout
     */
    public function standardCheckout(): StandardCheckout
    {
        return $this->standardCheckout;
    }

    /**
     * Get Express Checkout service
     *
     * @return ExpressCheckout
     */
    public function expressCheckout(): ExpressCheckout
    {
        return $this->expressCheckout;
    }

    /**
     * Get Payment Utility service
     *
     * @return PaymentUtility
     */
    public function utility(): PaymentUtility
    {
        return $this->utility;
    }

    /**
     * Get the SmilePay client
     *
     * @return SmilePayClient
     */
    public function client(): SmilePayClient
    {
        return $this->client;
    }
}
