<?php

namespace Tests\Unit;

use App\Services\CartService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CartNormalizationLegacyTest extends TestCase
{
    #[DataProvider('legacyCarts')]
    public function test_existing_session_formats_keep_their_identity_and_quantity(array $raw, array $expected): void
    {
        $this->assertSame($expected, (new CartService)->normalize($raw));
    }

    public static function legacyCarts(): array
    {
        return [
            'numeric product key' => [[7 => '2'], [
                '7:base' => ['product_id' => 7, 'variant_id' => null, 'quantity' => 2],
            ]],
            'variant in legacy entry' => [[7 => ['quantity' => 2, 'variant_id' => '9']], [
                '7:9' => ['product_id' => 7, 'variant_id' => 9, 'quantity' => 2],
            ]],
            'identity from composite key' => [['7:9' => ['quantity' => 3]], [
                '7:9' => ['product_id' => 7, 'variant_id' => 9, 'quantity' => 3],
            ]],
            'explicit identity overrides key' => [['7:9' => ['product_id' => '8', 'variant_id' => '10', 'quantity' => 2]], [
                '8:10' => ['product_id' => 8, 'variant_id' => 10, 'quantity' => 2],
            ]],
            'explicit null clears variant but null product falls back' => [['7:9' => ['product_id' => null, 'variant_id' => null, 'quantity' => 2]], [
                '7:base' => ['product_id' => 7, 'variant_id' => null, 'quantity' => 2],
            ]],
            'zero variant in entry means base product' => [['7:9' => ['variant_id' => '0', 'quantity' => 2]], [
                '7:base' => ['product_id' => 7, 'variant_id' => null, 'quantity' => 2],
            ]],
            // Legacy scalar keys retain zero variants; normalization is not catalog validation.
            'zero variant in scalar key is retained' => [['7:0' => 2], [
                '7:0' => ['product_id' => 7, 'variant_id' => 0, 'quantity' => 2],
            ]],
            'duplicate lines merge and cap at twenty' => [[7 => 13, '7:base' => 12, '7:9' => 2], [
                '7:base' => ['product_id' => 7, 'variant_id' => null, 'quantity' => 20],
                '7:9' => ['product_id' => 7, 'variant_id' => 9, 'quantity' => 2],
            ]],
            'invalid or empty lines are discarded' => [[
                'invalid' => 3, '0:base' => 1, '7:base' => 0, '8:base' => -1,
                '9:base' => [], '10:base' => ['product_id' => 0, 'quantity' => 2],
            ], []],
            'entry supplies identity when key is invalid' => [['invalid' => ['product_id' => 7, 'quantity' => 1]], [
                '7:base' => ['product_id' => 7, 'variant_id' => null, 'quantity' => 1],
            ]],
            'legacy numeric coercion is retained' => [['7e0' => '2.9'], [
                '7:base' => ['product_id' => 7, 'variant_id' => null, 'quantity' => 2],
            ]],
            'empty cart' => [[], []],
        ];
    }
}
