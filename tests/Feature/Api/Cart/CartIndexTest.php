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
    describe('validation', function () {

        it('fails if neither user nor guest token is provided', function () {
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

        it('can retrieve the cart for an authenticated user', function () {
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

        it('returns an empty cart structure if an authenticated user has no cart', function () {
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

        it('can retrieve the cart for a guest user', function () {
            $guestCart = Cart::factory()->guest()->create();
            $guestCartItems = CartItem::factory()->count(2)->for($guestCart)->create();

            getJson(route('cart.index'),
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', $guestCart->id)
                ->assertJsonCount($guestCartItems->count(), 'data.items');
        });

        it('returns empty items for guest user with empty cart', function () {
            $guestCart = Cart::factory()->guest()->create();

            getJson(route('cart.index'),
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', $guestCart->id)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('returns an empty cart structure if a guest user has no cart', function () {
            getJson(route('cart.index'),
                [config('cart.guest_token_header') => Str::uuid()->toString()]
            )
                ->assertOk()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('ignores the cart if it is expired for an authenticated user', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->expired()->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonPath('data.id', null)
                ->assertJsonPath('data.items', [])
                ->assertJsonPath('data.total_items', 0)
                ->assertJsonPath('data.total_price', '0.00');
        });

        it('ignores the cart if it is expired for a guest user', function () {
            $guestCart = Cart::factory()->guest()->expired()->create();

            getJson(route('cart.index'),
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
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
        it('prevents retrieving another user\'s cart', function () {
            $user = User::factory()->create();
            $anotherUser = User::factory()->create();
            $anotherUserCart = Cart::factory()->for($anotherUser)->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'))
                ->assertOk()
                ->assertJsonPath('data.id', null);
        });

        it('prevents retrieving another guest\'s cart', function () {
            $guestCart = Cart::factory()->guest()->create();

            getJson(route('cart.index'),
                [config('cart.guest_token_header') => Str::uuid()->toString()]
            )
                ->assertOk()
                ->assertJsonPath('data.id', null);
        });

        it('prioritizes the authenticated user over the guest header', function () {
            $user = User::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $guestCart = Cart::factory()->guest()->create();

            Sanctum::actingAs($user);

            getJson(route('cart.index'),
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertOk()
                ->assertJsonPath('data.id', $userCart->id);
        });
    });
})->group('cart');
