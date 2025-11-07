<?php

use App\Http\Controllers\Api\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Token management routes
Route::middleware('auth:sanctum')->prefix('auth')->name('api.auth.')->group(function () {
    Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
    Route::post('/token', [TokenController::class, 'store'])->name('token.store');
    Route::delete('/token/{id}', [TokenController::class, 'destroy'])->name('token.destroy');
});
