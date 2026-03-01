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
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails if neither user nor guest token is provided', function () {
            deleteJson(route('cart.destroy'))
                ->assertUnauthorized()
                ->assertJson(['message' => __('cart.errors.identification_missing')]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('can clear all items from the cart for an authenticated user', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $userCartItem = CartItem::factory()->count(7)->for($userCart)->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.destroy'))
                ->assertNoContent();

            $this->assertDatabaseHas('carts', ['id' => $userCart->id]);
            $this->assertDatabaseMissing('cart_items', ['cart_id' => $userCart->id]);
        });

        it('can clear all items from the cart for a guest user', function () {
            $guestCart = Cart::factory()->guest()->create();
            $guestCartItem = CartItem::factory()->count(2)->for($guestCart)->create();

            deleteJson(route('cart.destroy'), [],
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertNoContent();

            $this->assertDatabaseHas('carts', ['id' => $guestCart->id]);
            $this->assertDatabaseMissing('cart_items', ['cart_id' => $guestCart->id]);
        });

        it('returns no content if the authenticated user has no cart', function () {
            $user = User::factory()->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.destroy'))
                ->assertNoContent();
        });

        it('returns no content if the guest user has no cart', function () {
            deleteJson(route('cart.destroy'), [],
                [config('cart.guest_token_header') => Str::uuid()->toString()]
            )
                ->assertNoContent();
        });

        it('ignores the request if the cart is expired', function () {
            $user = User::factory()->create();
            $expiredCart = Cart::factory()->expired()->for($user)->create();
            $expiredCartItems = CartItem::factory()->count(3)->for($expiredCart)->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.destroy'))
                ->assertNoContent();

            $this->assertDatabaseHas('carts', ['id' => $expiredCart->id]);
            $this->assertDatabaseHas('cart_items', ['cart_id' => $expiredCart->id]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permission
    |--------------------------------------------------------------------------
    */
    describe('permission', function () {

        it('prevents clearing another user\'s cart', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $guestCart = Cart::factory()->guest()->create();

            deleteJson(route('cart.destroy'), [],
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertNoContent();

            $this->assertDatabaseHas('carts', ['id' => $guestCart->id]);
            $this->assertDatabaseHas('carts', ['id' => $userCart->id]);
        });
    });

    it('prioritizes the authenticated user over the guest header', function () {
        $user = User::factory()->create();
        $userCart = Cart::factory()->for($user)->create();
        $userCartItem = CartItem::factory()->count(2)->for($userCart)->create();
        $guestCart = Cart::factory()->guest()->create();
        $guestCartItem = CartItem::factory()->count(2)->for($guestCart)->create();

        Sanctum::actingAs($user);

        deleteJson(route('cart.destroy'), [],
            [config('cart.guest_token_header') => $guestCart->guest_token]
        )
            ->assertNoContent();

        $this->assertDatabaseMissing('cart_items', ['cart_id' => $userCart->id]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $guestCart->id]);
    });
})->group('cart');
