<?php

namespace App\Console\Commands;

use App\Services\TelegramCallbackHandler;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramPoll extends Command
{
    protected $signature = 'telegram:poll {--max-time=3600 : Exit after this many seconds so the supervisor restarts a fresh process}';

    protected $description = 'Long-poll Telegram for button presses (fallback when the webhook cannot reach this server)';

    private const OFFSET_KEY = 'telegram_poll_offset';

    public function handle(TelegramService $telegram, TelegramCallbackHandler $handler): int
    {
        $deadline = time() + (int) $this->option('max-time');
        $offset = (int) Cache::get(self::OFFSET_KEY, 0);

        $this->info("Polling Telegram (offset {$offset})...");

        while (time() < $deadline) {
            try {
                $updates = $telegram->getUpdates($offset, 30);
            } catch (\Throwable $e) {
                Log::warning('Telegram getUpdates failed: ' . $e->getMessage());
                sleep(5);
                continue;
            }

            foreach ($updates as $update) {
                $offset = $update['update_id'] + 1;
                Cache::forever(self::OFFSET_KEY, $offset);

                if (!isset($update['callback_query'])) {
                    continue;
                }

                try {
                    $handler->handle($update['callback_query']);
                    $this->line(now()->format('H:i:s') . ' callback ' . ($update['callback_query']['data'] ?? '?'));
                } catch (\Throwable $e) {
                    Log::error('Telegram callback işlenemedi: ' . $e->getMessage(), ['data' => $update['callback_query']['data'] ?? null]);
                }
            }
        }

        return self::SUCCESS;
    }
}
