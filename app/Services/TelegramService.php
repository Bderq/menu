<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $botToken;
    private string $chatId;

    public function __construct()
    {
        $this->botToken = (string) config('services.telegram.bot_token');
        $this->chatId = (string) config('services.telegram.chat_id');
    }

    public static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Send an HTML message to the configured chat. Returns the Telegram message id.
     */
    public function sendMessage(string $html, ?array $inlineKeyboard = null): int
    {
        $payload = [
            'chat_id' => $this->chatId,
            'text' => $html,
            'parse_mode' => 'HTML',
        ];

        if ($inlineKeyboard !== null) {
            $payload['reply_markup'] = ['inline_keyboard' => $inlineKeyboard];
        }

        $response = $this->call('sendMessage', $payload);

        return (int) $response->json('result.message_id');
    }

    public function editMessage(int $messageId, string $html, ?array $inlineKeyboard = null): void
    {
        $this->call('editMessageText', [
            'chat_id' => $this->chatId,
            'message_id' => $messageId,
            'text' => $html,
            'parse_mode' => 'HTML',
            'reply_markup' => ['inline_keyboard' => $inlineKeyboard ?? []],
        ]);
    }

    public function answerCallback(string $callbackQueryId, string $text, bool $alert = false): void
    {
        try {
            $this->call('answerCallbackQuery', [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $alert,
            ]);
        } catch (\Throwable $e) {
            // Telegram rejects answers to callbacks older than ~30s; the action itself already happened.
            Log::info('Telegram answerCallback skipped: ' . $e->getMessage());
        }
    }

    /**
     * Long-poll for updates. Only works while no webhook is registered.
     */
    public function getUpdates(int $offset, int $timeoutSeconds = 30): array
    {
        return Http::timeout($timeoutSeconds + 10)
            ->post("https://api.telegram.org/bot{$this->botToken}/getUpdates", [
                'offset' => $offset,
                'timeout' => $timeoutSeconds,
                'allowed_updates' => ['callback_query'],
            ])
            ->throw()
            ->json('result', []);
    }

    public function deleteWebhook(bool $dropPendingUpdates = false): array
    {
        return $this->call('deleteWebhook', ['drop_pending_updates' => $dropPendingUpdates])->json();
    }

    public function setWebhook(string $url, string $secret): array
    {
        return $this->call('setWebhook', [
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => ['callback_query'],
            'drop_pending_updates' => true,
        ])->json();
    }

    private function call(string $method, array $payload): \Illuminate\Http\Client\Response
    {
        return Http::timeout(10)
            ->post("https://api.telegram.org/bot{$this->botToken}/{$method}", $payload)
            ->throw();
    }
}
