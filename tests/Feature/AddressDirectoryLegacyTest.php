<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AddressDirectory;
use App\Services\GhnShippingClient;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class AddressDirectoryLegacyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        config(['pawora.shipping.ghn.enabled' => false]);
    }

    public function test_ward_endpoint_preserves_names_order_and_caches_success_for_one_day(): void
    {
        $this->freezeTime();
        $this->actingAs(new User(['id' => 1, 'role' => 'customer']));
        Http::fake([
            'provinces.open-api.vn/api/v2/p/' => Http::response([
                ['name' => 'Hà Nội', 'code' => 1],
            ]),
            'provinces.open-api.vn/api/v2/p/1*' => Http::response(['wards' => [
                ['name' => 'Phường Ba Đình'], ['name' => ''], ['name' => 'Phường Tây Hồ'],
            ]]),
        ]);
        $url = route('address.wards', ['province' => 'Thành phố Hà Nội']);
        $expected = ['wards' => ['Phường Ba Đình', 'Phường Tây Hồ']];

        $this->getJson($url)->assertOk()->assertExactJson($expected);
        $this->getJson($url)->assertOk()->assertExactJson($expected);
        Http::assertSentCount(2);

        $this->travel(25)->hours();
        $this->getJson($url)->assertOk()->assertExactJson($expected);
        Http::assertSentCount(4);
    }

    public function test_public_directory_failure_keeps_hanoi_fallback_without_caching_it(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $directory = app(AddressDirectory::class);

        $this->assertSame(['Phường Tây Tựu'], $directory->wards('Thành phố Hà Nội'));
        $this->assertSame([], $directory->wards('Thành phố Hồ Chí Minh'));
        $this->assertFalse(cache()->has('pawora.wards.thanh-pho-ha-noi'));
    }

    public function test_missing_province_does_not_request_details(): void
    {
        Http::fake(['*' => Http::response([['name' => 'Đà Nẵng', 'code' => 48]])]);

        $this->assertSame(['Phường Tây Tựu'], app(AddressDirectory::class)->wards('Hà Nội'));
        Http::assertSentCount(1);
    }

    public function test_empty_ward_list_uses_fallback_and_is_not_cached(): void
    {
        Http::fake([
            'provinces.open-api.vn/api/v2/p/' => Http::response([['name' => 'Hà Nội', 'code' => 1]]),
            'provinces.open-api.vn/api/v2/p/1*' => Http::response(['wards' => []]),
        ]);

        $this->assertSame(['Phường Tây Tựu'], app(AddressDirectory::class)->wards('Hà Nội'));
        $this->assertFalse(cache()->has('pawora.wards.ha-noi'));
        Http::assertSentCount(2);
    }

    public function test_disabled_ghn_returns_empty_lists_without_network_requests(): void
    {
        Http::fake();
        $directory = app(AddressDirectory::class);

        $this->assertSame([], $directory->districts('Hà Nội'));
        $this->assertSame([], $directory->ghnWards(1));
        Http::assertNothingSent();
    }

    public function test_ghn_results_keep_their_existing_shape(): void
    {
        $ghn = Mockery::mock(GhnShippingClient::class);
        $ghn->shouldReceive('enabled')->andReturn(true);
        $ghn->shouldReceive('provinceId')->with('Hà Nội')->andReturn(201);
        $ghn->shouldReceive('districts')->with(201)->andReturn([['id' => 1, 'name' => 'Quận Ba Đình']]);
        $ghn->shouldReceive('wards')->with(1)->andReturn([['code' => '001', 'name' => 'Phường Ba Đình']]);
        $directory = new AddressDirectory($ghn);

        $this->assertSame([['id' => 1, 'name' => 'Quận Ba Đình']], $directory->districts('Hà Nội'));
        $this->assertSame([['code' => '001', 'name' => 'Phường Ba Đình']], $directory->ghnWards(1));
    }

    public function test_unknown_ghn_province_returns_an_empty_list(): void
    {
        $ghn = Mockery::mock(GhnShippingClient::class);
        $ghn->shouldReceive('enabled')->andReturn(true);
        $ghn->shouldReceive('provinceId')->with('Hà Nội')->andReturn(null);

        $this->assertSame([], (new AddressDirectory($ghn))->districts('Hà Nội'));
    }

    public function test_ghn_failures_return_empty_lists(): void
    {
        $ghn = Mockery::mock(GhnShippingClient::class);
        $ghn->shouldReceive('enabled')->andReturn(true);
        $ghn->shouldReceive('provinceId')->andThrow(new \RuntimeException('GHN unavailable'));
        $ghn->shouldReceive('wards')->andThrow(new \RuntimeException('GHN unavailable'));
        $directory = new AddressDirectory($ghn);

        $this->assertSame([], $directory->districts('Hà Nội'));
        $this->assertSame([], $directory->ghnWards(1));
    }
}
