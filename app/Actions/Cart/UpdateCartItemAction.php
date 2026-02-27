<?php

namespace App\Actions\Cart;

use App\Data\Cart\Requests\UpdateCartItemData;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateCartItemAction
{
    /**
     * Update the quantity of a cart item.
     *
     * @param UpdateCartItemData $itemData
     * @param CartItem $item
     * @return CartItem
     * @throws Throwable
     */
    public function handle(UpdateCartItemData $itemData, CartItem $item): CartItem
    {
        return DB::transaction(function () use ($itemData, $item) {
            $item->quantity = min($itemData->quantity, config('cart.max_quantity'));
            $item->save();
            $item->cart->refreshExpiration()->save();

            return $item;
        });
    }
}
