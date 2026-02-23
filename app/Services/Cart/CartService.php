<?php

namespace App\Services\Cart;

use App\Data\Cart\CartIdentifierData;
use App\Data\Cart\Requests\CreateCartItemData;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use DB;
use Throwable;

class CartService
{
    /**
     * Delete the cart for the given guest token if it exists.
     *
     * @param string $guestToken The guest token used to identify the cart to be deleted.
     * @return void
     */
    public function deleteForGuest(string $guestToken): void
    {
        $this->findForGuest($guestToken)?->delete();
    }

    /**
     * Delete the cart identified by the provided identifier data if it exists.
     *
     * @param CartIdentifierData $cartIdentifierData The data used to identify the cart to be deleted, which may include user information or a guest token.
     * @return void
     */
    public function delete(CartIdentifierData $cartIdentifierData): void
    {
        $this->find($cartIdentifierData)?->delete();
    }

    /**
     * Find an active cart for the given identifier data, which may include user information or a guest token.
     *
     * @param CartIdentifierData $cartIdentifierData The data used to identify the cart, which may include user information or a guest token.
     * @return Cart|null The found cart instance if it exists and is active; otherwise, null.
     */
    public function find(CartIdentifierData $cartIdentifierData): ?Cart
    {
        return Cart::forOwner($cartIdentifierData)->active()->first();
    }

    /**
     * Find an active cart for the given guest token.
     *
     * @param string $guestToken The guest token for which to find the cart.
     * @return Cart|null The found cart instance if it exists and is active; otherwise, null.
     */
    public function findForGuest(string $guestToken): ?Cart
    {
        return Cart::forGuest($guestToken)->active()->first();
    }

    /**
     * Delete the cart for the given user if it exists.
     *
     * @param User $user The user for whom to delete the cart.
     * @return void
     */
    public function deleteForUser(User $user): void
    {
        $this->findForUser($user)?->delete();
    }

    /**
     * Find an active cart for the given user.
     *
     * @param User $user The user for whom to find the cart.
     * @return Cart|null The found cart instance if it exists and is active; otherwise, null.
     */
    public function findForUser(User $user): ?Cart
    {
        return Cart::forUser($user)->active()->first();
    }

    /**
     * Find an existing cart for the given identifier data or create a new one if it doesn't exist or is expired.
     *
     * @param CartIdentifierData $cartIdentifierData The data used to identify the cart, which may include user information or a guest token.
     * @return Cart The found or newly created cart instance.
     * @throws Throwable If any error occurs during the database transaction.
     */
    public function findOrCreate(CartIdentifierData $cartIdentifierData): Cart
    {
        return DB::transaction(function () use ($cartIdentifierData) {
            $cart = Cart::forOwner($cartIdentifierData)->first();

            if (!$cart) {
                $cart = $this->create($cartIdentifierData);
            } elseif ($cart->isExpired()) {
                $cart->delete();
                $cart = $this->create($cartIdentifierData);
            }

            return $cart;
        });
    }

    /**
     * Create a new cart based on the provided identifier data.
     *
     * @param CartIdentifierData $cartIdentifierData The data used to create the cart, which may include user information or a guest token.
     * @return Cart The newly created cart instance.
     */
    public function create(CartIdentifierData $cartIdentifierData): Cart
    {
        return Cart::create([
            'user_id' => $cartIdentifierData->user?->id,
            'guest_token' => $cartIdentifierData->user?->id ? null : $cartIdentifierData->guestToken,
        ]);
    }

    /**
     * Add a product to the cart or update the quantity of an existing item.
     *
     * @param CartIdentifierData $cartIdentifierData The data used to identify the cart.
     * @param CreateCartItemData $cartItemData The data for the item to be added or updated.
     * @return array{0: Cart, 1: bool} An array containing the cart instance and a boolean indicating if a new item was created.
     * @throws Throwable If any error occurs during the database transaction.
     */
    public function addItem(CartIdentifierData $cartIdentifierData, CreateCartItemData $cartItemData): array
    {
        return DB::transaction(function () use ($cartIdentifierData, $cartItemData) {
            $cart = $this->findOrCreate($cartIdentifierData);
            $item = $cart->items()->where('product_id', $cartItemData->productId)->first();

            if (!$item) {
                $product = Product::findOrFail($cartItemData->productId);

                $item = $cart->items()->create([
                    'product_id' => $cartItemData->productId,
                    'price_snapshot' => $product->price,
                    'quantity' => $cartItemData->quantity,
                ]);
            } else {
                $item->addQuantity($cartItemData->quantity);
            }

            return [$cart, $item->wasRecentlyCreated];
        });
    }
}
