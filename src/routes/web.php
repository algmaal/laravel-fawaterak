<?php

use Algmaal\LaravelFawaterak\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fawaterak Package Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Fawaterak package. These routes are
| automatically loaded by the service provider.
|
*/

Route::group([
    'prefix' => 'fawaterak',
    'middleware' => config('fawaterak.webhook.middleware', ['api']),
], function () {
    Route::post('webhook', [WebhookController::class, 'handle'])
        ->name('fawaterak.webhook');
});
