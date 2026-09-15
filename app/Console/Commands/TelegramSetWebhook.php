<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook';

    protected $description = 'Register the Telegram bot webhook (callback buttons) with the configured secret';

    public function handle(TelegramService $telegram): int
    {
        $secret = (string) config('services.telegram.webhook_secret');

        if ($secret === '') {
            $this->error('TELEGRAM_WEBHOOK_SECRET is not set.');
            return self::FAILURE;
        }

        $url = rtrim(config('app.url'), '/') . '/telegram/webhook';
        $result = $telegram->setWebhook($url, $secret);

        $this->line(json_encode($result, JSON_UNESCAPED_UNICODE));

        return ($result['ok'] ?? false) ? self::SUCCESS : self::FAILURE;
    }
}
