<?php

namespace Emmanuelsiziba\SmilePay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Emmanuelsiziba\SmilePay\Services\StandardCheckout standardCheckout()
 * @method static \Emmanuelsiziba\SmilePay\Services\ExpressCheckout expressCheckout()
 * @method static \Emmanuelsiziba\SmilePay\Services\PaymentUtility utility()
 * @method static \Emmanuelsiziba\SmilePay\Client\SmilePayClient client()
 *
 * @see \Emmanuelsiziba\SmilePay\SmilePay
 */
class SmilePay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'smilepay';
    }
}
