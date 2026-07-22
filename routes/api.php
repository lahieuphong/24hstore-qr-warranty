<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\WarrantyController;
use App\Http\Controllers\Api\V1\WarrantySearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->name('api.v1.health');

    Route::get('/warranties/search', WarrantySearchController::class)
        ->middleware('throttle:public-warranty-search')
        ->name('api.v1.warranties.search');

    Route::get('/warranties/{product:qr_token}', WarrantyController::class)
        ->middleware('throttle:public-warranty')
        ->name('api.v1.warranties.show');
});
