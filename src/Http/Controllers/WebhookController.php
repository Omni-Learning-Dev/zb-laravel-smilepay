<?php

namespace YourVendor\SmilePay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use YourVendor\SmilePay\Events\PaymentReceived;
use YourVendor\SmilePay\Events\PaymentFailed;
use YourVendor\SmilePay\Events\PaymentCanceled;
use YourVendor\SmilePay\DataObjects\TransactionStatus;

class WebhookController
{
    /**
     * Handle SmilePay webhook callback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            // Log the webhook payload
            Log::channel(config('smilepay.logging.channel', 'stack'))
                ->info('SmilePay Webhook Received', ['payload' => $payload]);

            // Validate the payload
            if (!$this->isValidPayload($payload)) {
                Log::warning('Invalid SmilePay webhook payload', ['payload' => $payload]);
                return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
            }

            // Create transaction status object
            $transaction = new TransactionStatus($payload);

            // Dispatch appropriate event based on status
            $this->dispatchEvent($transaction);

            return response()->json(['status' => 'success', 'message' => 'Webhook processed'], 200);
        } catch (\Exception $e) {
            Log::error('SmilePay Webhook Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Validate webhook payload
     *
     * @param array $payload
     * @return bool
     */
    protected function isValidPayload(array $payload): bool
    {
        $requiredFields = [
            'merchantId',
            'reference',
            'orderReference',
            'status',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Dispatch event based on transaction status
     *
     * @param TransactionStatus $transaction
     * @return void
     */
    protected function dispatchEvent(TransactionStatus $transaction): void
    {
        $status = strtoupper($transaction->status);

        switch ($status) {
            case 'PAID':
                Event::dispatch(new PaymentReceived($transaction));
                break;

            case 'FAILED':
                Event::dispatch(new PaymentFailed($transaction));
                break;

            case 'CANCELED':
                Event::dispatch(new PaymentCanceled($transaction));
                break;

            default:
                Log::info("SmilePay payment status: {$status}", [
                    'orderReference' => $transaction->orderReference,
                ]);
                break;
        }
    }
}
