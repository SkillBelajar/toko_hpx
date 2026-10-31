<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Superadmin\PackageController;
use App\Http\Controllers\Superadmin\TenantController;
use App\Http\Controllers\Tenant\InventoryController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\Tenant\PosController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\ShiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', App\Http\Controllers\HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::view('/dashboard', 'superadmin.dashboard')->name('dashboard');

        Route::resource('packages', PackageController::class)->except('show');

        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');

        Route::view('/settings', 'superadmin.settings')->name('settings');
        Route::get('/guide', [GuideController::class, 'index'])->name('guide');
    });

Route::middleware(['auth', 'role:tenant'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::view('/dashboard', 'tenant.dashboard')->name('dashboard');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

        // Shift laci
        Route::get('/shift/open', [ShiftController::class, 'open'])->name('shift.open');
        Route::post('/shift/open', [ShiftController::class, 'store'])->name('shift.store');
        Route::get('/shift/close', [ShiftController::class, 'close'])->name('shift.close');
        Route::put('/shift/close', [ShiftController::class, 'update'])->name('shift.update');

        // POS — wajib shift aktif
        Route::middleware('shift')->group(function () {
            Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
            Route::get('/pos/lookup', [PosController::class, 'lookup'])->name('pos.lookup');
            Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        });

        Route::get('/reports', [ReportController::class, 'sales'])->name('reports');
        Route::get('/reports/shifts', [ReportController::class, 'shifts'])->name('reports.shifts');
        Route::get('/guide', [GuideController::class, 'index'])->name('guide');
        Route::view('/settings', 'tenant.placeholder')->name('settings');
    });
