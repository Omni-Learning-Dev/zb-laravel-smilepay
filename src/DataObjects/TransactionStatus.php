<?php

namespace Emmanuelsiziba\SmilePay\DataObjects;

class TransactionStatus
{
    public string $merchantId;
    public string $reference;
    public string $orderReference;
    public string $itemName;
    public float $amount;
    public string $currency;
    public string $paymentOption;
    public string $status;
    public string $createdDate;
    public string $returnUrl;
    public string $resultUrl;
    public ?float $clientFee;
    public ?float $merchantFee;
    public array $rawResponse;

    public function __construct(array $response)
    {
        $this->rawResponse = $response;
        $this->merchantId = $response['merchantId'] ?? '';
        $this->reference = $response['reference'] ?? '';
        $this->orderReference = $response['orderReference'] ?? '';
        $this->itemName = $response['itemName'] ?? '';
        $this->amount = $response['amount'] ?? 0.0;
        $this->currency = $response['currency'] ?? '';
        $this->paymentOption = $response['paymentOption'] ?? '';
        $this->status = $response['status'] ?? '';
        $this->createdDate = $response['createdDate'] ?? '';
        $this->returnUrl = $response['returnUrl'] ?? '';
        $this->resultUrl = $response['resultUrl'] ?? '';
        $this->clientFee = $response['clientFee'] ?? null;
        $this->merchantFee = $response['merchantFee'] ?? null;
    }

    /**
     * Check if payment is successful
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return strtoupper($this->status) === 'PAID';
    }

    /**
     * Check if payment is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return strtoupper($this->status) === 'PENDING';
    }

    /**
     * Check if payment failed
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return strtoupper($this->status) === 'FAILED';
    }

    /**
     * Check if payment was canceled
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return strtoupper($this->status) === 'CANCELED';
    }

    /**
     * Get the response as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->rawResponse;
    }

    /**
     * Get total fees
     *
     * @return float
     */
    public function getTotalFees(): float
    {
        return ($this->clientFee ?? 0) + ($this->merchantFee ?? 0);
    }

    /**
     * Get net amount (amount - merchant fee)
     *
     * @return float
     */
    public function getNetAmount(): float
    {
        return $this->amount - ($this->merchantFee ?? 0);
    }
}
