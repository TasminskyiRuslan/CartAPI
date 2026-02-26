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

        it('deletes authenticated user cart with items', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $userCartItem = CartItem::factory()->count(7)->for($userCart)->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.destroy'))
                ->assertNoContent();

            $this->assertDatabaseMissing('carts', [
                'id' => $userCart->id,
            ]);
            $this->assertDatabaseMissing('cart_items', [
                'cart_id' => $userCart->id,
            ]);
        });

        it('deletes guest cart with items', function () {
            $guestCart = Cart::factory()->guest()->create();
            CartItem::factory()->count(2)->for($guestCart)->create();

            deleteJson(route('cart.destroy'), [], [config('cart.guest_header') => $guestCart->guest_token])
                ->assertNoContent();

            $this->assertDatabaseMissing('carts', [
                'id' => $guestCart->id,
            ]);
            $this->assertDatabaseMissing('cart_items', [
                'cart_id' => $guestCart->id,
            ]);
        });

        it('returns no content when authenticated user cart does not exist', function () {
            $user = User::factory()->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.destroy'))
                ->assertNoContent();
        });

        it('returns no content when guest cart does not exist', function () {
            deleteJson(route('cart.destroy'), [], [config('cart.guest_header') => Str::uuid()->toString()])
                ->assertNoContent();
        });

        it('returns no content if no authentication and no guest token provided', function () {
            deleteJson(route('cart.destroy'))
                ->assertNoContent();
        });

        it('deletes the cart even if it is already expired', function () {
            $user = User::factory()->create();
            $expiredCart = Cart::factory()->expired()->for($user)->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.destroy'))
                ->assertNoContent();

            $this->assertDatabaseMissing('carts', [
                'id' => $expiredCart->id,
            ]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permission
    |--------------------------------------------------------------------------
    */
    describe('permission', function () {

        it('does not delete cart belonging to another user', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $guestCart = Cart::factory()->guest()->create();

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
