<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\DashboardActionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'landing'])->name('home');
Route::get('/login', fn () => redirect('/auth/login'))->name('login');

Route::get('/features', [PageController::class, 'features'])->name('features');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/docs', [PageController::class, 'docs'])->name('docs');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login', [PageController::class, 'login'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [PageController::class, 'register'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/forgot-password', [PageController::class, 'forgot'])->name('forgot');
    Route::get('/verify-email', [PageController::class, 'verify'])->name('verify');
});

Route::prefix('app')->middleware('auth')->name('dashboard.')->group(function () {
    Route::get('/billing', [PageController::class, 'dashboard'])->defaults('screen', 'Subscription Billing')->name('billing');
    Route::get('/settings', [PageController::class, 'dashboard'])->defaults('screen', 'Settings')->name('settings');
    Route::get('/profile', [PageController::class, 'dashboard'])->defaults('screen', 'Profile')->name('profile');
    Route::post('/billing/subscription', [DashboardActionController::class, 'subscription'])->name('subscription.store');
    Route::post('/billing/checkout', [PaymentController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/success', [PaymentController::class, 'success'])->name('billing.success');
    Route::get('/billing/cancel', [PaymentController::class, 'cancel'])->name('billing.cancel');
    Route::post('/profile', [DashboardActionController::class, 'profile'])->name('profile.update');
    Route::post('/settings', [DashboardActionController::class, 'settings'])->name('settings.update');
    Route::post('/admin/workspaces/{workspace}/subscription', [DashboardActionController::class, 'adminWorkspaceSubscription'])->name('admin.subscription.update');

    Route::middleware('subscribed')->group(function () {
        Route::get('/dashboard', [PageController::class, 'dashboard'])->name('overview');
        Route::get('/inbox', [PageController::class, 'dashboard'])->defaults('screen', 'Inbox / Live Chat')->name('inbox');
        Route::get('/contacts', [PageController::class, 'dashboard'])->defaults('screen', 'Contacts CRM')->name('contacts');
        Route::get('/broadcasts', [PageController::class, 'dashboard'])->defaults('screen', 'Broadcast Campaigns')->name('broadcasts');
        Route::get('/automations', [PageController::class, 'dashboard'])->defaults('screen', 'AI Automations')->name('automations');
        Route::get('/training', [PageController::class, 'dashboard'])->defaults('screen', 'AI Training')->name('training');
        Route::get('/analytics', [PageController::class, 'dashboard'])->defaults('screen', 'Analytics')->name('analytics');
        Route::get('/team', [PageController::class, 'dashboard'])->defaults('screen', 'Team Management')->name('team');
        Route::get('/notifications', [PageController::class, 'dashboard'])->defaults('screen', 'Notifications')->name('notifications');
        Route::get('/integrations', [PageController::class, 'dashboard'])->defaults('screen', 'Integrations')->name('integrations');
        Route::get('/api-keys', [PageController::class, 'dashboard'])->defaults('screen', 'API Keys')->name('apiKeys');
        Route::get('/activity', [PageController::class, 'dashboard'])->defaults('screen', 'Activity Logs')->name('activity');
        Route::post('/contacts', [DashboardActionController::class, 'contact'])->name('contacts.store');
        Route::post('/contacts/{contact}/update', [DashboardActionController::class, 'updateContact'])->name('contacts.update');
        Route::post('/contacts/{contact}/stage', [DashboardActionController::class, 'contactStage'])->name('contacts.stage');
        Route::post('/contacts/{contact}/block', [DashboardActionController::class, 'blockContact'])->name('contacts.block');
        Route::post('/contacts/{contact}/notes', [DashboardActionController::class, 'contactNote'])->name('contacts.notes');
        Route::delete('/contacts/{contact}', [DashboardActionController::class, 'deleteContact'])->name('contacts.destroy');
        Route::post('/conversations/{conversation}/messages', [DashboardActionController::class, 'message'])->name('conversations.messages.store');
        Route::post('/conversations/{conversation}/read', [DashboardActionController::class, 'markConversationRead'])->name('conversations.read');
        Route::delete('/conversations/{conversation}', [DashboardActionController::class, 'deleteConversation'])->name('conversations.destroy');
        Route::post('/messages/status/sync', [DashboardActionController::class, 'syncMessageStatuses'])->name('messages.status.sync');
        Route::post('/messages/{message}/status', [DashboardActionController::class, 'messageStatus'])->name('messages.status');
        Route::delete('/messages/{message}', [DashboardActionController::class, 'deleteMessage'])->name('messages.destroy');
        Route::post('/team', [DashboardActionController::class, 'team'])->name('team.store');
        Route::post('/whatsapp-accounts', [DashboardActionController::class, 'whatsappAccount'])->name('whatsapp.store');
        Route::post('/automations', [DashboardActionController::class, 'automation'])->name('automations.store');
        Route::post('/broadcasts', [DashboardActionController::class, 'broadcast'])->name('broadcasts.store');
        Route::post('/training', [DashboardActionController::class, 'training'])->name('training.store');
        Route::post('/integrations', [DashboardActionController::class, 'integration'])->name('integrations.store');
        Route::post('/api-keys', [DashboardActionController::class, 'apiKey'])->name('apiKeys.store');
    });
});

Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');
