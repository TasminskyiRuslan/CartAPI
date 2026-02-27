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
    | validation
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        it('fails when no user and no guest token', function () {
            getJson(route('cart.index'))
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

        it('returns authenticated user cart with items', function () {
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

        it('returns guest cart with items', function () {
            $guestCart = Cart::factory()->guest()->create();
            $guestCartItems = CartItem::factory()->count(2)->for($guestCart)->create();

            getJson(route('cart.index'), [config('cart.guest_token_header') => $guestCart->guest_token])
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', $guestCart->id)
                ->assertJsonCount($guestCartItems->count(), 'data.items');
        });

        it('returns empty items for guest user with empty cart', function () {
            $guestCart = Cart::factory()->guest()->create();

            getJson(route('cart.index'), [config('cart.guest_token_header') => $guestCart->guest_token])
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', $guestCart->id)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('returns empty cart structure for guest user without cart', function () {
            getJson(route('cart.index'), [config('cart.guest_token_header') => Str::uuid()->toString()])
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('ignores expired cart for authenticated user', function () {
            $user = User::factory()->create();
            Cart::factory()->for($user)->expired()->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('ignores expired cart for guest user', function () {
            $guestCart = Cart::factory()->guest()->expired()->create();

            getJson(route('cart.index'), [config('cart.guest_token_header') => $guestCart->guest_token])
                ->assertOk()
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | permission
    |--------------------------------------------------------------------------
    */
    describe('permission', function () {
        it('prevents access to another user cart', function () {
            $user = User::factory()->create();
            $anotherUser = User::factory()->create();
            Cart::factory()->for($anotherUser)->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonPath('data.id', null);
        });

        it('does not return another guest cart', function () {
            Cart::factory()->guest()->create();

            getJson(route('cart.index'), [config('cart.guest_token_header') => Str::uuid()->toString()])
                ->assertOk()
                ->assertJsonPath('data.id', null);
        });

        it('prioritizes authenticated user cart and ignores guest header', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $guestCart = Cart::factory()->guest()->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'), [config('cart.guest_token_header') => $guestCart->guest_token])
                ->assertOk()
                ->assertJsonPath('data.id', $userCart->id);
        });
    });
})->group('cart');
