<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:dashboard.access')
        ->name('dashboard');

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
    Route::post('/leads/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::patch('/leads/{lead}/assign', [LeadController::class, 'updateAssign'])->name('leads.assign.update');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

    Route::resource('companies', CompanyController::class)->only([
        'index',
        'store',
        'show',
        'update',
        'destroy',
    ]);
});
