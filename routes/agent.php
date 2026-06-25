<?php

use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\FolderController as AgentFolderController;
use App\Http\Controllers\Agent\LeadController as AgentLeadController;
use App\Http\Controllers\Agent\NotificationController as AgentNotificationController;
use App\Http\Controllers\Agent\PushSubscriptionController;
use App\Http\Controllers\FolderInvoiceController;
use App\Http\Controllers\FolderTransportationVoucherController;
use Illuminate\Support\Facades\Route;

Route::prefix('agent')->name('agent.')->middleware('role:agent')->group(function () {
    // Dashboard routes
    Route::get('/dashboard', [AgentDashboardController::class, 'index'])
        ->middleware('can:dashboard.access')
        ->name('dashboard');
    Route::get('/dashboard/performance', [AgentDashboardController::class, 'performanceData'])
        ->middleware('can:dashboard.access')
        ->name('dashboard.performance');
    Route::get('/notifications', [AgentNotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('/notifications/{notificationId}/open', [AgentNotificationController::class, 'open'])
        ->name('notifications.open');
    Route::get('/notifications/poll', [AgentNotificationController::class, 'poll'])
        ->name('notifications.poll');
    Route::get('/push/vapid-public-key', [PushSubscriptionController::class, 'vapidPublicKey'])
        ->name('push.vapid-public-key');
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])
        ->name('push.subscribe');
    Route::delete('/push/subscribe', [PushSubscriptionController::class, 'destroy'])
        ->name('push.unsubscribe');
    // Lead routes
    Route::get('/leads/export', [AgentLeadController::class, 'export'])
        ->middleware('can:leads.access')
        ->name('leads.export');
    Route::get('/leads', [AgentLeadController::class, 'index'])
        ->middleware('can:leads.access')
        ->name('leads.index');
    Route::get('/leads/chart/closed', [AgentLeadController::class, 'closedLeadsChart'])
        ->middleware('can:leads.access')
        ->name('leads.chart.closed');
    Route::post('/leads/check-duplicate', [AgentLeadController::class, 'checkDuplicate'])
        ->middleware('can:leads.create')
        ->name('leads.check-duplicate');
    Route::post('/leads', [AgentLeadController::class, 'store'])
        ->middleware('can:leads.create')
        ->name('leads.store');
    Route::patch('/leads/{lead}/status', [AgentLeadController::class, 'updateStatus'])
        ->middleware('can:leads.access')
        ->name('leads.status');
    Route::get('/leads/{lead}', [AgentLeadController::class, 'show'])
        ->middleware('can:leads.access')
        ->name('leads.show');
    // Folder routes
    Route::post('/folders/sections/{section}/save', [AgentFolderController::class, 'saveSectionDraft'])
        ->middleware(['can:folders.access', 'can:folders.edit'])
        ->name('folders.sections.save');
    Route::get('/folders/upcoming', [AgentFolderController::class, 'upcoming'])
        ->middleware('can:folders.access')
        ->name('folders.upcoming');
    Route::get('/folders/{folder}/invoice', [FolderInvoiceController::class, 'show'])
        ->middleware('can:folders.access')
        ->name('folders.invoice');
    Route::get('/folders/{folder}/transportation-voucher', [FolderTransportationVoucherController::class, 'show'])
        ->middleware('can:folders.access')
        ->name('folders.transportation-voucher');
    Route::get('/folders/{folder}/transportation-voucher/download', [FolderTransportationVoucherController::class, 'download'])
        ->middleware('can:folders.access')
        ->name('folders.transportation-voucher.download');
    Route::resource('folders', AgentFolderController::class)->middleware(['can:folders.access']);
});
