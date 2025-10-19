<?php

namespace YourVendor\SmilePay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \YourVendor\SmilePay\Services\StandardCheckout standardCheckout()
 * @method static \YourVendor\SmilePay\Services\ExpressCheckout expressCheckout()
 * @method static \YourVendor\SmilePay\Services\PaymentUtility utility()
 * @method static \YourVendor\SmilePay\Client\SmilePayClient client()
 *
 * @see \YourVendor\SmilePay\SmilePay
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
