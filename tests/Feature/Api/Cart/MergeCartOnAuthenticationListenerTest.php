<?php

use App\Actions\Cart\MergeCartAction;
use App\Listeners\Cart\MergeCartOnAuthenticationListener;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MergeCartOnAuthenticationListener', function () {

    it('does nothing if guest token is missing', function () {
        $this->mock(MergeCartAction::class)->shouldNotReceive('handle');
        event(new Login(config('auth.defaults.guard'), User::factory()->make(), false));
    });

    it('calls the action if guest token is present', function () {
        $guestToken = Str::uuid()->toString();
        request()->headers->set(config('cart.guest_header'), $guestToken);

        $user = User::factory()->create();
        $this->mock(MergeCartAction::class)->shouldReceive('handle')->once()->with($user->id, $guestToken);
        event(new Login(config('auth.defaults.guard'), $user, false));
    });
})->group('cart');
