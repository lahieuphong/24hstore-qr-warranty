<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LabelPdfController;
use App\Http\Controllers\ProductsTemplateController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\WarrantyLookupController;
use App\Livewire\ActivityLogs\Index as ActivityLogIndex;
use App\Livewire\Dashboard;
use App\Livewire\Imports\Index as ImportIndex;
use App\Livewire\Products\Index as ProductIndex;
use App\Livewire\Profile;
use App\Livewire\Users\Index as UserIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/login', [AuthenticatedSessionController::class, 'legacy'])->name('login.legacy');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/admin/login/', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.store');
});

Route::get('/bao-hanh/{product:qr_token}', WarrantyLookupController::class)
    ->middleware('throttle:120,1')
    ->name('warranty.show');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', Dashboard::class)
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        Route::get('/products', ProductIndex::class)
            ->middleware('permission:products.view')
            ->name('products.index');

        Route::get('/products/{product}/qr.png', QrCodeController::class)
            ->middleware('permission:products.view')
            ->name('products.qr');

        Route::get('/products/{product}/label.pdf', [LabelPdfController::class, 'single'])
            ->middleware('permission:products.print')
            ->name('products.label');

        Route::get('/labels.pdf', [LabelPdfController::class, 'bulk'])
            ->middleware('permission:products.print')
            ->name('labels.bulk');

        Route::get('/imports', ImportIndex::class)
            ->middleware('permission:products.import')
            ->name('imports.index');

        Route::get('/imports/template.xlsx', ProductsTemplateController::class)
            ->middleware('permission:products.import')
            ->name('imports.template');

        Route::get('/users', UserIndex::class)
            ->middleware('permission:users.manage')
            ->name('users.index');

        Route::get('/activity', ActivityLogIndex::class)
            ->middleware('permission:activity.view')
            ->name('activity.index');

        Route::get('/profile', Profile::class)->name('profile');
    });
});
