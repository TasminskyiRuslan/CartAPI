<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

describe('CartItemController -> destroy', function () {

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('returns not found for non-existing cart item', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            deleteJson(route('cart.item.destroy', 99999))
                ->assertNotFound();
        });

        it('returns not found when cart is expired', function () {
            $user = User::factory()->create();
            $expiredUserCart = Cart::factory()->for($user)->expired()->create();
            $expiredCartItem = CartItem::factory()->for($expiredUserCart)->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.item.destroy', $expiredCartItem))
                ->assertNotFound();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('deletes authenticated user cart item', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $userCartItem = CartItem::factory()->for($userCart)->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.item.destroy', $userCartItem))
                ->assertNoContent();

            $this->assertDatabaseMissing('cart_items', ['id' => $userCartItem->id]);
        });

        it('deletes guest cart item', function () {
            $guestCart = Cart::factory()->guest()->create();
            $guestCartItem = CartItem::factory()->for($guestCart)->create();

            deleteJson(route('cart.item.destroy', $guestCartItem), [], [config('cart.guest_token_header') => $guestCart->guest_token])
                ->assertNoContent();

            $this->assertDatabaseMissing('cart_items', ['id' => $guestCartItem->id]);
        });

        it('refreshes cart expiration when item is deleted', function () {
            $user = User::factory()->create();
            $initialExpiration = now()->addHour();
            $userCart = Cart::factory()->for($user)->createQuietly(['expires_at' => $initialExpiration]);
            $userCartItem = CartItem::factory()->for($userCart)->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.item.destroy', $userCartItem))
                ->assertNoContent();

            expect($userCart->refresh()->expires_at->gt($initialExpiration))->toBeTrue();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permission
    |--------------------------------------------------------------------------
    */
    describe('permission', function () {

        it('does not delete cart item belonging to another owner', function () {
            $user = User::factory()->create();
            $anotherUserCart = Cart::factory()->for(User::factory())->create();
            $anotherCartItem = CartItem::factory()->for($anotherUserCart)->create();

            Sanctum::actingAs($user);

            deleteJson(route('cart.item.destroy', $anotherCartItem))
                ->assertNotFound();
            $this->assertDatabaseHas('cart_items', [
                'id' => $anotherCartItem->id,
            ]);
        });
    });
})->group('cart');
