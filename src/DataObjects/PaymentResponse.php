<?php

namespace YourVendor\SmilePay\DataObjects;

class PaymentResponse
{
    public string $responseMessage;
    public string $responseCode;
    public ?string $paymentUrl;
    public ?string $transactionReference;
    public ?string $status;
    public ?string $innbucksPaymentCode;
    public ?string $gatewayRecommendation;
    public ?string $authenticationStatus;
    public ?string $redirectHtml;
    public ?array $customizedHtml;
    public array $rawResponse;

    public function __construct(array $response)
    {
        $this->rawResponse = $response;
        $this->responseMessage = $response['responseMessage'] ?? '';
        $this->responseCode = $response['responseCode'] ?? '';
        $this->paymentUrl = $response['paymentUrl'] ?? null;
        $this->transactionReference = $response['transactionReference'] ?? null;
        $this->status = $response['status'] ?? null;
        $this->innbucksPaymentCode = $response['innbucksPaymentCode'] ?? null;
        $this->gatewayRecommendation = $response['gatewayRecommendation'] ?? null;
        $this->authenticationStatus = $response['authenticationStatus'] ?? null;
        $this->redirectHtml = $response['redirectHtml'] ?? null;
        $this->customizedHtml = $response['customizedHtml'] ?? null;
    }

    /**
     * Check if the response is successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->responseCode === '00';
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
     * Get Innbucks deep link if available
     *
     * @return string|null
     */
    public function getInnbucksDeepLink(): ?string
    {
        if (!$this->innbucksPaymentCode) {
            return null;
        }

        return "schinn.wbpycode://innbucks.co.zw?pymInnCode={$this->innbucksPaymentCode}";
    }

    /**
     * Check if this is a card payment requiring 3DS
     *
     * @return bool
     */
    public function requires3DS(): bool
    {
        return !empty($this->redirectHtml) || !empty($this->customizedHtml);
    }

    /**
     * Get 3DS authentication URL if available
     *
     * @return string|null
     */
    public function get3DSUrl(): ?string
    {
        if (isset($this->customizedHtml['3ds2']['acsUrl'])) {
            return $this->customizedHtml['3ds2']['acsUrl'];
        }

        return null;
    }

    /**
     * Get 3DS challenge request if available
     *
     * @return string|null
     */
    public function get3DSChallengeRequest(): ?string
    {
        if (isset($this->customizedHtml['3ds2']['cReq'])) {
            return $this->customizedHtml['3ds2']['cReq'];
        }

        return null;
    }
}
