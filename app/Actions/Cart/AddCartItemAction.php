<?php

namespace App\Actions\Cart;

use App\Data\Cart\AddCartItemResultData;
use App\Data\Cart\CartIdentifierData;
use App\Data\Cart\Requests\CreateCartItemData;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

class AddCartItemAction
{
    /**
     * @param ResolveCartAction $resolveCartAction
     */
    public function __construct(
        protected ResolveCartAction $resolveCartAction
    ) {}

    /**
     * Add a product to the cart or update its quantity.
     *
     * @param CartIdentifierData $identifierData
     * @param CreateCartItemData $itemData
     * @return AddCartItemResultData
     * @throws Throwable
     */
    public function handle(CartIdentifierData $identifierData, CreateCartItemData $itemData): AddCartItemResultData
    {
        return DB::transaction(function () use ($identifierData, $itemData) {
            $cart = $this->resolveCartAction->handle($identifierData);
            $product = Product::findOrFail($itemData->productId);

            $item = $cart->items()->firstOrNew(['product_id' => $product->id]);

            $created = !$item->exists;

            if ($created) {
                $item->price_snapshot = $product->price;
                $item->quantity = $itemData->quantity;
            } else {
                $item->quantity = min($item->quantity + $itemData->quantity, config('cart.max_quantity'));
            }

            $item->save();
            $cart->refreshExpiration()->save();

            return new AddCartItemResultData(
                cart: $cart,
                created: $created
            );
        });
    }
}
