<?php

use Illuminate\Support\Facades\Route;
use YourVendor\SmilePay\Http\Controllers\WebhookController;

Route::post('/smilepay/webhook', [WebhookController::class, 'handle'])
    ->name(config('smilepay.webhook.route_name', 'smilepay.webhook'));
