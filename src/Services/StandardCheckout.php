<?php

namespace Emmanuelsiziba\SmilePay\Services;

use Emmanuelsiziba\SmilePay\Client\SmilePayClient;
use Emmanuelsiziba\SmilePay\Exceptions\PaymentException;
use Emmanuelsiziba\SmilePay\DataObjects\PaymentResponse;

class StandardCheckout
{
    protected SmilePayClient $client;

    public function __construct(SmilePayClient $client)
    {
        $this->client = $client;
    }

    /**
     * Initiate a standard checkout transaction
     *
     * @param array $data Payment data
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function initiate(array $data): PaymentResponse
    {
        $payload = $this->buildPayload($data);

        $response = $this->client->post('payments/initiate-transaction', $payload);

        if (isset($response['responseCode']) && $response['responseCode'] !== '00') {
            throw new PaymentException(
                $response['responseMessage'] ?? 'Payment initiation failed',
                0,
                null,
                $response
            );
        }

        return new PaymentResponse($response);
    }

    /**
     * Build the payment payload
     *
     * @param array $data
     * @return array
     */
    protected function buildPayload(array $data): array
    {
        return [
            'orderReference' => $data['orderReference'] ?? $this->generateOrderReference(),
            'amount' => $data['amount'],
            'returnUrl' => $data['returnUrl'] ?? config('smilepay.return_url'),
            'resultUrl' => $data['resultUrl'] ?? config('smilepay.result_url'),
            'itemName' => $data['itemName'],
            'itemDescription' => $data['itemDescription'] ?? $data['itemName'],
            'currencyCode' => $data['currencyCode'] ?? config('smilepay.default_currency'),
            'firstName' => $data['firstName'] ?? null,
            'lastName' => $data['lastName'] ?? null,
            'mobilePhoneNumber' => $data['mobilePhoneNumber'] ?? null,
            'email' => $data['email'] ?? null,
            'paymentMethod' => $data['paymentMethod'] ?? null,
            'cancelUrl' => $data['cancelUrl'] ?? config('smilepay.cancel_url'),
            'failureUrl' => $data['failureUrl'] ?? config('smilepay.failure_url'),
        ];
    }

    /**
     * Generate a unique order reference
     *
     * @return string
     */
    protected function generateOrderReference(): string
    {
        return 'SP-' . strtoupper(uniqid()) . '-' . time();
    }
}
