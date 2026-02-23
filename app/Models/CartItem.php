<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property numeric $price_snapshot
 * @property int $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Cart $cart
 * @property-read Product $product
 * @method static CartItemFactory factory($count = null, $state = [])
 * @method static Builder<static>|CartItem newModelQuery()
 * @method static Builder<static>|CartItem newQuery()
 * @method static Builder<static>|CartItem query()
 * @method static Builder<static>|CartItem whereCartId($value)
 * @method static Builder<static>|CartItem whereCreatedAt($value)
 * @method static Builder<static>|CartItem whereId($value)
 * @method static Builder<static>|CartItem wherePriceSnapshot($value)
 * @method static Builder<static>|CartItem whereProductId($value)
 * @method static Builder<static>|CartItem whereQuantity($value)
 * @method static Builder<static>|CartItem whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'price_snapshot',
        'quantity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_snapshot' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    /**
     * Refresh the expiration time of the associated cart whenever a cart item is saved or deleted.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saved(function (CartItem $item) {
            $item->cart->refreshExpiration();
        });

        static::deleted(function (CartItem $item) {
            $item->cart->refreshExpiration();
        });
    }
    /**
     * Get the product that owns the cart item.
     *
     * @return BelongsTo<Product, CartItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the cart that owns the cart item.
     *
     * @return BelongsTo<Cart, CartItem>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Calculate the total price for the cart item based on the price snapshot and quantity.
     *
     * @return string The total price for the cart item, formatted as a decimal string with 2 decimal places.
     */
    public function calculateTotalPrice(): string
    {
        return bcmul(
            (string) $this->price_snapshot,
            (string) $this->quantity,
            2
        );
    }

    public function addQuantity(int $quantity): CartItem
    {
        $this->quantity += $quantity;
        $this->save();
        return $this;
    }
}
