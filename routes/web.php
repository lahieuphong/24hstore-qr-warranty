<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\WarrantyLookupController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/bao-hanh/{product:qr_token}', WarrantyLookupController::class)
    ->middleware('throttle:120,1')
    ->name('warranty.show');

require __DIR__.'/admin.php';
