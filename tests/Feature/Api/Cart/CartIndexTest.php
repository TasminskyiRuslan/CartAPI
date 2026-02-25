<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe('CartController -> index', function () {

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('returns cart of authenticated user with items', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $userCartItems = CartItem::factory()->count(2)->for($userCart)->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', $userCart->id)
                ->assertJsonCount($userCartItems->count(), 'data.items');
        });

        it('returns empty items for authenticated user with empty cart', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', $userCart->id)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('returns empty cart structure for authenticated user without cart', function () {
            $user = User::factory()->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('returns cart of guest user with items', function () {
            $guestCart = Cart::factory()->guest(Str::uuid()->toString())->create();
            $guestCartItems = CartItem::factory()->count(2)->for($guestCart)->create();

            getJson(route('cart.index'), [config('cart.guest_header') => $guestCart->guest_token])
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', $guestCart->id)
                ->assertJsonCount($guestCartItems->count(), 'data.items');
        });

        it('returns empty items for guest user with empty cart', function () {
            $guestCart = Cart::factory()->guest(Str::uuid()->toString())->create();

            getJson(route('cart.index'), [config('cart.guest_header') => $guestCart->guest_token])
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', $guestCart->id)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });


        it('returns empty cart structure for guest user without cart', function () {
            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('does not return expired cart for authenticated user', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->createQuietly(['expires_at' => now()->subDay()]);

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('does not return expired cart for guest user', function () {
            $guestCart = Cart::factory()->guest(Str::uuid()->toString())->createQuietly(['expires_at' => now()->subDay()]);

            getJson(route('cart.index'), [config('cart.guest_header') => $guestCart->guest_token])
                ->assertOk()
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });
    });
})->group('cart');
