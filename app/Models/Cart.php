<?php

namespace App\Models;

use App\Data\Cart\CartIdentifierData;
use Database\Factories\CartFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
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
 * @method static notExpired()
 * @mixin Eloquent
 */
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory, Prunable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'guest_token',
        'expires_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the prunable model query.
     *
     * @return Builder The query builder instance for retrieving prunable models.
     */
    public function prunable(): Builder
    {
        return static::where('expires_at', '<=', now());
    }

    /**
     * Set the expiration date for the cart when creating.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Cart $cart) {
            $days = $cart->user_id ? config('cart.expiration_days.user') : config('cart.expiration_days.guest');
            $cart->expires_at = now()->addDays($days);
        });
    }

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
     * Scope a query to only include carts that belong to the specified owner, which can be either an authenticated user or a guest identified by a token.
     *
     * @param Builder $query The query builder instance to modify.
     * @param CartIdentifierData $data The data used to identify the cart owner, which may include user information and guest token.
     * @return Builder The modified query builder instance with the owner scope applied.
     */
    public function scopeForOwner(Builder $query, CartIdentifierData $data): Builder
    {
        if ($data->user) {
            return $query->where('user_id', $data->user?->id);
        }

        return $query->where('guest_token', $data->guestToken)->whereNull('user_id');
    }

    /**
     * Scope a query to only include active carts that have not expired.
     *
     * @param Builder $query The query builder instance to modify.
     * @return Builder The modified query builder instance with the active carts scope applied.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Check if the cart is expired based on the expires_at attribute.
     *
     * @return bool True if the cart is expired, false otherwise.
     */
    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }

    /**
     * Extend the expiration date of the cart based on whether it belongs to an authenticated user or a guest. The expiration period is determined by the configuration settings for user and guest carts.
     *
     * @return void
     */
    public function extendExpiration(): void
    {
        $days = $this->user_id
            ? config('cart.expiration_days.user')
            : config('cart.expiration_days.guest');

        $this->expires_at = now()->addDays($days);
        $this->save();
    }

    /**
     * Clear the cart by deleting all associated cart items. This method is typically used when a cart is expired or when the user wants to empty their cart.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->items()->delete();
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
