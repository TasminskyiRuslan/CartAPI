<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('LogoutController', function () {

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('logouts authenticated user and revokes token', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            deleteJson(route('auth.logout'))
                ->assertNoContent();

            expect($user->tokens()->count())->toBe(0);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('fails for unauthenticated user', function () {
            deleteJson(route('auth.logout'))
                ->assertUnauthorized();
        });
    });

})->group('auth');
