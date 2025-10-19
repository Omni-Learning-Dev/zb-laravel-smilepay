# SmilePay Laravel Package - Examples

This document provides practical examples for common use cases.

## Table of Contents
- [Basic Payment Flow](#basic-payment-flow)
- [Complete Ecommerce Integration](#complete-ecommerce-integration)
- [Subscription Billing](#subscription-billing)
- [Mobile App Integration](#mobile-app-integration)
- [Advanced Examples](#advanced-examples)

## Basic Payment Flow

### Simple Product Checkout

```php
// routes/web.php
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/payment/return', [CheckoutController::class, 'return'])->name('payment.return');

// app/Http/Controllers/CheckoutController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Emmanuelsiziba\SmilePay\Facades\SmilePay;
use Emmanuelsiziba\SmilePay\Exceptions\PaymentException;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'product_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        try {
            $response = SmilePay::standardCheckout()->initiate([
                'amount' => $validated['amount'],
                'itemName' => $validated['product_name'],
                'itemDescription' => 'Purchase from ' . config('app.name'),
                'email' => $validated['email'],
                'mobilePhoneNumber' => $validated['phone'],
                'returnUrl' => route('payment.return'),
            ]);

            if ($response->isSuccessful()) {
                return redirect($response->paymentUrl);
            }

            return back()->withErrors(['payment' => 'Failed to initiate payment']);

        } catch (PaymentException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }

    public function return(Request $request)
    {
        return view('payment.return', [
            'orderReference' => $request->query('reference'),
        ]);
    }
}
```

## Complete Ecommerce Integration

### Order Processing with Database

```php
// database/migrations/xxxx_create_orders_table.php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_reference')->unique();
    $table->string('transaction_reference')->nullable();
    $table->foreignId('user_id')->constrained();
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3);
    $table->string('payment_method')->nullable();
    $table->string('status')->default('pending'); // pending, paid, failed, canceled
    $table->text('items');
    $table->timestamps();
});

// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_reference',
        'transaction_reference',
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// app/Http/Controllers/OrderController.php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Emmanuelsiziba\SmilePay\Facades\SmilePay;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = auth()->user();

        // Create order
        $order = Order::create([
            'order_reference' => 'ORD-' . strtoupper(uniqid()),
            'user_id' => $user->id,
            'amount' => $request->total,
            'currency' => 'USD',
            'items' => $request->cart_items,
            'status' => 'pending',
        ]);

        // Initiate payment
        $response = SmilePay::standardCheckout()->initiate([
            'orderReference' => $order->order_reference,
            'amount' => $order->amount,
            'itemName' => 'Order ' . $order->order_reference,
            'itemDescription' => count($order->items) . ' items',
            'email' => $user->email,
            'mobilePhoneNumber' => $user->phone,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
        ]);

        if ($response->isSuccessful()) {
            $order->update([
                'transaction_reference' => $response->transactionReference,
            ]);

            return redirect($response->paymentUrl);
        }

        return back()->withErrors(['payment' => 'Payment initiation failed']);
    }
}

// app/Listeners/HandlePaymentReceived.php
namespace App\Listeners;

use App\Models\Order;
use App\Notifications\OrderConfirmation;
use Emmanuelsiziba\SmilePay\Events\PaymentReceived;

class HandlePaymentReceived
{
    public function handle(PaymentReceived $event)
    {
        $transaction = $event->transaction;

        $order = Order::where('order_reference', $transaction->orderReference)->first();

        if (!$order) {
            \Log::warning("Order not found: {$transaction->orderReference}");
            return;
        }

        $order->update([
            'status' => 'paid',
            'payment_method' => $transaction->paymentOption,
        ]);

        // Send confirmation
        $order->user->notify(new OrderConfirmation($order));

        // Process fulfillment
        dispatch(new ProcessOrderFulfillment($order));
    }
}
```

## Subscription Billing

### Monthly Subscription Payment

```php
// app/Http/Controllers/SubscriptionController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Emmanuelsiziba\SmilePay\Facades\SmilePay;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $user = auth()->user();
        $plan = $request->plan; // 'basic', 'premium', etc.

        $amounts = [
            'basic' => 10.00,
            'premium' => 25.00,
            'enterprise' => 50.00,
        ];

        $response = SmilePay::standardCheckout()->initiate([
            'orderReference' => 'SUB-' . $user->id . '-' . time(),
            'amount' => $amounts[$plan],
            'itemName' => ucfirst($plan) . ' Subscription',
            'itemDescription' => 'Monthly subscription',
            'email' => $user->email,
            'mobilePhoneNumber' => $user->phone,
        ]);

        if ($response->isSuccessful()) {
            // Store subscription record
            $user->subscriptions()->create([
                'plan' => $plan,
                'amount' => $amounts[$plan],
                'transaction_reference' => $response->transactionReference,
                'status' => 'pending',
            ]);

            return redirect($response->paymentUrl);
        }

        return back()->withErrors(['subscription' => 'Failed to process subscription']);
    }
}
```

## Mobile App Integration

### API Endpoints for Mobile Apps

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments/ecocash', [MobilePaymentController::class, 'ecocash']);
    Route::post('/payments/innbucks', [MobilePaymentController::class, 'innbucks']);
    Route::get('/payments/{reference}/status', [MobilePaymentController::class, 'status']);
});

// app/Http/Controllers/Api/MobilePaymentController.php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Emmanuelsiziba\SmilePay\Facades\SmilePay;

class MobilePaymentController extends Controller
{
    public function ecocash(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'ecocash_number' => 'required|string',
            'description' => 'required|string',
        ]);

        try {
            $response = SmilePay::expressCheckout()->ecocash([
                'amount' => $validated['amount'],
                'itemName' => $validated['description'],
                'ecocashMobile' => $validated['ecocash_number'],
                'email' => $request->user()->email,
            ]);

            return response()->json([
                'success' => $response->isSuccessful(),
                'transaction_reference' => $response->transactionReference,
                'status' => $response->status,
                'message' => $response->responseMessage,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function innbucks(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string',
        ]);

        $response = SmilePay::expressCheckout()->innbucks([
            'amount' => $validated['amount'],
            'itemName' => $validated['description'],
            'email' => $request->user()->email,
        ]);

        return response()->json([
            'success' => $response->isSuccessful(),
            'payment_code' => $response->innbucksPaymentCode,
            'deep_link' => $response->getInnbucksDeepLink(),
            'transaction_reference' => $response->transactionReference,
        ]);
    }

    public function status(Request $request, $reference)
    {
        $status = SmilePay::utility()->checkStatus($reference);

        return response()->json([
            'order_reference' => $status->orderReference,
            'status' => $status->status,
            'amount' => $status->amount,
            'currency' => $status->currency,
            'payment_method' => $status->paymentOption,
            'is_paid' => $status->isPaid(),
        ]);
    }
}
```

## Advanced Examples

### Polling Payment Status

```php
// For mobile money payments, poll until status changes
public function pollPaymentStatus($orderReference, $maxAttempts = 30)
{
    $attempt = 0;

    while ($attempt < $maxAttempts) {
        $status = SmilePay::utility()->checkStatus($orderReference);

        if ($status->isPaid()) {
            return ['success' => true, 'status' => 'paid'];
        }

        if ($status->isFailed() || $status->isCanceled()) {
            return ['success' => false, 'status' => $status->status];
        }

        sleep(2); // Wait 2 seconds between checks
        $attempt++;
    }

    return ['success' => false, 'status' => 'timeout'];
}
```

### Multi-Currency Support

```php
public function initiateCrossBorderPayment($amount, $fromCurrency, $toCurrency)
{
    // Convert currency
    $convertedAmount = $this->convertCurrency($amount, $fromCurrency, $toCurrency);

    $currencyCode = $toCurrency === 'USD' ? '840' : '924';

    $response = SmilePay::standardCheckout()->initiate([
        'amount' => $convertedAmount,
        'currencyCode' => $currencyCode,
        'itemName' => 'Cross-border payment',
        // ... other fields
    ]);

    return $response;
}
```

### Card Payment with 3DS

```php
public function processCardPayment(Request $request)
{
    $response = SmilePay::expressCheckout()->card([
        'amount' => $request->amount,
        'itemName' => $request->item_name,
        'pan' => $request->card_number,
        'expMonth' => $request->exp_month,
        'expYear' => $request->exp_year,
        'securityCode' => $request->cvv,
        'email' => auth()->user()->email,
    ]);

    if ($response->requires3DS()) {
        // Return 3DS HTML for iframe or redirect
        return view('payment.3ds', [
            'redirectHtml' => $response->redirectHtml,
            'acsUrl' => $response->get3DSUrl(),
        ]);
    }

    // Payment processed without 3DS
    return redirect()->route('payment.success');
}
```

### Refund Handling (Manual Process)

```php
// Note: SmilePay API doesn't have automatic refunds
// You need to handle refunds manually through merchant dashboard

public function requestRefund(Order $order)
{
    if (!$order->isPaid()) {
        return back()->withErrors(['refund' => 'Only paid orders can be refunded']);
    }

    // Mark order as refund requested
    $order->update(['status' => 'refund_requested']);

    // Notify admin to process manual refund
    \Notification::send(
        User::admins()->get(),
        new RefundRequested($order)
    );

    return back()->with('success', 'Refund request submitted');
}
```

### Testing with Fake Payments

```php
// tests/Feature/PaymentTest.php
namespace Tests\Feature;

use Tests\TestCase;
use Emmanuelsiziba\SmilePay\Facades\SmilePay;

class PaymentTest extends TestCase
{
    /** @test */
    public function can_initiate_standard_checkout()
    {
        $response = SmilePay::standardCheckout()->initiate([
            'amount' => 100.00,
            'itemName' => 'Test Product',
            'email' => 'test@example.com',
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertNotNull($response->paymentUrl);
    }

    /** @test */
    public function can_check_payment_status()
    {
        $status = SmilePay::utility()->checkStatus('TEST-ORDER-REF');

        $this->assertInstanceOf(TransactionStatus::class, $status);
    }
}
```

## More Examples

For more examples, visit the [GitHub repository](https://github.com/emmanuelsiziba/zb-laravel-smilepay) or check the [official documentation](https://docs.smilepay.id/).
