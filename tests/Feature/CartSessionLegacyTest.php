<?php

namespace Tests\Feature;

use App\Services\CartService;
use Tests\TestCase;

class CartSessionLegacyTest extends TestCase
{
    public function test_migrating_a_legacy_cart_invalidates_checkout_once(): void
    {
        $this->withSession(['cart' => [7 => 2], 'checkout_idempotency_key' => 'old-checkout']);
        $cart = app(CartService::class);

        $this->assertSame([
            '7:base' => ['product_id' => 7, 'variant_id' => null, 'quantity' => 2],
        ], $cart->lines());
        $this->assertFalse(session()->has('checkout_idempotency_key'));

        session(['checkout_idempotency_key' => 'new-checkout']);
        $this->assertSame(session('cart'), $cart->lines());
        $this->assertSame('new-checkout', session('checkout_idempotency_key'));
    }

    public function test_count_does_not_mutate_the_session_but_put_invalidates_checkout(): void
    {
        $this->withSession(['cart' => [7 => 30], 'checkout_idempotency_key' => 'checkout']);
        $cart = app(CartService::class);

        $this->assertSame(20, $cart->count());
        $this->assertSame([7 => 30], session('cart'));
        $this->assertSame('checkout', session('checkout_idempotency_key'));

        $cart->put(['7:9' => 2]);
        $this->assertSame([
            '7:9' => ['product_id' => 7, 'variant_id' => 9, 'quantity' => 2],
        ], session('cart'));
        $this->assertFalse(session()->has('checkout_idempotency_key'));
    }
}
