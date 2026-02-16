<?php

use App\Data\Auth\UserData;
use App\Http\Controllers\Api\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication actions
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Me action
    Route::get('/me', function (Request $request) {
        return UserData::from($request->user());
    })
        ->middleware('auth:sanctum')
        ->name('auth.me');

    // Register action
    Route::post('/register', RegisterController::class)
        ->name('auth.register');
});
