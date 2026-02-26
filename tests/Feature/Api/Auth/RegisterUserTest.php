<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('RegisterController', function () {

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('fails when required fields are missing', function () {
            postJson(route('auth.register'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        });

        it('fails when email is already taken', function () {
            $data = registrationPayload();

            User::factory()->create(['email' => $data['email']]);

            postJson(route('auth.register'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when email format is invalid', function () {
            postJson(route('auth.register'), registrationPayload(['email' => 'invalid-email']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('fails when password is too short', function () {
            postJson(route('auth.register'), registrationPayload([
                'password' => '123',
                'password_confirmation' => '123',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('fails when password confirmation does not match', function () {
            postJson(route('auth.register'), registrationPayload([
                'password_confirmation' => 'different',
            ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('registers user and returns access token', function () {
            $data = registrationPayload();

            postJson(route('auth.register'), $data)
                ->assertCreated()
                ->assertJsonPath('data.user.email', $data['email'])
                ->assertJsonStructure([
                    'data' => authJsonStructure()
                ]);

            $user = User::whereEmail($data['email'])->first();

            expect($user)->not->toBeNull()
                ->and(Hash::check($data['password'], $user->password))->toBeTrue();
        });
    });

})->group('auth');
