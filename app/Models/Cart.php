<?php

namespace App\Models;

use App\Data\Cart\Context\CartIdentifierData;
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
 * @method static forUser(User $user)
 * @method static forGuest(string $guestToken)
 * @method static forOwner(CartIdentifierData $data)
 * @property Carbon|null $expires_at
 * @method static Builder<static>|Cart active()
 * @method static Builder<static>|Cart whereExpiresAt($value)
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
     * Get the attributes that should be cast.
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
     * The "booted" method of the model.
     * Registers a saving event to automatically refresh the expiration date.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Cart $cart) {
            $cart->refreshExpiration();
        });
    }

    /**
     * Get the prunable model query for cleaning up expired carts.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::where('expires_at', '<=', now());
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
     * Get the cart items.
     *
     * @return HasMany<CartItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Scope a query to filter carts by owner (either authenticated user or guest).
     *
     * @param Builder<static> $query
     * @param CartIdentifierData $data
     * @return Builder<static>
     */
    public function scopeForOwner(Builder $query, CartIdentifierData $data): Builder
    {
        return $query->when(
            $data->user,
            fn (Builder $q) => $q->whereUserId($data->user->id),
            fn (Builder $q) => $q->whereGuestToken($data->guestToken)->whereNull('user_id')
        );
    }

    /**
     * Scope a query to only include active (not expired) carts.
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Determine if the cart has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }

    /**
     * Refresh the expiration date based on the owner type (user or guest).
     *
     * @return $this
     */
    public function refreshExpiration(): static
    {
        $days = $this->user_id
            ? config('cart.expiration_days.user')
            : config('cart.expiration_days.guest');

        $this->expires_at = now()->addDays($days);

        return $this;
    }

    /**
     * Calculate the total quantity of all items in the cart.
     *
     * @return int
     */
    public function calculateTotalItems(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Calculate the total price of the cart.
     *
     * @return string
     */
    public function calculateTotalPrice(): string
    {
        $totalPrice = '0.00';
        foreach ($this->items as $item) {
            $totalPrice = bcadd($totalPrice, $item->calculateTotalPrice(), 2);
        }
        return $totalPrice;
    }

    /**
     * Assign the cart to a specific user and remove guest status.
     *
     * @param int $userId
     * @return $this
     */
    public function assignToUser(int $userId): static
    {
        $this->user_id = $userId;
        $this->guest_token = null;

        return $this;
    }
}
