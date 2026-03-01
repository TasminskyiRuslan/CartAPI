<?php /** @noinspection ALL */

namespace App\Actions\Cart;

use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Throwable;

class  RemoveCartItemAction
{
    /**
     * Remove a cart item and refresh the cart expiration.
     *
     * @param CartItem $item
     * @return void
     * @throws Throwable
     */
    public function handle(CartItem $item): void
    {
        DB::transaction(function () use ($item) {
            $cart = $item->cart;
            $item->delete();
            $cart->refreshExpiration()->save();
        });
    }
}
