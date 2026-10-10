<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

// Incredibly limited functionality for anyone not logged in - can only see login page
Route::middleware('guest:web')
    ->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])
            ->name('login');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('login.store');
    });

Route::middleware('auth:web')->group(function() {
    // Any user is able to logout once logged in
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Any user is able to access the following routes once logged in
    Route::middleware('can:access-library')->group(function () {
        Route::view('/', 'welcome')
            ->name('home');

        Route::view('/books', 'books.index')
            ->name('books.index');

        Route::view('/members', 'members.index')
            ->name('members.index');

        // Checkout form and submission security
        Route::middleware('can:process-circulation')->group(function () {

            Route::get('/checkout', [CheckoutController::class, 'index'])
                ->name('checkout.index');

            Route::post('/checkout', [CheckoutController::class, 'store'])
                ->name('checkout.store');

        });

        Route::view('/reports', 'reports.index')
            ->middleware('can:run-reports')
            ->name('reports.index');
    });
});