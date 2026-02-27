<?php

namespace App\Listeners\Cart;

use App\Actions\Cart\MergeCartAction;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Throwable;

class MergeCartOnAuthenticationListener
{
    /**
     * @param MergeCartAction $mergeGuestCartAction
     */
    public function __construct(
        protected MergeCartAction $mergeGuestCartAction
    )
    {
    }

    /**
     * Handle the authentication event to merge the guest cart.
     *
     * @param Login|Registered $event
     * @return void
     * @throws Throwable
     */
    public function handle(Login|Registered $event): void
    {
        $guestToken = request()->header(config('cart.guest_token_header'));

        if (!$guestToken) {
            return;
        }

        $this->mergeGuestCartAction->handle(
            $event->user->getAuthIdentifier(),
            $guestToken
        );
    }
}
