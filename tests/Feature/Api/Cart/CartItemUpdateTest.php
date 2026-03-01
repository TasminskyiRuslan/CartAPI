<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

describe('CartItemController -> update', function () {

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {

        it('fails if required fields are missing', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $userCartItem = CartItem::factory()->for($userCart)->create();

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', $userCartItem), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });

        it('fails if the quantity is not an integer', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $userCartItem = CartItem::factory()->for($userCart)->create();

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', $userCartItem), ['quantity' => 'invalid'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });

        it('fails if the quantity is less than 1', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $userCartItem = CartItem::factory()->for($userCart)->create();

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', $userCartItem), ['quantity' => 0])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });

        it('fails if the quantity exceeds the maximum limit', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $userCartItem = CartItem::factory()->for($userCart)->create();
            $max = config('cart.max_quantity');

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', $userCartItem), ['quantity' => $max + 1])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });

        it('fails if the cart item does not exist', function () {
            $user = User::factory()->create();

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', 99999), ['quantity' => 50])
                ->assertNotFound();
        });

        it('fails if the cart is expired', function () {
            $user = User::factory()->create();
            $expiredUserCart = Cart::factory()->for($user)->expired()->create();
            $expiredCartItem = CartItem::factory()->for($expiredUserCart)->create();

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', $expiredCartItem), ['quantity' => 50])
                ->assertNotFound();
        });

        it('fails if the cart item does not belong to the current user\'s cart', function () {
            $user = User::factory()->create();
            $anotherUser = User::factory()->create();
            $anotherUserCart = Cart::factory()->for($anotherUser)->create();
            $anotherCartItem = CartItem::factory()->for($anotherUserCart)->create();

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', $anotherCartItem), ['quantity' => 50])
                ->assertNotFound();
        });

        it('fails if the cart item does not belong to the current guest\'s cart', function () {
            $guestCart = Cart::factory()->guest()->create();
            $anotherGuestCart = Cart::factory()->guest()->create();
            $anotherCartItem = CartItem::factory()->for($anotherGuestCart)->create();

            patchJson(route('cart.item.update', $anotherCartItem), ['quantity' => 50],
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertNotFound();
        });

        it('fails if neither user nor guest token is provided', function () {
            patchJson(route('cart.item.update', 99999))
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

        it('can update a cart item for an authenticated user', function () {
            $user = User::factory()->create();
            $quantity = 5;
            $userCart = Cart::factory()->for($user)->create();
            $userCartItem = CartItem::factory()->for($userCart)->create();
            $expectedTotalPrice = bcmul($userCartItem->price_snapshot, $quantity, 2);

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', $userCartItem), ['quantity' => $quantity])
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.items.0.quantity', $quantity)
                ->assertJsonPath('data.items.0.total_price', $expectedTotalPrice)
                ->assertJsonPath('data.total_items', $quantity)
                ->assertJsonPath('data.total_price', $expectedTotalPrice);

            $this->assertDatabaseHas('cart_items', [
                'id' => $userCartItem->id,
                'quantity' => $quantity,
            ]);
        });

        it('can update a cart item for a guest user', function () {
            $quantity = 5;
            $guestCart = Cart::factory()->guest()->create();
            $guestCartItem = CartItem::factory()->for($guestCart)->create();
            $expectedTotalPrice = bcmul($guestCartItem->price_snapshot, $quantity, 2);

            patchJson(route('cart.item.update', $guestCartItem), ['quantity' => $quantity],
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.items.0.quantity', $quantity)
                ->assertJsonPath('data.items.0.total_price', $expectedTotalPrice)
                ->assertJsonPath('data.total_items', $quantity)
                ->assertJsonPath('data.total_price', $expectedTotalPrice);

            $this->assertDatabaseHas('cart_items', [
                'id' => $guestCartItem->id,
                'quantity' => $quantity,
            ]);
        });

        it('refreshes the cart expiration date after updating an item', function () {
            $user = User::factory()->create();
            $quantity = 5;
            $initialExpiration = now()->addHour();
            $userCart = Cart::factory()->for($user)->create(['expires_at' => $initialExpiration]);
            $userCartItem = CartItem::factory()->for($userCart)->create();

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', $userCartItem), ['quantity' => $quantity])
                ->assertOk();

            expect($userCart->refresh()->expires_at->gt($initialExpiration))->toBeTrue();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permission
    |--------------------------------------------------------------------------
    */
    describe('permission', function () {
        it('prioritizes the authenticated user over the guest header', function () {
            $user = User::factory()->create();
            $guestCart = Cart::factory()->guest()->create();
            $guestCartItem = CartItem::factory()->for($guestCart)->create();

            Sanctum::actingAs($user);

            patchJson(route('cart.item.update', $guestCartItem), ['quantity' => 2],
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertNotFound();
        });
    });
})->group('cart');
