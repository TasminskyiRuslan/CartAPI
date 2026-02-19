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
            $cart = Cart::factory()->for($user)->create();
            $items = CartItem::factory()->count(2)->for($cart)->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => cartJsonStructure(),
                ])
                ->assertJsonPath('data.id', $cart->id)
                ->assertJsonCount($items->count(), 'data.items');
        });

        it('returns empty items for authenticated user with empty cart', function () {
            $user = User::factory()->create();
            $cart = Cart::factory()->for($user)->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => cartJsonStructure(),
                ])
                ->assertJsonPath('data.id', $cart->id)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('returns empty cart structure for authenticated user without cart', function () {
            $user = User::factory()->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => cartJsonStructure(),
                ])
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('returns cart of guest user with items', function () {
            $cart = Cart::factory()->create(['user_id' => null, 'guest_token' => Str::uuid()]);
            $items = CartItem::factory()->count(2)->for($cart)->create();

            getJson(route('cart.index'), [config('cart.cart_guest_header') => $cart->guest_token])
                ->assertOk()
                ->assertJsonStructure([
                    'data' => cartJsonStructure(),
                ])
                ->assertJsonPath('data.id', $cart->id)
                ->assertJsonCount($items->count(), 'data.items');
        });

        it('returns empty items for guest user with empty cart', function () {
            $cart = Cart::factory()->create(['user_id' => null, 'guest_token' => Str::uuid()]);

            getJson(route('cart.index'), [config('cart.cart_guest_header') => $cart->guest_token])
                ->assertOk()
                ->assertJsonStructure([
                    'data' => cartJsonStructure(),
                ])
                ->assertJsonPath('data.id', $cart->id)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });


        it('returns empty cart structure for guest user without cart', function () {
            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonStructure([
                    'data' => cartJsonStructure(),
                ])
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });
    });
})->group('cart');
