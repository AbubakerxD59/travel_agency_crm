<?php

use App\Http\Controllers\Admin\AbbreviationController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FolderController;
use App\Http\Controllers\Admin\FolderPaymentController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\FolderInvoiceController;
use App\Http\Controllers\FolderTransportationVoucherController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('role:super-admin')->group(function () {
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::get('/notifications/{notificationId}/open', [NotificationController::class, 'open'])
        ->name('notifications.open');

    Route::get('/folder-payments', [FolderPaymentController::class, 'index'])->name('folder-payments.index');
    Route::get('/folder-payments/{folder_payment}', [FolderPaymentController::class, 'show'])
        ->name('folder-payments.show');
    Route::post('/folder-payments/{folder_payment}/image', [FolderPaymentController::class, 'updateImage'])
        ->name('folder-payments.image.update');
    Route::delete('/folder-payments/{folder_payment}/image', [FolderPaymentController::class, 'destroyImage'])
        ->name('folder-payments.image.destroy');
    Route::post('/folder-payments/{folder_payment}/approve', [FolderPaymentController::class, 'approve'])
        ->name('folder-payments.approve');
    Route::post('/folder-payments/{folder_payment}/reject', [FolderPaymentController::class, 'reject'])
        ->name('folder-payments.reject');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:dashboard.access')
        ->name('dashboard');
    Route::get('/dashboard/agent-performance', [DashboardController::class, 'agentPerformanceData'])
        ->middleware('can:dashboard.access')
        ->name('dashboard.agent-performance');

    Route::get('/agents/{agent}/permissions', [AgentController::class, 'permissions'])
        ->name('agents.permissions.show');

    Route::put('/agents/{agent}/permissions', [AgentController::class, 'syncPermissions'])
        ->name('agents.permissions.update');

    Route::get('/agents/{agent}/overview', [AgentController::class, 'overview'])
        ->name('agents.overview');
    Route::get('/agents/{agent}/overview/performance', [AgentController::class, 'overviewPerformanceData'])
        ->name('agents.overview.performance');

    Route::resource('agents', AgentController::class)->only([
        'index',
        'store',
        'show',
        'update',
        'destroy',
    ]);

    Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/chart/closed', [LeadController::class, 'closedLeadsChart'])->name('leads.chart.closed');
    Route::post('/leads/check-duplicate', [LeadController::class, 'checkDuplicate'])->name('leads.check-duplicate');
    Route::post('/leads/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::patch('/leads/{lead}/assign', [LeadController::class, 'updateAssign'])->name('leads.assign.update');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::get('/folders', [FolderController::class, 'index'])->name('folders.index');
    Route::get('/folders/upcoming', [FolderController::class, 'upcoming'])->name('folders.upcoming');
    Route::get('/folders/create', [FolderController::class, 'create'])->name('folders.create');
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::patch('/folders/{folder}/lock', [FolderController::class, 'toggleLock'])->name('folders.toggle-lock');
    Route::get('/folders/{folder}/edit', [FolderController::class, 'edit'])->name('folders.edit');
    Route::patch('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::post('/folders/sections/{section}/save', [FolderController::class, 'saveSectionDraft'])
        ->name('folders.sections.save');
    Route::get('/folders/{folder}/invoice', [FolderInvoiceController::class, 'show'])->name('folders.invoice');
    Route::get('/folders/{folder}/invoice/download', [FolderInvoiceController::class, 'download'])
        ->name('folders.invoice.download');
    Route::get('/folders/{folder}/transportation-voucher', [FolderTransportationVoucherController::class, 'show'])
        ->name('folders.transportation-voucher');
    Route::get('/folders/{folder}/transportation-voucher/download', [FolderTransportationVoucherController::class, 'download'])
        ->name('folders.transportation-voucher.download');
    Route::get('/folders/{folder}', [FolderController::class, 'show'])->name('folders.show');

    Route::resource('companies', CompanyController::class)->only([
        'index',
        'store',
        'show',
        'update',
        'destroy',
    ]);

    Route::resource('abbreviations', AbbreviationController::class)->only([
        'index',
        'store',
        'show',
        'update',
        'destroy',
    ]);
});
