<?php

namespace App\Services;

use App\Models\SupportTicket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAiSupportService
{
    public function reply(SupportTicket $ticket): ?string
    {
        $apiKey = trim((string) config('services.openai.api_key'));
        if ($apiKey === '' || ! $ticket->ai_enabled || $ticket->operator_active) {
            return null;
        }

        $history = $ticket->replies()
            ->latest('id')
            ->limit(12)
            ->get()
            ->reverse()
            ->map(fn ($reply): array => [
                'role' => $reply->is_admin_reply ? 'assistant' : 'user',
                'content' => $this->redactSensitive(
                    mb_substr(strip_tags((string) $reply->message), 0, 2000)
                ),
            ])
            ->values()
            ->all();

        array_unshift($history, [
            'role' => 'system',
            'content' => implode(' ', [
                'You are the Mili Taxi support assistant.',
                'Reply concisely in the same language as the customer.',
                'Never claim that a payment, refund, ride, account change, or safety action was completed.',
                'Never request passwords, payment-card details, OTP codes, API keys, or other secrets.',
                'For emergencies, account disputes, payment disputes, or uncertain facts, say an operator will review the chat.',
            ]),
        ]);

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(20)
                ->retry(2, 300, throw: false)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => config('services.openai.model', 'gpt-4.1-mini'),
                    'input' => $history,
                    'max_output_tokens' => 300,
                    'store' => false,
                ]);

            if (! $response->successful()) {
                Log::warning('OpenAI support response failed.', [
                    'ticket_id' => $ticket->id,
                    'http_status' => $response->status(),
                ]);

                return null;
            }

            foreach ((array) $response->json('output', []) as $output) {
                foreach ((array) data_get($output, 'content', []) as $content) {
                    $text = data_get($content, 'text');
                    if (is_string($text) && trim($text) !== '') {
                        return mb_substr(trim($text), 0, 2000);
                    }
                }
            }
        } catch (Throwable $exception) {
            Log::warning('OpenAI support request failed.', [
                'ticket_id' => $ticket->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        return null;
    }

    private function redactSensitive(string $message): string
    {
        $message = preg_replace(
            '/\b(?:\d[ -]*?){12,19}\b/u',
            '[REDACTED PAYMENT DATA]',
            $message
        ) ?? $message;
        $message = preg_replace(
            '/\b(?:sk-[A-Za-z0-9_-]{16,}|AIza[A-Za-z0-9_-]{20,})\b/u',
            '[REDACTED API KEY]',
            $message
        ) ?? $message;

        return preg_replace(
            '/\b(password|otp|pin|api[ _-]?key)\b\s*[:=]?\s*\S+/iu',
            '$1: [REDACTED]',
            $message
        ) ?? $message;
    }
}
