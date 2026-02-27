<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Cart\CartController;
use App\Http\Controllers\Api\Cart\CartItemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication actions
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(callback: function () {
    // Me action
    Route::get('/me', MeController::class)
        ->middleware('auth:sanctum')
        ->name('auth.me');

    // Register action
    Route::post('/register', RegisterController::class)
        ->name('auth.register');

    // Login action
    Route::post('/login', LoginController::class)
        ->name('auth.login');

    // Logout action
    Route::delete('/logout', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('auth.logout');
});

Route::middleware('check.cart.owner')->prefix('cart')->group(callback: function () {
    // Cart index action
    Route::get('/', [CartController::class, 'index'])
        ->name('cart.index');

    // Cart destroy action
    Route::delete('/', [CartController::class, 'destroy'])
        ->name('cart.destroy');

    Route::prefix('items')->group(callback: function () {
        // Cart item store action
        Route::post('/', [CartItemController::class, 'store'])
            ->name('cart.item.store');

        // Cart item update action
        Route::patch('/{item}', [CartItemController::class, 'update'])
            ->name('cart.item.update');
    });
})
    ->scopeBindings();
