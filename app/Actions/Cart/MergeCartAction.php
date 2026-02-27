<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Throwable;

class MergeCartAction
{
    /**
     * Merge a guest cart into the user's active cart.
     *
     * If the guest cart is expired, it will be deleted.
     * If the user has no active cart (or it is expired), the guest cart will be assigned to the user.
     * Otherwise, cart items will be merged with quantity limits applied.
     *
     * @param int $userId
     * @param string $guestToken
     * @return void
     * @throws Throwable
     */
    public function handle(int $userId, string $guestToken): void
    {
        DB::transaction(function () use ($userId, $guestToken) {
            $guestCart = Cart::whereGuestToken($guestToken)->with('items.product')->lockForUpdate()->first();

            if (!$guestCart || $guestCart->isExpired()) {
                $guestCart?->delete();
                return;
            }

            $userCart = Cart::whereUserId($userId)->with('items.product')->lockForUpdate()->first();

            if (!$userCart || $userCart->isExpired()) {
                $userCart?->delete();
                $guestCart->assignToUser($userId)->refreshExpiration()->save();
                return;
            }

            $userItems = $userCart->items->keyBy('product_id');

            foreach ($guestCart->items as $guestItem) {
                $userItem = $userItems->get($guestItem->product_id);

                if ($userItem) {
                    $userItem->quantity = min($userItem->quantity + $guestItem->quantity, config('cart.max_quantity'));
                    $userItem->save();
                } else {
                    $guestItem->cart_id = $userCart->id;
                    $guestItem->save();
                }
            }

            $userCart->refreshExpiration()->save();
            $guestCart->refresh();
            $guestCart->delete();
        });
    }

}
