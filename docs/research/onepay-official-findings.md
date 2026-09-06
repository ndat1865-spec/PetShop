# OnePay: investigation of unsuccessful sandbox payments

Checked 2026-09-06. Scope: official OnePay references and existing integration code; no payment initiated and no implementation changed.

## Conclusion from observed transactions

The parent agent's read-only local audit found three OnePay payments with `vpc_TxnResponseCode=F`, message `3D Secure Authentication Failed`, and `vpc_Card=VC`. Their signatures, merchant and amounts passed validation against current configuration. The latest was order `PAW-260905-NRKSCB`, recorded at `2026-09-05 02:32:16`. These are findings supplied by the parent audit; this research agent did not read customer records or `.env`.

OnePay's indexed integration documentation defines `F` as unsuccessful 3D Secure authentication. This identifies the failed stage; it does **not** distinguish incorrect input, an unfinished challenge, outdated sandbox data or a simulator problem. The evidence does not establish which of those happened. [Official integration PDF, response-code table](https://mtf.onepay.vn/developer/resource/documents/docx/introduction_merchant_integration.pdf)

## Official contract and code comparison

- The live official demo specifies sandbox endpoint `https://mtf.onepay.vn/paygate/vpcpay.op`, merchant `TESTONEPAY`, and its corresponding public demo access code. It documents `INTERNATIONAL`, `DOMESTIC` and `QR` as card-list values, amount multiplied by 100, unique transaction references, version `2` and command `pay`. The endpoint and request shape in `app/Services/OnePayGateway.php` agree with this demo. Do not mix credentials from different merchant products/environments. [Live official demo](https://mtf.onepay.vn/client/merchant_client_demo/)
- OnePay documents uppercase, 64-character HMAC-SHA256 using its merchant-specific hexadecimal key, alphabetically sorted `vpc_`/`user_` fields and original, unencoded `key=value` values. The service follows that structure. The observed valid response signatures provide additional evidence against a signature mismatch for these transactions. [Official English PDF](https://mtf.onepay.vn/developer/resource/documents/docx/introduction_merchant_integration.pdf), [official Vietnamese PDF](https://mtf.onepay.vn/developer/resource/documents/docx/quy_trinh_tich_hop-quocte.pdf)
- Other documented codes are `0` for success, `5` for insufficient balance/limit, `7` for bank/system processing error, and `99` for user cancellation. These should not replace the actual observed `F` diagnosis. [Official Vietnamese response table](https://mtf.onepay.vn/developer/resource/documents/docx/quy_trinh_tich_hop-quocte.pdf), [official English response table](https://mtf.onepay.vn/developer/resource/documents/docx/introduction_merchant_integration.pdf)

## Sandbox data and next step

The indexed official table lists international Visa/Mastercard test data with expiry `05/2024`. Its combined column is labelled `OTP/CVV`; the international rows show a CVV value, which does not establish a working 3D Secure challenge password. No current official challenge password or replacement expiry was verified. Consequently, do not invent either. [Official test-data table](https://mtf.onepay.vn/developer/resource/documents/docx/quy_trinh_tich_hop-quocte.pdf)

Obtain current international sandbox cards and 3D Secure challenge instructions from the merchant's OnePay onboarding contact or `support@onepay.vn`. Supply the sandbox transaction reference and observed `F` message, without the secure secret or full signed redirect URL. The support address appears on OnePay's own service guide. [Official OnePay contact](https://onepay.vn/huongdandangkydv/)

OnePay's customer guide explains that an incorrect authentication password or closing the challenge can prevent completion; this is general guidance, not proof that either occurred here. [Official payment guide](https://onepay.vn/documents/payment/guideVN.jsp?logos=v)

## Separate localhost callback limitation

The live demo distinguishes a browser receipt Return URL from a merchant IPN Callback URL. The parent audit found both configured to localhost. Networking inference: a browser on the developer's machine can reach that local return page, but OnePay's external server cannot reach that machine through `localhost`. Publicly reachable callback hosting/tunnelling is needed for IPN delivery. This does not explain the gateway-generated `F` authentication result. [Official callback/return fields](https://mtf.onepay.vn/client/merchant_client_demo/)

## Source freshness and verification limit

The official demo was retrieved live. Both integration PDF URLs, and the `/developer/` landing page, returned HTTP 404 during direct retrieval; their official-domain search-index excerpts remain available. PDF facts above therefore describe indexed documentation, not a freshly downloadable current release. The expired sample dates reinforce the need to obtain current sandbox data directly from OnePay.

The parent agent reports the 10 existing OnePay tests passed (63 assertions). Local tests verify application behavior and do not establish that OnePay's remote 3D Secure simulator succeeds.
