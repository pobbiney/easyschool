<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackService
{
    public function initializeTransaction(
        string $email,
        int $amountInPesewas,
        string $reference,
        array $metadata = []
    ): array {
        $response = Http::withToken($this->secretKey())
            ->acceptJson()
            ->post($this->baseUrl().'/transaction/initialize', [
                'email' => $email,
                'amount' => $amountInPesewas,
                'reference' => $reference,
                'currency' => config('paystack.currency', 'GHS'),
                'metadata' => $metadata,
            ]);

        $payload = $response->json();

        if (! $response->successful() || ! ($payload['status'] ?? false)) {
            throw new RuntimeException($payload['message'] ?? 'Unable to initialize Paystack transaction.');
        }

        return $payload['data'] ?? [];
    }

    public function verifyTransaction(string $reference): array
    {
        $response = Http::withToken($this->secretKey())
            ->acceptJson()
            ->get($this->baseUrl().'/transaction/verify/'.urlencode($reference));

        $payload = $response->json();

        if (! $response->successful() || ! ($payload['status'] ?? false)) {
            throw new RuntimeException($payload['message'] ?? 'Unable to verify Paystack transaction.');
        }

        return $payload['data'] ?? [];
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        $secret = $this->secretKey();

        if ($secret === '') {
            return false;
        }

        $computed = hash_hmac('sha512', $payload, $secret);

        return hash_equals($computed, $signature);
    }

    public function publicKey(): string
    {
        return (string) config('paystack.public_key');
    }

    public function isConfigured(): bool
    {
        return $this->publicKey() !== '' && $this->secretKey() !== '';
    }

    private function secretKey(): string
    {
        return (string) config('paystack.secret_key');
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('paystack.base_url', 'https://api.paystack.co'), '/');
    }
}
