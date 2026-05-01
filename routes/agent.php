<?php

use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\FolderController as AgentFolderController;
use App\Http\Controllers\Agent\LeadController as AgentLeadController;
use App\Http\Controllers\Agent\NotificationController as AgentNotificationController;
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
    // Lead routes
    Route::get('/leads', [AgentLeadController::class, 'index'])
        ->middleware('can:leads.access')
        ->name('leads.index');
    Route::patch('/leads/{lead}/status', [AgentLeadController::class, 'updateStatus'])
        ->middleware('can:leads.access')
        ->name('leads.status');
    Route::get('/leads/{lead}', [AgentLeadController::class, 'show'])
        ->middleware('can:leads.access')
        ->name('leads.show');
    // Folder routes
    Route::post('/folders/sections/{section}/save', [AgentFolderController::class, 'saveSectionDraft'])
        ->middleware('can:folders.access')
        ->name('folders.sections.save');
    Route::resource('folders', AgentFolderController::class)->middleware('can:folders.access');
});
