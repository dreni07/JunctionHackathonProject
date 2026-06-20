<?php

use App\Http\Controllers\Operations\ActivityLogController;
use App\Http\Controllers\Operations\AlertController;
use App\Http\Controllers\Operations\AssetController;
use App\Http\Controllers\Operations\BlackoutWindowController;
use App\Http\Controllers\Operations\ConflictController;
use App\Http\Controllers\Operations\DashboardController;
use App\Http\Controllers\Operations\EmailController;
use App\Http\Controllers\Operations\EventContactController;
use App\Http\Controllers\Operations\EventController;
use App\Http\Controllers\Operations\EventRequestController;
use App\Http\Controllers\Operations\EventTaskPlanningController;
use App\Http\Controllers\Operations\MapCalibrationController;
use App\Http\Controllers\Operations\OrganizationController;
use App\Http\Controllers\Operations\ProposalController;
use App\Http\Controllers\Operations\ReservationController;
use App\Http\Controllers\Operations\SpaceController;
use App\Http\Controllers\Operations\TaskController;
use App\Http\Controllers\Operations\TenantFinanceController;
use App\Http\Controllers\Operations\TenantWorkerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'operational'])
    ->prefix('operations')
    ->name('operations.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard');

        Route::get('event-requests', [EventRequestController::class, 'index'])->name('event-requests.index');
        Route::get('event-requests/{eventRequest}', [EventRequestController::class, 'show'])->name('event-requests.show');
        Route::patch('event-requests/{eventRequest}', [EventRequestController::class, 'update'])->name('event-requests.update');
        Route::post('event-requests/{eventRequest}/convert', [EventRequestController::class, 'convert'])->name('event-requests.convert');
        Route::post('event-requests/{eventRequest}/reject', [EventRequestController::class, 'reject'])->name('event-requests.reject');

        Route::get('events', [EventController::class, 'index'])->name('events.index');
        Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::patch('events/{event}', [EventController::class, 'update'])->name('events.update');

        // AI splits an approved event into tasks and assigns them by role.
        Route::post('events/{event}/plan-tasks', [EventTaskPlanningController::class, 'store'])->name('events.plan-tasks');

        Route::get('events/{event}/reservations', [ReservationController::class, 'index'])->name('events.reservations.index');
        Route::post('events/{event}/reservations', [ReservationController::class, 'store'])->name('events.reservations.store');
        Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

        Route::get('spaces', [SpaceController::class, 'index'])->name('spaces.index');
        Route::get('spaces/{space}', [SpaceController::class, 'show'])->name('spaces.show');

        // Pin venues onto the Pyramid floor plan (one-time calibration).
        // "Manage boring things": AI-assisted bulk emails.
        Route::get('manage-boring-things', [EmailController::class, 'index'])->name('manage-boring-things');
        Route::post('emails/generate', [EmailController::class, 'generate'])->name('emails.generate');
        Route::post('emails/send', [EmailController::class, 'send'])->name('emails.send');

        Route::get('map-calibration', [MapCalibrationController::class, 'index'])->name('map-calibration');
        Route::post('map-calibration/plan', [MapCalibrationController::class, 'uploadPlan'])->name('map-calibration.plan');
        Route::patch('spaces/{space}/geometry', [MapCalibrationController::class, 'update'])->name('spaces.geometry.update');

        Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::patch('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

        Route::get('conflicts', [ConflictController::class, 'index'])->name('conflicts.index');
        Route::get('conflicts/{conflict}', [ConflictController::class, 'show'])->name('conflicts.show');
        Route::post('conflicts/{conflict}/resolve', [ConflictController::class, 'resolve'])->name('conflicts.resolve');

        Route::get('proposals', [ProposalController::class, 'index'])->name('proposals.index');
        Route::post('proposals', [ProposalController::class, 'store'])->name('proposals.store');
        Route::get('proposals/{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
        Route::patch('proposals/{proposal}', [ProposalController::class, 'update'])->name('proposals.update');
        Route::post('proposals/{proposal}/line-items', [ProposalController::class, 'storeLineItem'])->name('proposals.line-items.store');
        Route::delete('proposals/{proposal}/line-items/{lineItem}', [ProposalController::class, 'destroyLineItem'])->name('proposals.line-items.destroy');
        Route::post('proposals/{proposal}/submit', [ProposalController::class, 'submit'])->name('proposals.submit');
        Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve'])->name('proposals.approve');
        Route::post('proposals/{proposal}/reject', [ProposalController::class, 'reject'])->name('proposals.reject');

        Route::get('alerts', [AlertController::class, 'index'])->name('alerts.index');
        Route::post('alerts', [AlertController::class, 'store'])->name('alerts.store');
        Route::patch('alerts/{alert}/read', [AlertController::class, 'markRead'])->name('alerts.read');
        Route::patch('alerts/{alert}/dismiss', [AlertController::class, 'dismiss'])->name('alerts.dismiss');
        Route::patch('alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');

        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('event-requests/{eventRequest}/contacts', [EventContactController::class, 'index'])->name('event-requests.contacts.index');
        Route::post('event-requests/{eventRequest}/contacts', [EventContactController::class, 'store'])->name('event-requests.contacts.store');
        Route::patch('event-contacts/{eventContact}', [EventContactController::class, 'update'])->name('event-contacts.update');
        Route::delete('event-contacts/{eventContact}', [EventContactController::class, 'destroy'])->name('event-contacts.destroy');

        Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
        Route::patch('assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::post('events/{event}/asset-reservations', [AssetController::class, 'storeReservation'])->name('events.asset-reservations.store');
        Route::delete('asset-reservations/{assetReservation}', [AssetController::class, 'destroyReservation'])->name('asset-reservations.destroy');

        Route::get('blackout-windows', [BlackoutWindowController::class, 'index'])->name('blackout-windows.index');
        Route::post('blackout-windows', [BlackoutWindowController::class, 'store'])->name('blackout-windows.store');
        Route::patch('blackout-windows/{blackoutWindow}', [BlackoutWindowController::class, 'update'])->name('blackout-windows.update');
        Route::delete('blackout-windows/{blackoutWindow}', [BlackoutWindowController::class, 'destroy'])->name('blackout-windows.destroy');

        Route::get('organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
        Route::patch('organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');

        Route::middleware('tenant.manager')->group(function (): void {
            Route::get('team', [TenantWorkerController::class, 'index'])->name('team.index');
            Route::post('team', [TenantWorkerController::class, 'store'])->name('team.store');

            Route::get('finance', [TenantFinanceController::class, 'index'])->name('finance.index');
            Route::patch('finance/profile', [TenantFinanceController::class, 'updateProfile'])->name('finance.profile.update');
            Route::post('finance/payments', [TenantFinanceController::class, 'storePayment'])->name('finance.payments.store');
            Route::post('finance/expenses', [TenantFinanceController::class, 'storeExpense'])->name('finance.expenses.store');
        });
    });
