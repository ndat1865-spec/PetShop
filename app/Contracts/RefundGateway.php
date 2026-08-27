<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

interface RefundGateway
{
    public function requestRefund(Order $order, PaymentTransaction $refund, ?PaymentTransaction $payment): array;

    public function verifyCallback(Request $request): bool;

    /** @return array{request_id:string,status:string,amount:int,provider_transaction_id:?string,payload:array} */
    public function callbackData(Request $request): array;
}
