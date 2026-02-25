<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe('CartItemController -> store', function () {

    /*
    |--------------------------------------------------------------------------
    | validation
    |--------------------------------------------------------------------------
    */
    describe('validation', function () {
        it('fails when required fields are missing', function () {
            postJson(route('cart.item.store'), [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['product_id']);
        });

        it('fails when product does not exist', function () {
            postJson(route('cart.item.store'), ['product_id' => 99999])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['product_id']);
        });

        it('fails when quantity is less than 1', function () {
            $product = Product::factory()->create();

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => 0,
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });

        it('fails when quantity is not as integer', function () {
            $product = Product::factory()->create();

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => 'invalid',
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });

        it('fails when quantity exceeds the maximum limit (99)', function () {
            $product = Product::factory()->create();

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => 100,
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | success
    |--------------------------------------------------------------------------
    */
    describe('success', function () {

        beforeEach(function () {
            $this->user = User::factory()->create();
            $this->originalPrice = '500.00';
            $this->product = Product::factory()->create(['price' => $this->originalPrice]);
        });

        it('adds a new item with default quantity (1) when quantity is not provided', function () {
            Sanctum::actingAs($this->user);

            postJson(route('cart.item.store'), ['product_id' => $this->product->id])
                ->assertCreated()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.items.0.price_snapshot', $this->product->price)
                ->assertJsonPath('data.items.0.quantity', 1)
                ->assertJsonPath('data.items.0.total_price', $this->product->price)
                ->assertJsonPath('data.items.0.product.id', $this->product->id)
                ->assertJsonPath('data.total_items', 1)
                ->assertJsonPath('data.total_price', $this->product->price);
        });

        it('adds a new item and creates a new cart for an authenticated user', function () {
            $quantity = 4;
            $expectedTotalPrice = bcmul($this->product->price, $quantity, 2);

            Sanctum::actingAs($this->user);

            postJson(route('cart.item.store'), [
                'product_id' => $this->product->id,
                'quantity' => $quantity,
            ])
                ->assertCreated()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.items.0.price_snapshot', $this->product->price)
                ->assertJsonPath('data.items.0.quantity', $quantity)
                ->assertJsonPath('data.items.0.total_price', $expectedTotalPrice)
                ->assertJsonPath('data.items.0.product.id', $this->product->id)
                ->assertJsonPath('data.total_items', $quantity)
                ->assertJsonPath('data.total_price', $expectedTotalPrice);

            $this->assertDatabaseHas('carts', [
                'user_id' => $this->user->id,
                'guest_token' => null,
            ]);
            $this->assertDatabaseHas('cart_items', [
                'product_id' => $this->product->id,
                'price_snapshot' => $this->product->price,
                'quantity' => $quantity,
            ]);
        });

        it('adds a new item and creates a new cart for a guest user', function () {
            $guestToken = Str::uuid()->toString();
            $quantity = 4;
            $expectedTotalPrice = bcmul($this->product->price, $quantity, 2);

            postJson(route('cart.item.store'), [
                'product_id' => $this->product->id,
                'quantity' => $quantity,
            ], [config('cart.guest_header') => $guestToken])
                ->assertCreated()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.items.0.price_snapshot', $this->product->price)
                ->assertJsonPath('data.items.0.quantity', $quantity)
                ->assertJsonPath('data.items.0.total_price', $expectedTotalPrice)
                ->assertJsonPath('data.items.0.product.id', $this->product->id)
                ->assertJsonPath('data.total_items', $quantity)
                ->assertJsonPath('data.total_price', $expectedTotalPrice);

            $this->assertDatabaseHas('carts', [
                'user_id' => null,
                'guest_token' => $guestToken,
            ]);
            $this->assertDatabaseHas('cart_items', [
                'product_id' => $this->product->id,
                'price_snapshot' => $this->product->price,
                'quantity' => $quantity,
            ]);
        });

        it('adds a new item to an existing cart for an authenticated user without creating duplicates', function () {
            $userCart = Cart::factory()->for($this->user)->create();
            $quantity = 4;

            Sanctum::actingAs($this->user);

            postJson(route('cart.item.store'), [
                'product_id' => $this->product->id,
                'quantity' => $quantity,
            ])
                ->assertCreated();

            $this->assertDatabaseHas('cart_items', [
                'product_id' => $this->product->id,
                'quantity' => $quantity,
            ]);

            expect(Cart::whereUserId($this->user->id)->count())->toBe(1);
        });

        it('adds a new item to an existing cart for a guest user without creating duplicates', function () {
            $guestCart = Cart::factory()->guest()->create();
            $quantity = 4;

            postJson(route('cart.item.store'), [
                'product_id' => $this->product->id,
                'quantity' => $quantity,
            ], [config('cart.guest_header') => $guestCart->guest_token])
                ->assertCreated();

            $this->assertDatabaseHas('cart_items', [
                'product_id' => $this->product->id,
                'quantity' => $quantity,
            ]);

            expect(Cart::whereGuestToken($guestCart->guest_token)->count())->toBe(1);
        });

        it('updates the quantity of an existing item', function () {
            $userCart = Cart::factory()->for($this->user)->create();
            $initialQuantity = 2;
            $addedQuantity = 3;
            $expectedQuantity = $initialQuantity + $addedQuantity;
            $expectedTotalPrice = bcmul($this->product->price, $expectedQuantity, 2);

            $userCartItem = CartItem::factory()->for($userCart)->create([
                'product_id' => $this->product->id,
                'quantity' => $initialQuantity,
            ]);

            Sanctum::actingAs($this->user);

            postJson(route('cart.item.store'), [
                'product_id' => $this->product->id,
                'quantity' => $addedQuantity,
            ])
                ->assertOk()
                ->assertJsonPath('data.items.0.quantity', $expectedQuantity)
                ->assertJsonPath('data.items.0.total_price', $expectedTotalPrice)
                ->assertJsonPath('data.total_items', $expectedQuantity)
                ->assertJsonPath('data.total_price', $expectedTotalPrice);

            $this->assertDatabaseHas('cart_items', [
                'id' => $userCartItem->id,
                'quantity' => $expectedQuantity,
            ]);
        });

        it('does not change price snapshot when product price changes and quantity is updated', function () {
            $userCart = Cart::factory()->for($this->user)->create();
            $userCartItem = CartItem::factory()->for($userCart)->create([
                'product_id' => $this->product->id,
                'price_snapshot' => $this->originalPrice,
                'quantity' => 1,
            ]);

            $this->product->update(['price' => '700.00']);

            Sanctum::actingAs($this->user);

            postJson(route('cart.item.store'), [
                'product_id' => $this->product->id,
            ])
                ->assertOk()
                ->assertJsonPath('data.items.0.quantity', 2)
                ->assertJsonPath('data.items.0.total_price', bcmul($this->originalPrice, 2, 2))
                ->assertJsonPath('data.items.0.price_snapshot', $this->originalPrice)
                ->assertJsonPath('data.total_items', 2)
                ->assertJsonPath('data.total_price', bcmul($this->originalPrice, 2, 2));

            $userCartItem->refresh();

            expect($userCartItem->price_snapshot)->toBe($this->originalPrice);
        });

        it('deletes an expired cart and creates a new one before adding the item', function () {
            $expiredCart = Cart::factory()->for($this->user)->createQuietly(['expires_at' => now()->subDay()]);

            Sanctum::actingAs($this->user);

            postJson(route('cart.item.store'), ['product_id' => $this->product->id])
                ->assertCreated();

            $this->assertDatabaseMissing('carts', [
                'id' => $expiredCart->id,
            ]);
            $this->assertDatabaseHas('carts', [
                'user_id' => $this->user->id,
            ]);
        });

        it('refreshes the cart expiration date when a new item is added', function () {
            $initialExpiration = now()->addHour();
            $userCart = Cart::factory()->for($this->user)->createQuietly(['expires_at' => $initialExpiration]);

            Sanctum::actingAs($this->user);

            postJson(route('cart.item.store'), ['product_id' => $this->product->id])
                ->assertCreated();

            $userCart->refresh();

            expect($userCart->expires_at->gt($initialExpiration))->toBeTrue();
        });

        it('refreshes the cart expiration date when a new item is updated', function () {
            $initialExpiration = now()->addHour();
            $userCart = Cart::factory()->for($this->user)->createQuietly(['expires_at' => $initialExpiration]);
            $userCartItem = CartItem::factory()->for($userCart)->create(['product_id' => $this->product->id]);

            Sanctum::actingAs($this->user);

            postJson(route('cart.item.store'), ['product_id' => $this->product->id])
                ->assertOk();

            $userCart->refresh();

            expect($userCart->expires_at->gt($initialExpiration))->toBeTrue();
        });

        it('caps the quantity to 99 when cumulative addition exceeds the limit', function () {
            $userCart = Cart::factory()->for($this->user)->create();
            $userCartItem = CartItem::factory()->for($userCart)->create([
                'product_id' => $this->product->id,
                'quantity' => 90,
            ]);

            Sanctum::actingAs($this->user);

            postJson(route('cart.item.store'), [
                'product_id' => $this->product->id,
                'quantity' => 20,
            ])
                ->assertOk()
                ->assertJsonPath('data.items.0.quantity', config('cart.max_quantity'));

            $this->assertDatabaseHas('cart_items', [
                'id' => $userCartItem->id,
                'cart_id' => $userCart->id,
                'product_id' => $userCartItem->product_id,
                'quantity' => config('cart.max_quantity'),
            ]);
        });
    });
})->group('cart');
