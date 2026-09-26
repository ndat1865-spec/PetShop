<?php

namespace App\Services;

use App\Contracts\RefundGateway;
use App\Services\RefundGateways\ManualRefundGateway;
use App\Services\RefundGateways\MomoRefundGateway;
use App\Services\RefundGateways\SepayRefundGateway;
use App\Services\RefundGateways\VnPayRefundGateway;
use App\Services\RefundGateways\ZaloPayRefundGateway;

class RefundGatewayRegistry
{
    public function for(string $provider): RefundGateway
    {
        return match ($provider) {
            'vnpay' => app(VnPayRefundGateway::class),
            'zalopay' => app(ZaloPayRefundGateway::class),
            'momo' => app(MomoRefundGateway::class),
            'bank_transfer', 'sepay' => app(SepayRefundGateway::class),
            default => app(ManualRefundGateway::class),
        };
    }
}
