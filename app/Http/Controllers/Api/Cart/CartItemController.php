<?php /** @noinspection ALL */

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Data\Cart\Context\CartIdentifierData;
use App\Data\Cart\Requests\CreateCartItemData;
use App\Data\Cart\Requests\UpdateCartItemData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CartItemController extends Controller
{
    #[OA\Post(
        path: '/cart/items',
        description: 'Adds a product to the cart or increases its quantity if it already exists.',
        summary: 'Add item to cart',
        security: [['sanctum' => []], ['guest_token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateCartItemRequest')
        ),
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'Item added to cart.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/CartResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Item quantity increased.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/CartResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Identification missing (user not logged in and no guest token provided).'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Add an item to the cart or increase its quantity.
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

    #[OA\Patch(
        path: '/cart/items/{item}',
        description: 'Updates the quantity of a specific cart item.',
        summary: 'Update cart item quantity',
        security: [['sanctum' => []], ['guest_token' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateCartItemRequest')
        ),
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'item',
                description: 'Cart item identifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Item quantity updated.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/CartResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Cart item not found.',
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Identification missing (user not logged in and no guest token provided).'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Update the quantity of a specific cart item.
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

    #[OA\Delete(
        path: '/cart/items/{item}',
        description: 'Removes a specific product from the active cart.',
        summary: 'Remove specific item from cart',
        security: [['sanctum' => []], ['guest_token' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'item',
                description: 'Cart item identifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'Item successfully removed from cart.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Identification missing (user not logged in and no guest token provided).'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_NOT_FOUND,
                description: 'Cart item not found.'
            ),
        ]
    )]
    /**
     * Remove a specific item from the cart.
     *
     * @param CartItem $item
     * @param RemoveCartItemAction $removeCartItemAction
     * @return Response
     * @throws Throwable
     */
    public function destroy(CartItem $item, RemoveCartItemAction $removeCartItemAction): Response
    {
        $removeCartItemAction->handle($item);
        return response()->noContent();
    }
}
