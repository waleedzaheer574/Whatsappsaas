<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AutomationController;
use App\Http\Controllers\Api\V1\BroadcastController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ChatMessageController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\IntegrationController;
use App\Http\Controllers\Api\V1\TrainingSourceController;
use App\Http\Controllers\Api\V1\WhatsAppAccountController;
use App\Http\Controllers\Api\V1\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/health', fn () => ['success' => true, 'message' => 'ChatFlow AI API is online']);

    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::get('/whatsapp/{account}', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.verify');
        Route::post('/whatsapp/{account}', [WhatsAppWebhookController::class, 'receive'])->name('whatsapp.receive');
    });

    Route::middleware(['throttle:60,1'])->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::apiResource('whatsapp-accounts', WhatsAppAccountController::class)->only(['index', 'store', 'show', 'update']);
        Route::apiResource('contacts', ContactController::class);
        Route::apiResource('conversations', ConversationController::class)->only(['index', 'show', 'update']);
        Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage'])->name('conversations.messages.send');
        Route::apiResource('automations', AutomationController::class);
        Route::apiResource('broadcasts', BroadcastController::class);
        Route::apiResource('training-sources', TrainingSourceController::class);
        Route::apiResource('integrations', IntegrationController::class);
        Route::get('/analytics/summary', AnalyticsController::class)->name('analytics.summary');
    });

    Route::middleware(['throttle:60,1', 'api.key'])->group(function () {
        Route::post('/chats/messages', [ChatMessageController::class, 'store'])->name('chats.messages.store');
    });
});
