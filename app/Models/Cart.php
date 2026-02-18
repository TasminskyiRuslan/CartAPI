<?php

namespace App\Models;

use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
