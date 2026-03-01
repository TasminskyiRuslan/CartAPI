<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('MeController', function () {
    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('can retrieve the current authenticated user profile', function () {
            $user = User::factory()->create();

            Sanctum::actingAs($user);

            getJson(route('auth.me'))
                ->assertOk()
                ->assertJsonPath('data.email', $user->email)
                ->assertJsonStructure(['data' => userJsonStructure()]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permissions
    |--------------------------------------------------------------------------
    */
    describe('permissions', function () {
        it('prevents access for an unauthenticated user', function () {
            getJson(route('auth.me'))
                ->assertUnauthorized();
        });
    });
})->group('auth');
