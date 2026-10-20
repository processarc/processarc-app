<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\ProcessInsightController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatContactController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\CompanyContactController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'marketing.home')->name('home');
Route::view('/product', 'marketing.platform')->name('product');
Route::redirect('/platform/nvidia', '/product', 301)->name('platform.legacy');
Route::view('/architecture', 'marketing.architecture')->name('architecture');
Route::view('/company', 'marketing.company')->name('company');
Route::view('/resources', 'marketing.resources')->name('resources');
Route::view('/privacy', 'marketing.legal', ['page' => 'privacy'])->name('legal.privacy');
Route::view('/terms', 'marketing.legal', ['page' => 'terms'])->name('legal.terms');
Route::view('/security', 'marketing.legal', ['page' => 'security'])->name('legal.security');
Route::view('/ai-governance', 'marketing.legal', ['page' => 'ai-governance'])->name('legal.ai-governance');
Route::get('/sitemap.xml', fn () => response()->view('marketing.sitemap')->header('Content-Type', 'application/xml'))->name('sitemap');
Route::post('/api/process-insights', ProcessInsightController::class)->middleware('throttle:10,1')->name('api.process-insights');
Route::post('/api/assistant', ChatController::class)->middleware('throttle:10,1')->name('api.assistant');
Route::post('/api/assistant/contact', ChatContactController::class)->middleware('throttle:3,10')->name('api.assistant.contact');
Route::post('/api/newsletter', NewsletterController::class)->middleware('throttle:3,10')->name('api.newsletter');
Route::post('/contact', CompanyContactController::class)->middleware('throttle:3,10')->name('contact.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('app')->name('app.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::middleware('can:manage-operations')->group(function () {
        Route::get('/processes/create', [ProcessController::class, 'create'])->name('processes.create');
        Route::post('/processes', [ProcessController::class, 'store'])->name('processes.store');
        Route::get('/processes/{process}/edit', [ProcessController::class, 'edit'])->name('processes.edit');
        Route::put('/processes/{process}', [ProcessController::class, 'update'])->name('processes.update');
        Route::delete('/processes/{process}', [ProcessController::class, 'destroy'])->name('processes.destroy');
        Route::post('/processes/{process}/stages', [WorkflowController::class, 'store'])->name('workflows.store');
        Route::patch('/stages/{stage}', [WorkflowController::class, 'update'])->name('workflows.update');
        Route::post('/processes/{process}/optimizations', [OpportunityController::class, 'store'])->name('optimizations.store');
        Route::post('/optimizations', [OpportunityController::class, 'storeFromWorkspace'])->name('optimizations.store-from-workspace');
        Route::patch('/optimizations/{opportunity}', [OpportunityController::class, 'update'])->name('optimizations.update');
        Route::post('/insights', InsightController::class)->name('insights.generate');
    });
    Route::resource('processes', ProcessController::class)->only(['index', 'show']);
    Route::get('/workflows', [WorkflowController::class, 'index'])->name('workflows.index');
    Route::get('/optimizations', [OpportunityController::class, 'index'])->name('optimizations.index');
    Route::get('/analysis', [DashboardController::class, 'analysis'])->name('analysis');
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
    Route::post('/insights', InsightController::class)->name('insights.generate');
});
