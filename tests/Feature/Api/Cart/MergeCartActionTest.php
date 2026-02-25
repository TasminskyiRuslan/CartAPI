<?php

use App\Actions\Cart\MergeCartAction;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MergeCartAction', function () {

    beforeEach(function () {
       $this->guestToken = Str::uuid()->toString();
       $this->user = User::factory()->create();
       $this->action = app(MergeCartAction::class);
    });

    it('returns if guest cart does not exist', function () {
        $this->action->handle($this->user->id, $this->guestToken);
        $this->assertDatabaseMissing('carts', ['user_id' => $this->user->id]);
    });

    it('deletes guest cart and returns if it is expired', function () {
        $guestCart = Cart::factory()->guest($this->guestToken)->createQuietly(['expires_at' => now()->subDay()]);
        $product = Product::factory()->create();
        $guestCartItem = CartItem::factory()->for($guestCart)->for($product)->create();

        $this->action->handle($this->user->id, $this->guestToken);

        $this->assertDatabaseMissing('carts', [
            'id' => $guestCart->id
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $guestCartItem->id
        ]);
    });

    it('assigns guest cart to user if user has no cart', function () {
        $guestCart = Cart::factory()->guest($this->guestToken)->create();

        $this->action->handle($this->user->id, $this->guestToken);

        $this->assertDatabaseHas('carts', [
            'id' => $guestCart->id,
            'user_id' => $this->user->id,
            'guest_token' => null,
        ]);
    });

    it('deletes user expired cart and assigns guest cart to user', function () {
        $product = Product::factory()->create();
        $expiredUserCart = Cart::factory()->for($this->user)->createQuietly(['expires_at' => now()->subDay()]);
        $userCartItem = CartItem::factory()->for($expiredUserCart)->for($product)->create();
        $guestCart = Cart::factory()->guest($this->guestToken)->create();
        $guestCartItem = CartItem::factory()->for($guestCart)->for($product)->create();

        $this->action->handle($this->user->id, $this->guestToken);

        $this->assertDatabaseMissing('carts', [
            'id' => $expiredUserCart->id
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $userCartItem->id
        ]);
        $this->assertDatabaseHas('carts', [
            'id' => $guestCart->id,
            'user_id' => $this->user->id,
            'guest_token' => null,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $guestCartItem->id,
            'cart_id' => $guestCart->id,
        ]);
    });

    it('merges same products, sums quantity and deletes guest cart', function () {
        $product = Product::factory()->create();
        $userCart = Cart::factory()->for($this->user)->create();
        $userCartItem = CartItem::factory()->for($userCart)->for($product)->create(['quantity' => 2]);
        $guestCart = Cart::factory()->guest($this->guestToken)->create();
        $guestCartItem = CartItem::factory()->for($guestCart)->for($product)->create(['quantity' => 3]);

        $this->action->handle($this->user->id, $this->guestToken);

        $this->assertDatabaseMissing('carts', [
            'id' => $guestCart->id
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $guestCartItem->id
        ]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $userCartItem->id,
            'cart_id' => $userCart->id,
            'product_id' => $userCartItem->product_id,
            'quantity' => min($userCartItem->quantity + $guestCartItem->quantity, config('cart.max_quantity')),
        ]);
    });

    it('merges different products into user cart and deletes guest cart', function () {
        $products = Product::factory()->count(2)->create();
        $userCart = Cart::factory()->for($this->user)->create();
        $userCartItem = CartItem::factory()->for($userCart)->for($products[0])->create();
        $guestCart = Cart::factory()->guest($this->guestToken)->create();
        $guestCartItem = CartItem::factory()->for($guestCart)->for($products[1])->create();

        $this->action->handle($this->user->id, $this->guestToken);

        $this->assertDatabaseMissing('carts', [
            'id' => $guestCart->id
        ]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $userCartItem->id,
            'cart_id' => $userCart->id,
            'product_id' => $userCartItem->product_id,
            'quantity' => $userCartItem->quantity,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $guestCartItem->id,
            'cart_id' => $userCart->id,
            'product_id' => $guestCartItem->product_id,
            'quantity' => $guestCartItem->quantity,
        ]);
    });

    it('merges mixed products into user cart and deletes guest cart', function () {
        $products = Product::factory()->count(3)->create();
        $userCart = Cart::factory()->for($this->user)->create();
        $userCartItems = [
            CartItem::factory()->for($userCart)->for($products[0])->create(['quantity' => 2]),
            CartItem::factory()->for($userCart)->for($products[1])->create(['quantity' => 1]),
        ];
        $guestCart = Cart::factory()->guest($this->guestToken)->create();
        $guestCartItems = [
            CartItem::factory()->for($guestCart)->for($products[0])->create(['quantity' => 3]),
            CartItem::factory()->for($guestCart)->for($products[2])->create(['quantity' => 4]),
        ];

        $this->action->handle($this->user->id, $this->guestToken);

        $this->assertDatabaseMissing('carts', [
            'id' => $guestCart->id
        ]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $userCartItems[0]->id,
            'cart_id' => $userCart->id,
            'product_id' => $userCartItems[0]->product_id,
            'quantity' => min($userCartItems[0]->quantity + $guestCartItems[0]->quantity, config('cart.max_quantity')),
        ]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $guestCartItems[1]->id,
            'cart_id' => $userCart->id,
            'product_id' => $guestCartItems[1]->product_id,
            'quantity' => $guestCartItems[1]->quantity,
        ]);
    });

    it('refreshes user cart expiration and deletes empty guest cart without modifying user items', function () {
        $initialExpiration = now()->addHour();
        $product = Product::factory()->create();
        $userCart = Cart::factory()->for($this->user)->createQuietly(['expires_at' => $initialExpiration]);
        $userCartItem = CartItem::factory()->for($userCart)->for($product)->create(['quantity' => 2]);
        $guestCart = Cart::factory()->guest($this->guestToken)->create();

        $this->action->handle($this->user->id, $this->guestToken);

        $this->assertDatabaseMissing('carts', [
            'id' => $guestCart->id
        ]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $userCartItem->id,
            'cart_id' => $userCart->id,
            'quantity' => $userCartItem->quantity,
        ]);

        $userCart->refresh();
        expect($userCart->expires_at->gt($initialExpiration))->toBeTrue();
    });

    it('is idempotent when executed multiple times', function () {
        $product = Product::factory()->create();
        $guestCart = Cart::factory()->guest($this->guestToken)->create();
        $guestCartItem = CartItem::factory()->for($guestCart)->for($product)->create(['quantity' => 2]);

        $this->action->handle($this->user->id, $this->guestToken);
        $this->action->handle($this->user->id, $this->guestToken);

        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseHas('cart_items', [
            'id' => $guestCartItem->id,
            'product_id' => $guestCartItem->product_id,
            'quantity' => $guestCartItem->quantity,
        ]);
    });

    it('does not exceed 99 units per product during merge', function () {
        $product = Product::factory()->create();
        $userCart = Cart::factory()->for($this->user)->create();
        $userCartItem = CartItem::factory()->for($userCart)->for($product)->create(['quantity' => 60]);
        $guestCart = Cart::factory()->guest($this->guestToken)->create();
        $guestCartItem = CartItem::factory()->for($guestCart)->for($product)->create(['quantity' => 50]);

        $this->action->handle($this->user->id, $this->guestToken);

        $this->assertDatabaseMissing('carts', [
            'id' => $guestCart->id
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $guestCartItem->id
        ]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $userCartItem->id,
            'product_id' => $userCartItem->product_id,
            'quantity' => config('cart.max_quantity'),
        ]);
    });
})->group('cart');
