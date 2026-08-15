<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MNotifyService
{
    public function isConfigured(): bool
    {
        return (bool) config('mnotify.enabled')
            && trim((string) config('mnotify.api_key')) !== ''
            && trim((string) config('mnotify.sender_id')) !== '';
    }

    /**
     * @param  array<int, string>  $recipients
     * @return array<string, mixed>|null
     */
    public function sendQuickSms(array $recipients, string $message): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $recipients = array_values(array_filter(array_unique($recipients)));

        if ($recipients === []) {
            return null;
        }

        $response = Http::acceptJson()
            ->asJson()
            ->post($this->endpoint('/sms/quick'), [
                'recipient' => $recipients,
                'sender' => substr((string) config('mnotify.sender_id'), 0, 11),
                'message' => $message,
                'is_schedule' => false,
            ]);

        $payload = $response->json();

        if (! $response->successful() || ($payload['status'] ?? null) !== 'success') {
            Log::warning('mNotify SMS failed', [
                'status' => $response->status(),
                'response' => $payload,
            ]);

            return null;
        }

        return is_array($payload) ? $payload : null;
    }

    private function endpoint(string $path): string
    {
        $baseUrl = rtrim((string) config('mnotify.base_url'), '/');
        $apiKey = urlencode((string) config('mnotify.api_key'));

        return $baseUrl.$path.'?key='.$apiKey;
    }
}
