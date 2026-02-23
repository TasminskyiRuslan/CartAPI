<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('CartController -> destroy', function () {

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {
        it('delete cart of authenticated user with items', function () {
            $user = User::factory()->create();
            $cart = Cart::factory()->for($user)->create();
            $items = CartItem::factory()->count(7)->for($cart)->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.destroy'))
                ->assertNoContent();

            $this->assertDatabaseMissing('carts', [
                'id' => $cart->id,
            ]);
            $this->assertDatabaseMissing('cart_items', [
                'cart_id' => $cart->id,
            ]);
        });

        it('delete cart of guest user with items', function () {
            $cart = Cart::factory()->create(['user_id' => null, 'guest_token' => Str::uuid()]);
            $items = CartItem::factory()->count(2)->for($cart)->create();

            deleteJson(route('cart.destroy'), [], [config('cart.guest_header') => $cart->guest_token])
                ->assertNoContent();

            $this->assertDatabaseMissing('carts', [
                'id' => $cart->id,
            ]);
            $this->assertDatabaseMissing('cart_items', [
                'cart_id' => $cart->id,
            ]);
        });

        it('returns no content even if authenticated user cart does not exist', function () {
            $user = User::factory()->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.destroy'))
                ->assertNoContent();
        });

        it('returns no content even if guest cart does not exist', function () {
            deleteJson(route('cart.destroy'), [], [config('cart.guest_header') => Str::uuid()])
                ->assertNoContent();
        });

        it('returns no content if no authentication and no guest token provided', function () {
            deleteJson(route('cart.destroy'))
                ->assertNoContent();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permission
    |--------------------------------------------------------------------------
    */
    describe('permission', function () {

        it('cannot delete cart belonging to another user', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $guestCart = Cart::factory()->create(['user_id' => null, 'guest_token' => Str::uuid()]);

            deleteJson(route('cart.destroy'), [], [config('cart.guest_header') => $guestCart->guest_token])
                ->assertNoContent();

            $this->assertDatabaseMissing('carts', [
                'id' => $guestCart->id,
            ]);
            $this->assertDatabaseHas('carts', [
                'id' => $userCart->id,
            ]);
        });
    });
})->group('cart');
