<?php

namespace YourVendor\SmilePay\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use YourVendor\SmilePay\Exceptions\SmilePayException;

class SmilePayClient
{
    protected Client $client;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $baseUrl;
    protected bool $loggingEnabled;
    protected string $logChannel;

    public function __construct()
    {
        $environment = config('smilepay.environment', 'sandbox');
        $this->baseUrl = config("smilepay.base_url.{$environment}");
        $this->apiKey = config('smilepay.api_key');
        $this->apiSecret = config('smilepay.api_secret');
        $this->loggingEnabled = config('smilepay.logging.enabled', false);
        $this->logChannel = config('smilepay.logging.channel', 'stack');

        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new SmilePayException('SmilePay API credentials are not configured.');
        }

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => config('smilepay.timeout', 30),
            'connect_timeout' => config('smilepay.connect_timeout', 10),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => $this->apiKey,
                'x-api-secret' => $this->apiSecret,
            ],
        ]);
    }

    /**
     * Make a POST request to the SmilePay API
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws SmilePayException
     */
    public function post(string $endpoint, array $data = []): array
    {
        try {
            $this->logRequest('POST', $endpoint, $data);

            $response = $this->client->post($endpoint, [
                'json' => $data,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            $this->logResponse($endpoint, $body);

            return $body;
        } catch (GuzzleException $e) {
            $this->logError($endpoint, $e);
            throw new SmilePayException(
                "SmilePay API request failed: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Make a GET request to the SmilePay API
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws SmilePayException
     */
    public function get(string $endpoint, array $params = []): array
    {
        try {
            $this->logRequest('GET', $endpoint, $params);

            $response = $this->client->get($endpoint, [
                'query' => $params,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            $this->logResponse($endpoint, $body);

            return $body;
        } catch (GuzzleException $e) {
            $this->logError($endpoint, $e);
            throw new SmilePayException(
                "SmilePay API request failed: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Log the API request
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return void
     */
    protected function logRequest(string $method, string $endpoint, array $data): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        Log::channel($this->logChannel)->info("SmilePay API Request: {$method} {$endpoint}", [
            'data' => $data,
        ]);
    }

    /**
     * Log the API response
     *
     * @param string $endpoint
     * @param array $response
     * @return void
     */
    protected function logResponse(string $endpoint, array $response): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        Log::channel($this->logChannel)->info("SmilePay API Response: {$endpoint}", [
            'response' => $response,
        ]);
    }

    /**
     * Log API errors
     *
     * @param string $endpoint
     * @param \Throwable $exception
     * @return void
     */
    protected function logError(string $endpoint, \Throwable $exception): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        Log::channel($this->logChannel)->error("SmilePay API Error: {$endpoint}", [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ]);
    }

    /**
     * Get the base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
