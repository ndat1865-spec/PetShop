<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\OnePayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentGatewayReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_momo_successful_browser_return_marks_the_local_order_paid(): void
    {
        config()->set('services.momo.access_key', 'test-access-key');
        config()->set('services.momo.secret_key', 'test-secret-key');

        $order = $this->order('momo', 'PAW-MOMO-RETURN');
        $requestId = (string) Str::uuid();
        PaymentTransaction::create([
            'order_id' => $order->id,
            'request_id' => $requestId,
            'provider' => 'momo',
            'type' => 'payment',
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => 'VND',
        ]);

        $payload = $this->momoPayload($order, $requestId);

        $this->get(route('payments.momo.return', ['order' => $order, ...$payload]))
            ->assertOk()
            ->assertSee('Thanh toán thành công');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'request_id' => $requestId,
            'provider_transaction_id' => '123456789',
            'status' => 'paid',
        ]);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_momo_browser_return_rejects_a_valid_signature_for_the_wrong_amount(): void
    {
        config()->set('services.momo.access_key', 'test-access-key');
        config()->set('services.momo.secret_key', 'test-secret-key');

        $order = $this->order('momo', 'PAW-MOMO-AMOUNT');
        $requestId = (string) Str::uuid();
        PaymentTransaction::create([
            'order_id' => $order->id,
            'request_id' => $requestId,
            'provider' => 'momo',
            'type' => 'payment',
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => 'VND',
        ]);

        $payload = $this->momoPayload($order, $requestId, 199999);

        $this->get(route('payments.momo.return', ['order' => $order, ...$payload]))
            ->assertBadRequest()
            ->assertSee('Thanh toán chưa thành công');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_onepay_failed_browser_return_marks_the_order_failed(): void
    {
        $this->configureOnePay();
        $order = $this->order('onepay', 'PAW-ONEPAY-FAILED');
        $payload = [
            'vpc_Amount' => (string) ((int) $order->total * 100),
            'vpc_Card' => 'VC',
            'vpc_CurrencyCode' => 'VND',
            'vpc_MerchTxnRef' => $order->number,
            'vpc_Merchant' => 'TESTONEPAY',
            'vpc_Message' => '3D Secure Authentication Failed',
            'vpc_TxnResponseCode' => 'F',
        ];
        $payload['vpc_SecureHash'] = $this->onePaySignature($payload);

        $this->get(route('payments.onepay.return', $payload))
            ->assertRedirect(route('orders.show', ['order' => $order, 'token' => $order->public_token]))
            ->assertSessionHas('error', 'Thanh toán OnePay chưa thành công.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'failed',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider' => 'onepay',
            'status' => 'failed',
        ]);
    }

    public function test_onepay_payment_request_matches_the_official_sandbox_contract(): void
    {
        $this->configureOnePay();
        $order = $this->order('onepay', 'PAW-ONEPAY-REQUEST');
        $request = Request::create('/thanh-toan', 'POST', server: ['REMOTE_ADDR' => '203.0.113.10']);

        $url = app(OnePayGateway::class)->paymentUrl($order, $request);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $params);

        $this->assertSame('https', parse_url($url, PHP_URL_SCHEME));
        $this->assertSame('mtf.onepay.vn', parse_url($url, PHP_URL_HOST));
        $this->assertSame('/paygate/vpcpay.op', parse_url($url, PHP_URL_PATH));
        $this->assertSame('2', $params['vpc_Version']);
        $this->assertSame('pay', $params['vpc_Command']);
        $this->assertSame('TESTONEPAY', $params['vpc_Merchant']);
        $this->assertSame('TESTCODE', $params['vpc_AccessCode']);
        $this->assertSame($order->number, $params['vpc_MerchTxnRef']);
        $this->assertSame("Pawora {$order->number}", $params['vpc_OrderInfo']);
        $this->assertLessThanOrEqual(34, strlen($params['vpc_OrderInfo']));
        $this->assertSame((string) ((int) $order->total * 100), $params['vpc_Amount']);
        $this->assertSame('VND', $params['vpc_Currency']);
        $this->assertSame('203.0.113.10', $params['vpc_TicketNo']);
        $this->assertSame(route('payments.onepay.return'), $params['vpc_ReturnURL']);
        $this->assertSame(route('payments.onepay.callback'), $params['vpc_CallbackURL']);

        $secureHash = $params['vpc_SecureHash'];
        unset($params['vpc_SecureHash']);
        $this->assertSame($this->onePaySignature($params), $secureHash);
    }

    public function test_onepay_signature_covers_custom_user_parameters(): void
    {
        $this->configureOnePay();
        $payload = [
            'user_TraceId' => 'trace-123',
            'vpc_Amount' => '19890000',
            'vpc_MerchTxnRef' => 'PAW-ONEPAY-HASH',
            'vpc_Merchant' => 'TESTONEPAY',
            'vpc_TxnResponseCode' => '0',
        ];
        $payload['vpc_SecureHash'] = $this->onePaySignature($payload);

        $this->assertTrue(app(OnePayGateway::class)->validSignature($payload));
    }

    public function test_onepay_successful_callback_marks_the_order_paid_and_acknowledges_the_ipn(): void
    {
        $this->configureOnePay();
        $order = $this->order('onepay', 'PAW-ONEPAY-CALLBACK-OK');
        $payload = $this->onePayPayload($order, '0', 'ONEPAY-TXN-OK');

        $this->get(route('payments.onepay.callback', $payload))
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSeeText('responsecode=1&desc=confirm-success', false);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider' => 'onepay',
            'provider_transaction_id' => 'ONEPAY-TXN-OK',
            'status' => 'paid',
            'amount' => 198900,
            'currency' => 'VND',
        ]);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_onepay_failed_callback_records_failure_but_still_acknowledges_receipt(): void
    {
        $this->configureOnePay();
        $order = $this->order('onepay', 'PAW-ONEPAY-CALLBACK-FAILED');
        $payload = $this->onePayPayload($order, '99', 'ONEPAY-TXN-FAILED');

        $this->post(route('payments.onepay.callback'), $payload)
            ->assertOk()
            ->assertSeeText('responsecode=1&desc=confirm-success', false);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'failed',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider_transaction_id' => 'ONEPAY-TXN-FAILED',
            'status' => 'failed',
        ]);
        $this->get(route('orders.show', ['order' => $order, 'token' => $order->public_token]))
            ->assertOk()
            ->assertSee('Thanh toán thất bại');
    }

    public function test_onepay_callback_uses_request_currency_when_the_response_does_not_echo_it(): void
    {
        $this->configureOnePay();
        $order = $this->order('onepay', 'PAW-ONEPAY-NO-CURRENCY');
        $payload = $this->onePayPayload($order, 'F', 'ONEPAY-TXN-NO-CURRENCY');
        unset($payload['vpc_CurrencyCode']);
        $payload['vpc_SecureHash'] = $this->onePaySignature($payload);

        $this->get(route('payments.onepay.callback', $payload))
            ->assertOk()
            ->assertSeeText('responsecode=1&desc=confirm-success', false);

        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider_transaction_id' => 'ONEPAY-TXN-NO-CURRENCY',
            'status' => 'failed',
            'currency' => 'VND',
        ]);
    }

    public function test_onepay_callback_rejects_invalid_signature_without_changing_the_order(): void
    {
        $this->configureOnePay();
        $order = $this->order('onepay', 'PAW-ONEPAY-BAD-HASH');
        $payload = $this->onePayPayload($order, '0', 'ONEPAY-TXN-BAD-HASH');
        $payload['vpc_SecureHash'] = str_repeat('0', 64);

        $this->get(route('payments.onepay.callback', $payload))
            ->assertOk()
            ->assertSeeText('responsecode=0&desc=confirm-fail', false);

        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertDatabaseMissing('payment_transactions', [
            'order_id' => $order->id,
            'provider' => 'onepay',
        ]);
    }

    public function test_onepay_callback_requires_the_exact_amount(): void
    {
        $this->configureOnePay();
        $order = $this->order('onepay', 'PAW-ONEPAY-BAD-AMOUNT');
        $payload = $this->onePayPayload($order, '0', 'ONEPAY-TXN-BAD-AMOUNT', ((int) $order->total * 100) + 1);

        $this->get(route('payments.onepay.callback', $payload))
            ->assertOk()
            ->assertSeeText('responsecode=0&desc=confirm-fail', false);

        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'status' => 'invalid_amount',
        ]);
    }

    public function test_onepay_callback_rejects_the_wrong_currency_or_merchant(): void
    {
        $this->configureOnePay();
        $wrongCurrencyOrder = $this->order('onepay', 'PAW-ONEPAY-BAD-CURRENCY');
        $wrongCurrency = $this->onePayPayload($wrongCurrencyOrder, '0', 'ONEPAY-TXN-BAD-CURRENCY');
        $wrongCurrency['vpc_CurrencyCode'] = 'USD';
        $wrongCurrency['vpc_SecureHash'] = $this->onePaySignature($wrongCurrency);

        $this->get(route('payments.onepay.callback', $wrongCurrency))
            ->assertOk()
            ->assertSeeText('responsecode=0&desc=confirm-fail', false);

        $wrongMerchantOrder = $this->order('onepay', 'PAW-ONEPAY-BAD-MERCHANT');
        $wrongMerchant = $this->onePayPayload($wrongMerchantOrder, '0', 'ONEPAY-TXN-BAD-MERCHANT');
        $wrongMerchant['vpc_Merchant'] = 'OTHERONEPAY';
        $wrongMerchant['vpc_SecureHash'] = $this->onePaySignature($wrongMerchant);

        $this->get(route('payments.onepay.callback', $wrongMerchant))
            ->assertOk()
            ->assertSeeText('responsecode=0&desc=confirm-fail', false);

        $this->assertSame('pending', $wrongCurrencyOrder->fresh()->payment_status);
        $this->assertSame('pending', $wrongMerchantOrder->fresh()->payment_status);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $wrongCurrencyOrder->id,
            'status' => 'invalid_currency',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $wrongMerchantOrder->id,
            'status' => 'invalid_merchant',
        ]);
    }

    public function test_onepay_callbacks_are_idempotent_and_cannot_overwrite_a_paid_transaction(): void
    {
        $this->configureOnePay();
        $order = $this->order('onepay', 'PAW-ONEPAY-IDEMPOTENT');
        $success = $this->onePayPayload($order, '0', 'ONEPAY-TXN-IDEMPOTENT');

        $this->get(route('payments.onepay.callback', $success))->assertOk();
        $this->get(route('payments.onepay.callback', $success))->assertOk();

        $invalidCurrency = $success;
        $invalidCurrency['vpc_CurrencyCode'] = 'USD';
        $invalidCurrency['vpc_SecureHash'] = $this->onePaySignature($invalidCurrency);
        $this->get(route('payments.onepay.callback', $invalidCurrency))
            ->assertOk()
            ->assertSeeText('responsecode=0&desc=confirm-fail', false);

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider_transaction_id' => 'ONEPAY-TXN-IDEMPOTENT',
            'status' => 'paid',
        ]);
        $this->assertSame(1, PaymentTransaction::where('order_id', $order->id)->where('provider', 'onepay')->count());
    }

    private function order(string $paymentMethod, string $number): Order
    {
        return Order::create([
            'number' => $number,
            'public_token' => (string) Str::uuid(),
            'customer_name' => 'Khách thử nghiệm',
            'email' => 'payment@example.test',
            'phone' => '0909000000',
            'shipping_address' => 'Hà Nội',
            'subtotal' => 198900,
            'total' => 198900,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }

    private function momoPayload(Order $order, string $requestId, ?int $amount = null): array
    {
        $payload = [
            'amount' => $amount ?? (int) $order->total,
            'extraData' => '',
            'message' => 'Thành công.',
            'orderId' => $order->number,
            'orderInfo' => "Pawora - thanh toán đơn {$order->number}",
            'orderType' => 'momo_wallet',
            'partnerCode' => 'MOMO',
            'payType' => 'webApp',
            'requestId' => $requestId,
            'responseTime' => '1787792400000',
            'resultCode' => 0,
            'transId' => '123456789',
        ];
        $signed = ['accessKey' => config('services.momo.access_key'), ...$payload];
        ksort($signed);
        $payload['signature'] = hash_hmac(
            'sha256',
            collect($signed)->map(fn ($value, $key) => $key.'='.$value)->implode('&'),
            (string) config('services.momo.secret_key')
        );

        return $payload;
    }

    private function configureOnePay(): void
    {
        config()->set('services.onepay.merchant_id', 'TESTONEPAY');
        config()->set('services.onepay.access_code', 'TESTCODE');
        config()->set('services.onepay.secure_secret', '0123456789ABCDEF0123456789ABCDEF');
        config()->set('services.onepay.payment_url', 'https://mtf.onepay.vn/paygate/vpcpay.op');
        config()->set('services.onepay.return_url', null);
        config()->set('services.onepay.callback_url', null);
    }

    private function onePayPayload(Order $order, string $responseCode, string $transactionNo, ?int $amount = null): array
    {
        $payload = [
            'vpc_Amount' => (string) ($amount ?? ((int) $order->total * 100)),
            'vpc_Command' => 'pay',
            'vpc_CurrencyCode' => 'VND',
            'vpc_MerchTxnRef' => $order->number,
            'vpc_Merchant' => 'TESTONEPAY',
            'vpc_Message' => $responseCode === '0' ? 'Approved' : 'Transaction failed',
            'vpc_TransactionNo' => $transactionNo,
            'vpc_TxnResponseCode' => $responseCode,
            'vpc_Version' => '2',
        ];
        $payload['vpc_SecureHash'] = $this->onePaySignature($payload);

        return $payload;
    }

    private function onePaySignature(array $payload): string
    {
        ksort($payload);
        $hashData = [];
        foreach ($payload as $key => $value) {
            if ($key !== 'vpc_SecureHash'
                && $key !== 'vpc_SecureHashType'
                && (str_starts_with($key, 'vpc_') || str_starts_with($key, 'user_'))
                && $value !== '') {
                $hashData[] = $key.'='.$value;
            }
        }

        return strtoupper(hash_hmac(
            'sha256',
            implode('&', $hashData),
            pack('H*', (string) config('services.onepay.secure_secret'))
        ));
    }
}
