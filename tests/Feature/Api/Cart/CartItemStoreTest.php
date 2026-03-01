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

        it('fails if required fields are missing', function () {
            postJson(route('cart.item.store'), [],
                [config('cart.guest_token_header') => Str::uuid()->toString()]
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['product_id']);
        });

        it('fails if the product does not exist', function () {
            postJson(route('cart.item.store'), ['product_id' => 99999],
                [config('cart.guest_token_header') => Str::uuid()->toString()]
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['product_id']);
        });

        it('fails if the quantity is less than 1', function () {
            $product = Product::factory()->create();

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => 0,
            ], [config('cart.guest_token_header') => Str::uuid()->toString()])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });

        it('fails if the quantity is not an integer', function () {
            $product = Product::factory()->create();

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => 'invalid',
            ], [config('cart.guest_token_header') => Str::uuid()->toString()])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });

        it('fails if the quantity exceeds the maximum limit', function () {
            $product = Product::factory()->create();
            $max = config('cart.max_quantity');

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => $max + 1,
            ], [config('cart.guest_token_header') => Str::uuid()->toString()])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['quantity']);
        });

        it('fails if neither user nor guest token is provided', function () {
            $product = Product::factory()->create();

            postJson(route('cart.item.store'), ['product_id' => $product->id])
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

        it('adds a new item with default quantity if not provided', function () {
            $user = User::factory()->create();
            $product = Product::factory()->create(['price' => '500.00']);

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'), ['product_id' => $product->id])
                ->assertCreated()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.items.0.price_snapshot', $product->price)
                ->assertJsonPath('data.items.0.quantity', 1)
                ->assertJsonPath('data.items.0.total_price', $product->price)
                ->assertJsonPath('data.items.0.product.id', $product->id)
                ->assertJsonPath('data.total_items', 1)
                ->assertJsonPath('data.total_price', $product->price);
        });

        it('creates a new cart and adds an item for an authenticated user', function () {
            $user = User::factory()->create();
            $product = Product::factory()->create(['price' => '100.00']);
            $quantity = 4;
            $expectedTotalPrice = bcmul($product->price, $quantity, 2);

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ])
                ->assertCreated()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.items.0.price_snapshot', $product->price)
                ->assertJsonPath('data.items.0.quantity', $quantity)
                ->assertJsonPath('data.items.0.total_price', $expectedTotalPrice)
                ->assertJsonPath('data.items.0.product.id', $product->id)
                ->assertJsonPath('data.total_items', $quantity)
                ->assertJsonPath('data.total_price', $expectedTotalPrice);

            $this->assertDatabaseHas('carts', [
                'user_id' => $user->id,
                'guest_token' => null,
            ]);
            $this->assertDatabaseHas('cart_items', [
                'product_id' => $product->id,
                'price_snapshot' => $product->price,
                'quantity' => $quantity,
            ]);
        });

        it('creates a new cart and adds an item for a guest user', function () {
            $guestToken = Str::uuid()->toString();
            $product = Product::factory()->create(['price' => '500.00']);
            $quantity = 4;
            $expectedTotalPrice = bcmul($product->price, $quantity, 2);

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ], [config('cart.guest_token_header') => $guestToken])
                ->assertCreated()
                ->assertJsonStructure(['data' => cartJsonStructure()])
                ->assertJsonPath('data.items.0.price_snapshot', $product->price)
                ->assertJsonPath('data.items.0.quantity', $quantity)
                ->assertJsonPath('data.items.0.total_price', $expectedTotalPrice)
                ->assertJsonPath('data.items.0.product.id', $product->id)
                ->assertJsonPath('data.total_items', $quantity)
                ->assertJsonPath('data.total_price', $expectedTotalPrice);

            $this->assertDatabaseHas('carts', [
                'user_id' => null,
                'guest_token' => $guestToken,
            ]);
            $this->assertDatabaseHas('cart_items', [
                'product_id' => $product->id,
                'price_snapshot' => $product->price,
                'quantity' => $quantity,
            ]);
        });

        it('adds an item to an existing cart for an authenticated user without duplicating the cart', function () {
            $user = User::factory()->create();
            $product = Product::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $quantity = 4;

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ])
                ->assertCreated();

            $this->assertDatabaseHas('cart_items', [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);

            expect(Cart::whereUserId($user->id)->count())->toBe(1);
        });

        it('adds an item to an existing cart for a guest user without duplicating the cart', function () {
            $guestCart = Cart::factory()->guest()->create();
            $product = Product::factory()->create();
            $quantity = 4;

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ], [config('cart.guest_token_header') => $guestCart->guest_token])
                ->assertCreated();

            $this->assertDatabaseHas('cart_items', [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);

            expect(Cart::whereGuestToken($guestCart->guest_token)->count())->toBe(1);
        });

        it('increments the quantity of an existing item in the cart', function () {
            $user = User::factory()->create();
            $product = Product::factory()->create(['price' => '500.00']);
            $userCart = Cart::factory()->for($user)->create();
            $initialQuantity = 2;
            $addedQuantity = 3;
            $expectedQuantity = $initialQuantity + $addedQuantity;
            $expectedTotalPrice = bcmul($product->price, $expectedQuantity, 2);

            $userCartItem = CartItem::factory()->for($userCart)->create([
                'product_id' => $product->id,
                'quantity' => $initialQuantity,
            ]);

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
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

        it('preserves the original price snapshot when the product price changes', function () {
            $user = User::factory()->create();
            $originalPrice = '500.00';
            $product = Product::factory()->create(['price' => $originalPrice]);
            $userCart = Cart::factory()->for($user)->create();
            $userCartItem = CartItem::factory()->for($userCart)->create([
                'product_id' => $product->id,
                'price_snapshot' => $product->price,
                'quantity' => 1,
            ]);

            $product->update(['price' => '700.00']);

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
            ])
                ->assertOk()
                ->assertJsonPath('data.items.0.quantity', 2)
                ->assertJsonPath('data.items.0.total_price', bcmul($originalPrice, 2, 2))
                ->assertJsonPath('data.items.0.price_snapshot', $originalPrice)
                ->assertJsonPath('data.total_items', 2)
                ->assertJsonPath('data.total_price', bcmul($originalPrice, 2, 2));

            expect($userCartItem->refresh()->price_snapshot)->toBe($originalPrice);
        });

        it('replaces expired cart for an authenticated user when adding an item', function () {
            $user = User::factory()->create();
            $product = Product::factory()->create();
            $expiredCart = Cart::factory()->for($user)->expired()->create();

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'), ['product_id' => $product->id])
                ->assertCreated();

            $this->assertDatabaseMissing('carts', ['id' => $expiredCart->id]);
            $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        });

        it('replaces expired cart for guest user when adding an item', function () {
            $product = Product::factory()->create();
            $guestCart = Cart::factory()->guest()->expired()->create();

            postJson(route('cart.item.store'),
                ['product_id' => $product->id],
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertCreated();

            $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);
            $this->assertDatabaseHas('carts', ['guest_token' => $guestCart->guest_token]);
        });

        it('refreshes the cart expiration date after adding an item', function () {
            $user = User::factory()->create();
            $product = Product::factory()->create();
            $initialExpiration = now()->addHour();
            $userCart = Cart::factory()->for($user)->createQuietly(['expires_at' => $initialExpiration]);

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'), ['product_id' => $product->id])
                ->assertCreated();

            expect($userCart->refresh()->expires_at->gt($initialExpiration))->toBeTrue();
        });

        it('refreshes the cart expiration date after updating an item', function () {
            $user = User::factory()->create();
            $product = Product::factory()->create();
            $initialExpiration = now()->addHour();
            $userCart = Cart::factory()->for($user)->createQuietly(['expires_at' => $initialExpiration]);
            $userCartItem = CartItem::factory()->for($userCart)->create(['product_id' => $product->id]);

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'), ['product_id' => $product->id])
                ->assertOk();

            expect($userCart->refresh()->expires_at->gt($initialExpiration))->toBeTrue();
        });

        it('caps the quantity to the maximum limit during cumulative addition', function () {
            $user = User::factory()->create();
            $product = Product::factory()->create();
            $userCart = Cart::factory()->for($user)->create();
            $max = config('cart.max_quantity');
            $userCartItem = CartItem::factory()->for($userCart)->create([
                'product_id' => $product->id,
                'quantity' => 90,
            ]);

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'), [
                'product_id' => $product->id,
                'quantity' => 20,
            ])
                ->assertOk()
                ->assertJsonPath('data.items.0.quantity', $max);

            $this->assertDatabaseHas('cart_items', [
                'id' => $userCartItem->id,
                'cart_id' => $userCart->id,
                'product_id' => $userCartItem->product_id,
                'quantity' => $max,
            ]);
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
            $product = Product::factory()->create();
            $guestCart = Cart::factory()->guest()->create();

            Sanctum::actingAs($user);

            postJson(route('cart.item.store'),
                ['product_id' => $product->id],
                [config('cart.guest_token_header') => $guestCart->guest_token]
            )
                ->assertCreated();

            $this->assertDatabaseHas('cart_items', [
                'product_id' => $product->id,
                'cart_id' => Cart::whereUserId($user->id)->first()->id,
            ]);

            $this->assertDatabaseMissing('cart_items', ['cart_id' => $guestCart->id]);
        });
    });
})->group('cart');
