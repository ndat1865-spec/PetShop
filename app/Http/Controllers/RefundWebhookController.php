<?php

namespace App\Http\Controllers;

use App\Services\RefundGatewayRegistry;
use App\Services\RefundService;
use Illuminate\Http\Request;

class RefundWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider, RefundGatewayRegistry $gateways, RefundService $refunds)
    {
        abort_unless(in_array($provider, ['vnpay', 'zalopay', 'momo', 'sepay'], true), 404);
        $gateway = $gateways->for($provider);
        if (! $gateway->verifyCallback($request)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        $refund = $refunds->applyCallback($provider, $gateway->callbackData($request));

        return response()->json(['success' => true, 'status' => $refund->status]);
    }
}
