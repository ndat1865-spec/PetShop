<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OnePayGateway
{
    public const CURRENCY = 'VND';

    public function enabled(): bool
    {
        return (bool) config('services.onepay.enabled')
            && filled(config('services.onepay.merchant_id'))
            && filled(config('services.onepay.access_code'))
            && $this->hasValidSecureSecret();
    }

    public function paymentUrl(Order $order, Request $request): string
    {
        $params = [
            'Title' => 'Pawora Checkout',
            'vpc_Version' => '2',
            'vpc_Command' => 'pay',
            'vpc_Merchant' => (string) config('services.onepay.merchant_id'),
            'vpc_AccessCode' => (string) config('services.onepay.access_code'),
            'vpc_MerchTxnRef' => $order->number,
            'vpc_OrderInfo' => Str::ascii("Pawora {$order->number}"),
            'vpc_Amount' => $this->amountFor($order),
            'vpc_Currency' => self::CURRENCY,
            'vpc_Locale' => 'vn',
            'vpc_CardList' => 'INTERNATIONAL',
            'vpc_ReturnURL' => config('services.onepay.return_url') ?: route('payments.onepay.return'),
            'vpc_CallbackURL' => config('services.onepay.callback_url') ?: route('payments.onepay.callback'),
            'vpc_TicketNo' => $request->ip() ?: '127.0.0.1',
        ];

        $params['vpc_SecureHash'] = $this->hash($params);

        return rtrim((string) config('services.onepay.payment_url'), '?').'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function validSignature(array $params): bool
    {
        $secureHash = $params['vpc_SecureHash'] ?? '';
        unset($params['vpc_SecureHash'], $params['vpc_SecureHashType']);

        if (! is_string($secureHash) || preg_match('/\A[0-9a-f]{64}\z/i', $secureHash) !== 1 || ! $this->hasValidSecureSecret()) {
            return false;
        }

        try {
            return hash_equals(strtoupper($secureHash), $this->hash($params));
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function successful(array $params): bool
    {
        return ($params['vpc_TxnResponseCode'] ?? null) === '0';
    }

    public function amountMatches(Order $order, mixed $amount): bool
    {
        return is_scalar($amount)
            && preg_match('/\A\d+\z/', (string) $amount) === 1
            && hash_equals($this->amountFor($order), (string) $amount);
    }

    public function currencyMatches(array $params): bool
    {
        $currencies = array_values(array_filter([
            $params['vpc_CurrencyCode'] ?? null,
            $params['vpc_Currency'] ?? null,
        ], static fn ($value) => is_scalar($value) && (string) $value !== ''));

        return $currencies === []
            || collect($currencies)->every(static fn ($currency) => strtoupper((string) $currency) === self::CURRENCY);
    }

    public function merchantMatches(array $params): bool
    {
        return is_scalar($params['vpc_Merchant'] ?? null)
            && hash_equals((string) config('services.onepay.merchant_id'), (string) $params['vpc_Merchant']);
    }

    public function responseCurrency(array $params): string
    {
        return strtoupper((string) ($params['vpc_CurrencyCode'] ?? $params['vpc_Currency'] ?? self::CURRENCY));
    }

    private function amountFor(Order $order): string
    {
        return (string) ((int) $order->total * 100);
    }

    private function hash(array $params): string
    {
        ksort($params);

        $hashData = [];
        foreach ($params as $key => $value) {
            if (! is_string($key)
                || (! str_starts_with($key, 'vpc_') && ! str_starts_with($key, 'user_'))
                || $value === null
                || $value === '') {
                continue;
            }

            if (! is_scalar($value)) {
                throw new InvalidArgumentException('OnePay signature parameters must be scalar values.');
            }

            $hashData[] = $key.'='.(string) $value;
        }

        $secret = pack('H*', (string) config('services.onepay.secure_secret'));

        return strtoupper(hash_hmac('sha256', implode('&', $hashData), $secret));
    }

    private function hasValidSecureSecret(): bool
    {
        $secret = config('services.onepay.secure_secret');

        return is_string($secret)
            && $secret !== ''
            && strlen($secret) % 2 === 0
            && ctype_xdigit($secret);
    }
}
