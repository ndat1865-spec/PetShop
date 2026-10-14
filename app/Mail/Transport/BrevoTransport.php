<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Sends mail through Brevo's HTTPS API, because Railway's Trial/Hobby plans block outbound SMTP.
 */
class BrevoTransport extends AbstractTransport
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(private ?string $key, private bool $verifySsl = true)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if (blank($this->key)) {
            throw new TransportException('BREVO_API_KEY is not set.');
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $sender = $email->getFrom()[0] ?? $message->getEnvelope()->getSender();

        $payload = array_filter([
            'sender' => $this->address($sender),
            'to' => $this->addresses($email->getTo()),
            'cc' => $this->addresses($email->getCc()),
            'bcc' => $this->addresses($email->getBcc()),
            'replyTo' => isset($email->getReplyTo()[0]) ? $this->address($email->getReplyTo()[0]) : null,
            'subject' => $email->getSubject(),
            'htmlContent' => $this->body($email->getHtmlBody()),
            'textContent' => $this->body($email->getTextBody()),
            'attachment' => array_map(fn ($part) => [
                'name' => $part->getFilename() ?? 'attachment',
                'content' => base64_encode($part->getBody()),
            ], $email->getAttachments()),
        ]);

        $request = Http::acceptJson()
            ->withHeaders(['api-key' => $this->key])
            ->timeout(10);

        if (! $this->verifySsl) {
            $request = $request->withoutVerifying();
        }

        try {
            $response = $request->post(self::ENDPOINT, $payload);
        } catch (\Throwable $exception) {
            throw new TransportException('Request to Brevo API failed: '.$exception->getMessage(), 0, $exception);
        }

        if (! $response->successful()) {
            throw new TransportException(sprintf(
                'Brevo API returned %d: %s',
                $response->status(),
                $response->json('message') ?? $response->body(),
            ));
        }

        if (filled($response->json('messageId'))) {
            $message->setMessageId((string) $response->json('messageId'));
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }

    private function address(Address $address): array
    {
        return array_filter(['email' => $address->getAddress(), 'name' => $address->getName()]);
    }

    /**
     * @param  Address[]  $addresses
     */
    private function addresses(array $addresses): array
    {
        return array_map(fn (Address $address) => $this->address($address), $addresses);
    }

    private function body($body): ?string
    {
        return is_resource($body) ? stream_get_contents($body) : $body;
    }
}
