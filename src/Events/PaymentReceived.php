<?php

namespace YourVendor\SmilePay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use YourVendor\SmilePay\DataObjects\TransactionStatus;

class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public TransactionStatus $transaction;

    /**
     * Create a new event instance.
     *
     * @param TransactionStatus $transaction
     */
    public function __construct(TransactionStatus $transaction)
    {
        $this->transaction = $transaction;
    }
}
