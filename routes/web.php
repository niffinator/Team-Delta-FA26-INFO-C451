<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/checkout', [CheckoutController::class, 'index'])
    ->name('checkout.index');

Route::post('/checkout', [CheckoutController::class, 'store'])
    ->name('checkout.store');

Route::view('/books', 'books.index')
    ->name('books.index');

Route::view('/members', 'members.index')
    ->name('members.index');

Route::view('reports', 'reports.index')
    ->name('reports.index');