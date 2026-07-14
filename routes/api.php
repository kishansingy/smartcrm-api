<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Call\CallController;
use App\Http\Controllers\Api\V1\Call\CallSettingsController;
use App\Http\Controllers\Api\V1\Call\ExotelWebhookController;
use App\Http\Controllers\Api\V1\Call\RetellWebhookController;
use App\Http\Controllers\Api\V1\Contact\ContactController;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\Lead\LeadController;
use App\Http\Controllers\Api\V1\Pipeline\DealController;
use App\Http\Controllers\Api\V1\Pipeline\PipelineController;
use App\Http\Controllers\Api\V1\Task\TaskController;
use App\Http\Controllers\Api\V1\WhatsApp\WhatsAppController;
use App\Http\Controllers\Api\V1\WhatsApp\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public auth routes
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login',    [AuthController::class, 'login'])->name('login');
    });

    // Exotel webhook — public, no auth (Exotel posts here when call ends)
    Route::post('calls/webhook/exotel', [ExotelWebhookController::class, 'handle'])
        ->name('calls.webhook.exotel');

    // Retell webhook — public, no auth (Retell posts call events here)
    Route::post('calls/webhook/retell', [RetellWebhookController::class, 'handle'])
        ->name('calls.webhook.retell');

    // Protected routes
    Route::middleware(['auth:sanctum', 'tenant.active'])->group(function () {

        // Auth
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('me',     [AuthController::class, 'me'])->name('me');
        });

        // Dashboard
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('overview',      [DashboardController::class, 'overview'])->name('overview');
            Route::get('revenue-chart', [DashboardController::class, 'revenueChart'])->name('revenue-chart');
            Route::get('lead-charts',   [DashboardController::class, 'leadCharts'])->name('lead-charts');
            Route::get('deal-charts',   [DashboardController::class, 'dealCharts'])->name('deal-charts');
            Route::post('refresh',      [DashboardController::class, 'refresh'])->name('refresh');
        });

        // Leads
        Route::prefix('leads')->name('leads.')->group(function () {
            Route::get('stats',        [LeadController::class, 'stats'])->name('stats');
            Route::post('bulk-assign', [LeadController::class, 'bulkAssign'])->name('bulk-assign');
            Route::post('bulk-status', [LeadController::class, 'bulkStatus'])->name('bulk-status');
        });
        Route::apiResource('leads', LeadController::class);

        // Contacts
        Route::prefix('contacts')->name('contacts.')->group(function () {
            Route::get('stats',              [ContactController::class, 'stats'])->name('stats');
            Route::delete('bulk',            [ContactController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::post('{contact}/notes',   [ContactController::class, 'storeNote'])->name('notes.store');
            Route::delete('{contact}/notes/{note}', [ContactController::class, 'destroyNote'])->name('notes.destroy');
        });
        Route::apiResource('contacts', ContactController::class);

        // Pipelines
        Route::prefix('pipelines')->name('pipelines.')->group(function () {
            Route::post('{pipeline}/reorder-stages', [PipelineController::class, 'reorderStages'])->name('reorder-stages');
        });
        Route::apiResource('pipelines', PipelineController::class);

        // Deals
        Route::prefix('deals')->name('deals.')->group(function () {
            Route::get('stats',         [DealController::class, 'stats'])->name('stats');
            Route::get('kanban',        [DealController::class, 'kanban'])->name('kanban');
            Route::post('{deal}/move',  [DealController::class, 'move'])->name('move');
        });
        Route::apiResource('deals', DealController::class);

        // Tasks
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('upcoming',                      [TaskController::class, 'upcoming'])->name('upcoming');
            Route::get('overdue',                       [TaskController::class, 'overdue'])->name('overdue');
            Route::get('my-stats',                      [TaskController::class, 'myStats'])->name('my-stats');
            Route::get('stats',                         [TaskController::class, 'tenantStats'])->name('stats');
            Route::post('{task}/complete',              [TaskController::class, 'complete'])->name('complete');
            Route::post('{task}/comments',              [TaskController::class, 'storeComment'])->name('comments.store');
            Route::delete('{task}/comments/{comment}',  [TaskController::class, 'destroyComment'])->name('comments.destroy');
        });
        Route::apiResource('tasks', TaskController::class);

        // Calls & AI Agent
        Route::prefix('calls')->name('calls.')->group(function () {
            Route::get('stats',                         [CallController::class, 'stats'])->name('stats');
            Route::get('report',                        [CallController::class, 'report'])->name('report');
            Route::get('retell-agents',                 [CallController::class, 'retellAgents'])->name('retell-agents');
            Route::get('contact/{contact}',             [CallController::class, 'contactHistory'])->name('contact-history');
            Route::post('{callLog}/summary',            [CallController::class, 'generateSummary'])->name('generate-summary');
            Route::post('bulk',                         [CallController::class, 'bulkCall'])->name('bulk');
            Route::get('settings',                      [CallSettingsController::class, 'show'])->name('settings.show');
            Route::put('settings',                      [CallSettingsController::class, 'update'])->name('settings.update');
        });
        Route::apiResource('calls', CallController::class)->only(['index', 'show', 'store', 'update']);

        // WhatsApp
        Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
            Route::get('conversations',                                    [WhatsAppController::class, 'conversations'])->name('conversations');
            Route::get('conversations/{conversation}/messages',            [WhatsAppController::class, 'messages'])->name('messages');
            Route::post('conversations/{conversation}/mark-read',          [WhatsAppController::class, 'markRead'])->name('mark-read');
            Route::get('stats',                                            [WhatsAppController::class, 'stats'])->name('stats');
            Route::post('send',                                            [WhatsAppController::class, 'send'])->name('send');
            Route::post('broadcast',                                       [WhatsAppController::class, 'broadcast'])->name('broadcast');
            Route::get('messages',                                         [WhatsAppController::class, 'messageLog'])->name('message-log');
            Route::get('templates',                                        [WhatsAppController::class, 'templates'])->name('templates');
            Route::post('templates/sync',                                  [WhatsAppController::class, 'syncTemplates'])->name('templates.sync');
        });

        // Users & Roles
        Route::get('users/roles',  [UserController::class, 'roles'])->name('users.roles');
        Route::apiResource('users', UserController::class);

    });

    // WhatsApp webhook — public (Meta verification + incoming messages)
    Route::get('whatsapp/webhook',  [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
    Route::post('whatsapp/webhook', [WhatsAppWebhookController::class, 'receive'])->name('whatsapp.webhook.receive');

});
