<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\PackageController;
use App\Http\Controllers\Superadmin\TenantController;
use App\Http\Controllers\Superadmin\UserController as SuperadminUserController;
use App\Http\Controllers\Tenant\CashShiftController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\ExportController;
use App\Http\Controllers\Tenant\GuideController;
use App\Http\Controllers\Tenant\InventoryAuditController;
use App\Http\Controllers\Tenant\InventoryController;
use App\Http\Controllers\Tenant\PosController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\SettingsController;
use App\Http\Controllers\Tenant\UserController as TenantUserController;
use Illuminate\Support\Facades\Route;

// Redirect root ke login
Route::redirect('/', '/login');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Superadmin routes
    Route::middleware('role:superadmin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('tenants', TenantController::class);
        Route::resource('packages', PackageController::class);
        Route::resource('users', SuperadminUserController::class);
        Route::view('/settings', 'superadmin.settings')->name('settings');
    });

    // Tenant routes (admin_tenant & kasir)
    Route::middleware('tenant')->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('dashboard');

        // Khusus admin_tenant
        Route::middleware('role:admin_tenant')->group(function () {
            Route::resource('inventory', InventoryController::class);
            Route::resource('users', TenantUserController::class)->except(['show']);
            Route::resource('cash-shifts', CashShiftController::class)->only(['index', 'show']);

            // Export Excel
            Route::get('/export/sales', [ExportController::class, 'sales'])->name('export.sales');
            Route::get('/export/inventory', [ExportController::class, 'inventory'])->name('export.inventory');

            // Audit Stok IMEI
            Route::get('/audits', [InventoryAuditController::class, 'index'])->name('audits.index');
            Route::get('/audits/create', [InventoryAuditController::class, 'create'])->name('audits.create');
            Route::post('/audits', [InventoryAuditController::class, 'store'])->name('audits.store');
            Route::get('/audits/{audit}', [InventoryAuditController::class, 'show'])->name('audits.show');
            Route::post('/audits/{audit}/scan', [InventoryAuditController::class, 'scan'])->name('audits.scan');
            Route::post('/audits/{audit}/complete', [InventoryAuditController::class, 'complete'])->name('audits.complete');
        });

        // Buka / Tutup shift kasir
        Route::get('/shift/open', [CashShiftController::class, 'create'])->name('shift.create');
        Route::post('/shift/open', [CashShiftController::class, 'store'])->name('shift.store');
        Route::get('/shift/close', [CashShiftController::class, 'editClose'])->name('shift.close');
        Route::post('/shift/close', [CashShiftController::class, 'updateClose'])->name('shift.close.update');

        // POS — wajib shift aktif
        Route::middleware('shift')->group(function () {
            Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
            Route::get('/pos/lookup', [PosController::class, 'lookup'])->name('pos.lookup');
            Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        });

        Route::get('/reports', [ReportController::class, 'sales'])->name('reports');
        Route::get('/reports/shifts', [ReportController::class, 'shifts'])->name('reports.shifts');
        Route::get('/guide', [GuideController::class, 'index'])->name('guide');

        // Pengaturan Toko & Akun
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
        Route::put('/settings/store', [SettingsController::class, 'updateStore'])->name('settings.store');
        Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    });
});
