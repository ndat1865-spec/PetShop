<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_with_an_empty_catalog(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
