<?php

use App\Http\Controllers\Admin\ProblemController as AdminProblemController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProblemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public auth routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Email verification (can work without auth for the verify route)
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('throttle:6,1')
    ->name('verification.verify');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('dashboard', function () {
        return \Inertia\Inertia::render('Dashboard');
    })->middleware('verified')->name('dashboard');

    // Email verification (requires auth)
    Route::get('/email/verify', [EmailVerificationController::class, 'prompt'])
        ->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // User-facing problem routes
    Route::get('/problems', [ProblemController::class, 'index'])->name('problems.index');
    Route::get('/problems/{problem:slug}', [ProblemController::class, 'show'])->name('problems.show');

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', function () {
            return \Inertia\Inertia::render('Admin/Dashboard');
        })->name('dashboard');

        Route::resource('problems', AdminProblemController::class)->except(['show']);
    });
});
