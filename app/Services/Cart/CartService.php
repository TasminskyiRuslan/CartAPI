<?php

namespace App\Services\Cart;

use App\Data\Cart\CartIdentifierData;
use App\Models\Cart;
use App\Models\User;
use DB;
use Throwable;

class CartService
{
    /**
     * Find an active cart for the given guest token.
     *
     * @param string $guestToken The guest token for which to find the cart.
     * @return Cart|null The found cart instance if it exists and is active; otherwise, null.
     */
    public function findForGuestToken(string $guestToken): ?Cart
    {
        return Cart::forGuest($guestToken)->active()->first();
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
     * Find an active cart for the given identifier data, which may include user information or a guest token.
     *
     * @param CartIdentifierData $data The data used to identify the cart, which may include user information or a guest token.
     * @return Cart|null The found cart instance if it exists and is active; otherwise, null.
     */
    public function find(CartIdentifierData $data): ?Cart
    {
        return Cart::forOwner($data)->active()->first();
    }

    /**
     * Create a new cart based on the provided identifier data.
     *
     * @param CartIdentifierData $data The data used to create the cart, which may include user information or a guest token.
     * @return Cart The newly created cart instance.
     */
    public function create(CartIdentifierData $data): Cart
    {
        return Cart::create([
            'user_id' => $data->user?->id,
            'guest_token' => $data->user?->id ? null : $data->guestToken,
        ]);
    }

    /**
     * Find an existing cart for the given identifier data or create a new one if it doesn't exist or is expired.
     *
     * @param CartIdentifierData $data The data used to identify the cart, which may include user information or a guest token.
     * @return Cart The found or newly created cart instance.
     * @throws Throwable If any error occurs during the database transaction.
     */
    public function findOrCreate(CartIdentifierData $data): Cart
    {
        return DB::transaction(function () use ($data) {
            $cart = Cart::forOwner($data)->first();

            if (!$cart) {
                $cart = $this->create($data);
            } elseif ($cart->isExpired()) {
                $cart->delete();
                $cart = $this->create($data);
            }

            return $cart;
        });
    }
}
