<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Data\Cart\Context\CartIdentifierData;
use App\Data\Cart\Requests\CreateCartItemData;
use App\Data\Cart\Requests\UpdateCartItemData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CartItemController extends Controller
{
    #[OA\Post(
        path: '/cart/items',
        description: 'Adds a product to the cart.',
        summary: 'Add or update cart item',
        security: [['sanctum' => []], ['guest_token' => []], []],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateItemRequest')
        ),
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'Cart item created.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Cart'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Cart item quantity updated.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Cart'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Add a product to the cart or update quantity.
     *
     * @param CreateCartItemData $cartItemData
     * @param AddCartItemAction $addCartItemAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(CreateCartItemData $cartItemData, AddCartItemAction $addCartItemAction): JsonResponse
    {
        $cartData = $addCartItemAction->handle($cartItemData, CartIdentifierData::fromRequest(request()));
        return CartResource::make($cartData->cart->loadMissing('items.product'))
            ->response()
            ->setStatusCode($cartData->created ? SymfonyResponse::HTTP_CREATED : SymfonyResponse::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCartItemData $cartItemData
     * @param CartItem $item
     * @param UpdateCartItemAction $updateCartItemAction
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(UpdateCartItemData $cartItemData, CartItem $item, UpdateCartItemAction $updateCartItemAction): JsonResponse
    {
        $updateCartItemAction->handle($cartItemData, $item);
        return CartResource::make($item->cart->loadMissing('items.product'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartItem $item)
    {
        //
    }
}
