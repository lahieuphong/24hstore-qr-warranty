<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\LabelPdfController;
use App\Http\Controllers\Admin\ProductsTemplateController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Data\Index as DataAdminIndex;
use App\Livewire\Admin\Imports\Index as ImportIndex;
use App\Livewire\Admin\Products\Index as ProductIndex;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\Users\Index as UserIndex;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.store');
});

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

        Route::get('/data', DataAdminIndex::class)
            ->middleware('role:super-admin')
            ->name('data.index');

        Route::get('/profile', Profile::class)->name('profile');
    });
});
