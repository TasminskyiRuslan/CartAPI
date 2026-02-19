<?php

namespace App\Models;

use Database\Factories\CartFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $guest_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, CartItem> $items
 * @property-read int|null $items_count
 * @property-read User|null $user
 * @method static CartFactory factory($count = null, $state = [])
 * @method static Builder<static>|Cart newModelQuery()
 * @method static Builder<static>|Cart newQuery()
 * @method static Builder<static>|Cart query()
 * @method static Builder<static>|Cart whereCreatedAt($value)
 * @method static Builder<static>|Cart whereGuestToken($value)
 * @method static Builder<static>|Cart whereId($value)
 * @method static Builder<static>|Cart whereUpdatedAt($value)
 * @method static Builder<static>|Cart whereUserId($value)
 * @mixin Eloquent
 */
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'guest_token',
    ];

    /**
     * Get the user that owns the cart.
     *
     * @return BelongsTo<User, Cart>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for the cart.
     *
     * @return HasMany<CartItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate the total quantity of items in the cart.
     *
     * @return int The total quantity of items in the cart.
     */
    public function calculateTotalItems(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Calculate the total price of the cart by summing the total price of each cart item.
     *
     * @return string The total price of the cart formatted as a decimal string with 2 decimal places.
     */
    public function calculateTotalPrice(): string
    {
        $totalPrice = '0.00';
        foreach ($this->items as $item) {
            $totalPrice = bcadd($totalPrice, $item->calculateTotalPrice(), 2);
        }
        return $totalPrice;
    }
}
