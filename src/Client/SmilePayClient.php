<?php

namespace Emmanuelsiziba\SmilePay\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Emmanuelsiziba\SmilePay\Exceptions\SmilePayException;

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
            $exception = $this->handleException($e, $endpoint, 'POST');
            $this->logSmilePayException($exception);
            throw $exception;
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
            $exception = $this->handleException($e, $endpoint, 'GET');
            $this->logSmilePayException($exception);
            throw $exception;
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
     * Log SmilePay exceptions with user-friendly messages
     *
     * @param SmilePayException $exception
     * @return void
     */
    protected function logSmilePayException(SmilePayException $exception): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $context = $exception->getContext();
        $logData = [
            'message' => $exception->getMessage(), // User-friendly message
            'status_code' => $exception->getCode(),
            'endpoint' => $context['endpoint'] ?? 'unknown',
            'method' => $context['method'] ?? 'unknown',
        ];

        // Add raw response for debugging (if available)
        if (isset($context['raw_response'])) {
            $logData['raw_response'] = $context['raw_response'];
        }

        // Add any other context information
        if (isset($context['error'])) {
            $logData['error_type'] = $context['error'];
        }

        Log::channel($this->logChannel)->error("SmilePay API Error: {$logData['endpoint']}", $logData);
    }

    /**
     * Handle Guzzle exceptions and convert them to user-friendly SmilePayExceptions
     *
     * @param GuzzleException $exception
     * @param string $endpoint
     * @param string $method
     * @return SmilePayException
     */
    protected function handleException(GuzzleException $exception, string $endpoint, string $method): SmilePayException
    {
        $statusCode = 0;
        $errorMessage = 'An unexpected error occurred while communicating with SmilePay API.';
        $context = [
            'endpoint' => $endpoint,
            'method' => $method,
        ];

        // Handle different types of Guzzle exceptions
        if ($exception instanceof \GuzzleHttp\Exception\RequestException) {
            $response = $exception->getResponse();

            if ($response) {
                $statusCode = $response->getStatusCode();
                $context['status_code'] = $statusCode;

                // Try to get a readable error message from the response
                $responseBody = (string) $response->getBody();
                $context['raw_response'] = $responseBody;

                // Try to decode JSON error response
                $jsonError = json_decode($responseBody, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($jsonError['message'])) {
                    $errorMessage = $jsonError['message'];
                } elseif (json_last_error() === JSON_ERROR_NONE && isset($jsonError['error'])) {
                    $errorMessage = is_string($jsonError['error']) ? $jsonError['error'] : ($jsonError['error']['message'] ?? $errorMessage);
                } else {
                    // Provide user-friendly messages based on status code
                    $errorMessage = $this->getStatusCodeMessage($statusCode, $endpoint);
                }
            } else {
                $errorMessage = "Network error: Unable to reach SmilePay API. Please check your internet connection.";
                $context['error'] = 'No response received from server';
            }
        } elseif ($exception instanceof \GuzzleHttp\Exception\ConnectException) {
            $errorMessage = "Connection error: Unable to connect to SmilePay API. Please check your internet connection and try again.";
            $context['error'] = 'Connection failed';
        } elseif ($exception instanceof \GuzzleHttp\Exception\ServerException) {
            $errorMessage = "Server error: SmilePay API is currently experiencing issues. Please try again later.";
            $context['error'] = 'Server error';
        }

        return new SmilePayException($errorMessage, $statusCode, $exception, $context);
    }

    /**
     * Get user-friendly message based on HTTP status code
     *
     * @param int $statusCode
     * @param string $endpoint
     * @return string
     */
    protected function getStatusCodeMessage(int $statusCode, string $endpoint): string
    {
        return match ($statusCode) {
            400 => "Bad request: The payment request is invalid. Please check your payment details and try again.",
            401 => "Authentication failed: Invalid API credentials. Please check your SmilePay configuration.",
            403 => "Access denied: You don't have permission to access this resource. Please verify your API credentials.",
            404 => "Endpoint not found: The payment method or endpoint '{$endpoint}' is not available. Please verify the API endpoint or contact support.",
            405 => "Method not allowed: The requested operation is not supported for this endpoint.",
            408 => "Request timeout: The request took too long to process. Please try again.",
            422 => "Validation error: The payment data provided is invalid. Please check all required fields.",
            429 => "Rate limit exceeded: Too many requests. Please wait a moment and try again.",
            500 => "Server error: SmilePay API encountered an internal error. Please try again later.",
            502 => "Bad gateway: SmilePay API is temporarily unavailable. Please try again in a few moments.",
            503 => "Service unavailable: SmilePay API is currently down for maintenance. Please try again later.",
            504 => "Gateway timeout: The request timed out. Please try again.",
            default => "SmilePay API error (HTTP {$statusCode}): An unexpected error occurred. Please try again or contact support."
        };
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
