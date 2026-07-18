<?php

use App\Livewire\Home;
use App\Livewire\WarrantyLookup;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/bao-hanh/{token}', WarrantyLookup::class)
    ->whereUuid('token')
    ->middleware('throttle:120,1')
    ->name('warranty.show');
