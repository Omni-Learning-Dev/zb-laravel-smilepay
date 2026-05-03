<?php

namespace Emmanuelsiziba\SmilePay\Http\Controllers;

use Emmanuelsiziba\SmilePay\Events\PaymentCanceled;
use Emmanuelsiziba\SmilePay\Events\PaymentFailed;
use Emmanuelsiziba\SmilePay\Events\PaymentReceived;
use Emmanuelsiziba\SmilePay\Services\PaymentUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class ReturnController
{
    public function __construct(protected PaymentUtility $utility) {}

    /**
     * ZB redirects the customer here after the hosted checkout completes, is cancelled, or fails.
     *
     * ZB passes orderReference (and sometimes status) as query parameters.
     * We poll for definitive status, fire the appropriate package event, then either:
     *  - redirect to the app's custom return URL (if SMILEPAY_APP_RETURN_URL is set), or
     *  - return a simple JSON response so the caller can handle navigation.
     */
    public function handle(Request $request)
    {
        $orderReference = $request->get('orderReference') ?? $request->get('reference');

        Log::channel(config('smilepay.logging.channel', 'stack'))
            ->info('SmilePay return received', $request->all());

        if (! $orderReference) {
            return $this->respond('failed', null, 'Missing order reference');
        }

        try {
            $status = $this->utility->checkStatus($orderReference);

            if ($status->isPaid()) {
                Event::dispatch(new PaymentReceived($status));

                return $this->respond('success', $orderReference);
            }

            if ($status->isCanceled()) {
                Event::dispatch(new PaymentCanceled($status));

                return $this->respond('cancelled', $orderReference, 'Payment was cancelled');
            }

            if ($status->isFailed()) {
                Event::dispatch(new PaymentFailed($status));

                return $this->respond('failed', $orderReference, 'Payment failed');
            }
        } catch (\Exception $e) {
            Log::channel(config('smilepay.logging.channel', 'stack'))
                ->error('SmilePay return status check failed', [
                    'orderReference' => $orderReference,
                    'error' => $e->getMessage(),
                ]);
        }

        // Status could not be confirmed — let the consuming app poll
        return $this->respond('pending', $orderReference, 'Payment status is being confirmed');
    }

    /**
     * Respond to the return. If the consuming app has set SMILEPAY_APP_RETURN_URL,
     * redirect there with status/reference as query params so the app can handle navigation
     * (e.g. deep-link back to the mobile app). Otherwise return JSON.
     */
    protected function respond(string $status, ?string $orderReference, string $message = '')
    {
        $appReturnUrl = config('smilepay.app_return_url');

        if ($appReturnUrl) {
            $params = http_build_query(array_filter([
                'status' => $status,
                'orderReference' => $orderReference,
                'message' => $message,
            ]));

            return redirect($appReturnUrl.'?'.$params);
        }

        return response()->json(array_filter([
            'status' => $status,
            'orderReference' => $orderReference,
            'message' => $message ?: null,
        ]));
    }
}
