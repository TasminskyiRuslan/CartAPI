<?php

namespace App\Http\Controllers\Api\Cart;

use App\Data\Cart\CartIdentifierData;
use App\Data\Cart\Requests\CreateCartItemData;
use App\Data\Cart\Requests\UpdateCartItemData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CartItemController extends Controller
{
    public function __construct(
        protected CartService $service
    )
    {
    }

    /**
     * Store a newly created cart item or update the quantity of an existing one.
     *
     * @param Request $request The incoming HTTP request.
     * @param CreateCartItemData $data The data transfer object containing product ID and quantity.
     * @return JsonResponse A JSON response containing the updated cart data, with a 201 status if a new item was created, or 200 if updated.
     * @throws Throwable If any error occurs during the database transaction.
     */
    public function store(Request $request, CreateCartItemData $data): JsonResponse
    {
        [$cart, $itemCreated] = $this->service->addItem(CartIdentifierData::fromRequest($request), $data);
        return CartResource::make($cart->loadMissing('items.product'))
            ->toResponse($request)
            ->setStatusCode($itemCreated ? SymfonyResponse::HTTP_CREATED : SymfonyResponse::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartItemData $data, CartItem $item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartItem $item)
    {
        //
    }
}
