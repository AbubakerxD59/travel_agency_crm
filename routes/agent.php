<?php

use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\LeadController as AgentLeadController;
use Illuminate\Support\Facades\Route;

Route::prefix('agent')->name('agent.')->middleware('role:agent')->group(function () {
    Route::get('/dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/leads', [AgentLeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}/edit', [AgentLeadController::class, 'edit'])->name('leads.edit');
    Route::patch('/leads/{lead}', [AgentLeadController::class, 'update'])->name('leads.update');
    Route::get('/leads/{lead}', [AgentLeadController::class, 'show'])->name('leads.show');
});
