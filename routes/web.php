<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\HoldController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReturnController;
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

        Route::get('/books', [BookController::class, 'index'])
            ->name('books.index');

        // Book management restricted to authorized users
        Route::middleware('can:manage-books')->group(function () {
            Route::get('/books/create', [BookController::class, 'create'])
                ->name('books.create');

            Route::post('/books', [BookController::class, 'store'])
                ->name('books.store');
        });

        Route::get('/members', [MemberController::class, 'index'])
            ->name('members.index');

        // Member management restricted to authorized users
        Route::middleware('can:manage-members')->group(function () {
            Route::get('/members/create', [MemberController::class, 'create'])
                ->name('members.create');

            Route::post('/members', [MemberController::class, 'store'])
                ->name('members.store');
        });

        // Checkout form and submission security
        Route::middleware('can:process-circulation')->group(function () {

            Route::get('/checkout', [CheckoutController::class, 'index'])
                ->name('checkout.index');

            Route::post('/checkout', [CheckoutController::class, 'store'])
                ->name('checkout.store');

            // Return book form and submission security
            Route::get('/returns', [ReturnController::class, 'index'])
                ->name('returns.index');

            Route::post('/returns', [ReturnController::class, 'store'])
                ->name('returns.store');
            
            Route::get('/holds', [HoldController::class, 'index'])
                ->name('holds.index');

            Route::post('/holds', [HoldController::class, 'store'])
                ->name('holds.store');

            Route::patch('/holds/{hold_id}/cancel', [HoldController::class, 'cancel'])
                ->whereNumber('hold_id')
                ->name('holds.cancel');            

        });

        Route::view('/reports', 'reports.index')
            ->middleware('can:run-reports')
            ->name('reports.index');
    });
});
