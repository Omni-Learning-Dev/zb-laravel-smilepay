<?php

namespace Emmanuelsiziba\SmilePay\Services;

use Emmanuelsiziba\SmilePay\Client\SmilePayClient;
use Emmanuelsiziba\SmilePay\Exceptions\PaymentException;
use Emmanuelsiziba\SmilePay\DataObjects\TransactionStatus;

class PaymentUtility
{
    protected SmilePayClient $client;

    public function __construct(SmilePayClient $client)
    {
        $this->client = $client;
    }

    /**
     * Check payment transaction status
     *
     * @param string $orderReference
     * @return TransactionStatus
     * @throws PaymentException
     */
    public function checkStatus(string $orderReference): TransactionStatus
    {
        try {
            $response = $this->client->get("payments/transaction/{$orderReference}/status/check");

            return new TransactionStatus($response);
        } catch (\Exception $e) {
            throw new PaymentException(
                "Failed to check payment status: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Cancel a payment transaction
     *
     * @param string $orderReference
     * @return array
     * @throws PaymentException
     */
    public function cancel(string $orderReference): array
    {
        try {
            $response = $this->client->post("payments/cancel/{$orderReference}");

            if (isset($response['success']) && !$response['success']) {
                throw new PaymentException(
                    $response['description'] ?? 'Payment cancellation failed',
                    0,
                    null,
                    $response
                );
            }

            return $response;
        } catch (PaymentException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new PaymentException(
                "Failed to cancel payment: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Check if payment is successful
     *
     * @param string $orderReference
     * @return bool
     */
    public function isPaymentSuccessful(string $orderReference): bool
    {
        try {
            $status = $this->checkStatus($orderReference);
            return strtoupper($status->status) === 'PAID';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if payment is pending
     *
     * @param string $orderReference
     * @return bool
     */
    public function isPaymentPending(string $orderReference): bool
    {
        try {
            $status = $this->checkStatus($orderReference);
            return strtoupper($status->status) === 'PENDING';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if payment failed
     *
     * @param string $orderReference
     * @return bool
     */
    public function isPaymentFailed(string $orderReference): bool
    {
        try {
            $status = $this->checkStatus($orderReference);
            return in_array(strtoupper($status->status), ['FAILED', 'CANCELED']);
        } catch (\Exception $e) {
            return false;
        }
    }
}
