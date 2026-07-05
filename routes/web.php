<?php

use App\Http\Controllers\Admin\CheckInController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicController::class)->name('home');
Route::get('/register', [RegistrationController::class, 'create'])->name('register');
Route::post('/register', [RegistrationController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('register.store');
Route::get('/tickets/{ticketCode}', [TicketController::class, 'show'])->name('tickets.show');
Route::post('/tickets/resend', [TicketController::class, 'resend'])
    ->middleware('throttle:3,1')
    ->name('tickets.resend');

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('events', EventController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('payment-methods', PaymentMethodController::class)->except(['show']);
    Route::get('registrations', [AdminRegistrationController::class, 'index'])->name('registrations.index');
    Route::post('registrations/{registration}/verify', [AdminRegistrationController::class, 'verify'])->name('registrations.verify');
    Route::post('registrations/{registration}/reject', [AdminRegistrationController::class, 'reject'])->name('registrations.reject');
    Route::get('check-in', [CheckInController::class, 'index'])->name('check-in.index');
    Route::post('check-in', [CheckInController::class, 'store'])->name('check-in.store');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('general', [SettingController::class, 'editGeneral'])->name('general');
        Route::put('general', [SettingController::class, 'updateGeneral'])->name('general.update');
        Route::get('contact', [SettingController::class, 'editContact'])->name('contact');
        Route::put('contact', [SettingController::class, 'updateContact'])->name('contact.update');
        Route::get('smtp', [SettingController::class, 'editSmtp'])->name('smtp');
        Route::put('smtp', [SettingController::class, 'updateSmtp'])->name('smtp.update');
    });
});

require __DIR__.'/settings.php';
