<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->defaultRedirectRoute())
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        // Dashboard Routes
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('can:dashboard.access')
            ->name('dashboard');

        // Agent Routes
        Route::get('/agents/{agent}/permissions', [AgentController::class, 'permissions'])
            ->name('agents.permissions.show');

        Route::put('/agents/{agent}/permissions', [AgentController::class, 'syncPermissions'])
            ->name('agents.permissions.update');

        Route::resource('agents', AgentController::class)->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);

        Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
        Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

        // Company Routes
        Route::resource('companies', CompanyController::class)->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);
    });
});
