<?php

use Illuminate\Support\Facades\Route;
use Emmanuelsiziba\SmilePay\Http\Controllers\WebhookController;
use Emmanuelsiziba\SmilePay\Http\Controllers\ReturnController;

// Server-to-server webhook: ZB posts payment status here
Route::post('/smilepay/webhook', [WebhookController::class, 'handle'])
    ->name(config('smilepay.webhook.route_name', 'smilepay.webhook'));

// User redirect: ZB sends the customer here after completing / cancelling / failing on the hosted page
Route::get('/smilepay/return', [ReturnController::class, 'handle'])
    ->name('smilepay.return');
