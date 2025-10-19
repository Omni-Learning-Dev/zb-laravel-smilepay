<?php

namespace Emmanuelsiziba\SmilePay\Services;

use Emmanuelsiziba\SmilePay\Client\SmilePayClient;
use Emmanuelsiziba\SmilePay\Exceptions\PaymentException;
use Emmanuelsiziba\SmilePay\DataObjects\PaymentResponse;

class ExpressCheckout
{
    protected SmilePayClient $client;

    public function __construct(SmilePayClient $client)
    {
        $this->client = $client;
    }

    /**
     * Initiate Innbucks express checkout
     *
     * @param array $data
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function innbucks(array $data): PaymentResponse
    {
        $payload = $this->buildBasePayload($data);

        $response = $this->client->post('payments/express-checkout/innbucks', $payload);

        if (isset($response['responseCode']) && $response['responseCode'] !== '00') {
            throw new PaymentException(
                $response['responseMessage'] ?? 'Innbucks payment initiation failed',
                0,
                null,
                $response
            );
        }

        return new PaymentResponse($response);
    }

    /**
     * Initiate Ecocash express checkout
     *
     * @param array $data
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function ecocash(array $data): PaymentResponse
    {
        if (!isset($data['ecocashMobile'])) {
            throw new PaymentException('ecocashMobile is required for Ecocash payments');
        }

        $payload = array_merge($this->buildBasePayload($data), [
            'ecocashMobile' => $data['ecocashMobile'],
        ]);

        $response = $this->client->post('payments/express-checkout/ecocash', $payload);

        if (isset($response['responseCode']) && $response['responseCode'] !== '00') {
            throw new PaymentException(
                $response['responseMessage'] ?? 'Ecocash payment initiation failed',
                0,
                null,
                $response
            );
        }

        return new PaymentResponse($response);
    }

    /**
     * Initiate Omari express checkout (Leg 1)
     *
     * @param array $data
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function omari(array $data): PaymentResponse
    {
        if (!isset($data['omariMobile'])) {
            throw new PaymentException('omariMobile is required for Omari payments');
        }

        $payload = array_merge($this->buildBasePayload($data), [
            'omariMobile' => $data['omariMobile'],
        ]);

        $response = $this->client->post('payments/express-checkout/omari', $payload);

        if (isset($response['responseCode']) && $response['responseCode'] !== '00') {
            throw new PaymentException(
                $response['responseMessage'] ?? 'Omari payment initiation failed',
                0,
                null,
                $response
            );
        }

        return new PaymentResponse($response);
    }

    /**
     * Confirm Omari payment with OTP (Leg 2)
     *
     * @param string $transactionReference
     * @param string $otp
     * @param string $omariMobile
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function omariConfirm(string $transactionReference, string $otp, string $omariMobile): PaymentResponse
    {
        $payload = [
            'transactionReference' => $transactionReference,
            'otp' => $otp,
            'omariMobile' => $omariMobile,
        ];

        $response = $this->client->post('payments/express-checkout/omari/confirmation', $payload);

        if (isset($response['responseCode']) && $response['responseCode'] !== '00') {
            throw new PaymentException(
                $response['responseMessage'] ?? 'Omari payment confirmation failed',
                0,
                null,
                $response
            );
        }

        return new PaymentResponse($response);
    }

    /**
     * Initiate SmileCash express checkout (Leg 1)
     *
     * @param array $data
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function smileCash(array $data): PaymentResponse
    {
        if (!isset($data['smileCashMobile'])) {
            throw new PaymentException('smileCashMobile is required for SmileCash payments');
        }

        $payload = array_merge($this->buildBasePayload($data), [
            'smileCashMobile' => $data['smileCashMobile'],
        ]);

        $response = $this->client->post('payments/express-checkout/smilecash', $payload);

        if (isset($response['responseCode']) && $response['responseCode'] !== '00') {
            throw new PaymentException(
                $response['responseMessage'] ?? 'SmileCash payment initiation failed',
                0,
                null,
                $response
            );
        }

        return new PaymentResponse($response);
    }

    /**
     * Confirm SmileCash payment with OTP (Leg 2)
     *
     * @param string $transactionReference
     * @param string $otp
     * @param string $smileCashMobile
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function smileCashConfirm(string $transactionReference, string $otp, string $smileCashMobile): PaymentResponse
    {
        $payload = [
            'transactionReference' => $transactionReference,
            'otp' => $otp,
            'smileCashMobile' => $smileCashMobile,
        ];

        $response = $this->client->post('payments/express-checkout/smilecash/confirmation', $payload);

        if (isset($response['responseCode']) && $response['responseCode'] !== '00') {
            throw new PaymentException(
                $response['responseMessage'] ?? 'SmileCash payment confirmation failed',
                0,
                null,
                $response
            );
        }

        return new PaymentResponse($response);
    }

    /**
     * Initiate card payment (Visa/Mastercard) via MPGS
     *
     * @param array $data
     * @return PaymentResponse
     * @throws PaymentException
     */
    public function card(array $data): PaymentResponse
    {
        $requiredFields = ['pan', 'expMonth', 'expYear', 'securityCode'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new PaymentException("{$field} is required for card payments");
            }
        }

        $payload = array_merge($this->buildBasePayload($data), [
            'pan' => $data['pan'],
            'expMonth' => $data['expMonth'],
            'expYear' => $data['expYear'],
            'securityCode' => $data['securityCode'],
            'paymentMethod' => $data['paymentMethod'] ?? 'CARD',
        ]);

        $response = $this->client->post('payments/express-checkout/mpgs', $payload);

        if (isset($response['responseCode']) && $response['responseCode'] !== '00') {
            throw new PaymentException(
                $response['responseMessage'] ?? 'Card payment initiation failed',
                0,
                null,
                $response
            );
        }

        return new PaymentResponse($response);
    }

    /**
     * Build base payment payload
     *
     * @param array $data
     * @return array
     */
    protected function buildBasePayload(array $data): array
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

    /**
     * Generate Innbucks deep link URL
     *
     * @param string $paymentCode
     * @return string
     */
    public function getInnbucksDeepLink(string $paymentCode): string
    {
        return "schinn.wbpycode://innbucks.co.zw?pymInnCode={$paymentCode}";
    }
}
