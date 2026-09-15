<?php

namespace App\Http\Controllers;

use App\Services\TelegramCallbackHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramCallbackHandler $handler): Response
    {
        $expected = (string) config('services.telegram.webhook_secret');
        $given = (string) $request->header('X-Telegram-Bot-Api-Secret-Token');

        abort_unless($expected !== '' && hash_equals($expected, $given), 403);

        $callback = $request->input('callback_query');

        if (!is_array($callback)) {
            return response()->noContent();
        }

        try {
            $handler->handle($callback);
        } catch (\Throwable $e) {
            Log::error('Telegram callback işlenemedi: ' . $e->getMessage(), ['data' => $callback['data'] ?? null]);
        }

        return response()->noContent();
    }
}
