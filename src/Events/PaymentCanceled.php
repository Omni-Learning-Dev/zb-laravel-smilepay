<?php

namespace Emmanuelsiziba\SmilePay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Emmanuelsiziba\SmilePay\DataObjects\TransactionStatus;

class PaymentCanceled
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
