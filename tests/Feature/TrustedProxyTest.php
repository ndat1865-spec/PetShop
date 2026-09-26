<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    public function test_forwarded_https_is_only_honoured_when_proxies_are_trusted(): void
    {
        Route::get('/__proxy-check', fn () => url('/pawora'));

        config()->set('app.trusted_proxies', null);
        $this->get('/__proxy-check', ['X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertSee('http://localhost/pawora', false);

        config()->set('app.trusted_proxies', '*');
        $this->get('/__proxy-check', ['X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertSee('https://localhost/pawora', false);
    }
}
