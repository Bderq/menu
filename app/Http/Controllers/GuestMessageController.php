<?php

namespace App\Http\Controllers;

use App\Models\GuestMessage;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class GuestMessageController extends Controller
{
    public function store(Request $request, $store_slug)
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();

        $request->validate([
            'content' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $ip = $request->ip();

        $message = GuestMessage::create([
            'store_id'   => $store->id,
            'ip_address' => $ip,
            'content'    => $request->content,
        ]);

        if ($request->has('review_interaction_id')) {
            \App\Models\GoogleReviewInteraction::where('id', $request->review_interaction_id)
                ->update([
                    'feedback_submitted' => true,
                    'guest_message_id'   => $message->id,
                ]);
        }

        // Telegram bildirimi — hata olursa müşteriyi etkileme, sadece logla
        try {
            $botToken = config('services.telegram.bot_token');
            $chatId = config('services.telegram.chat_id');

            $text = "📣 *{$store->name}* - Yeni Sesverin Mesajı\n\n"
                . "{$message->content}\n\n"
                . now()->format('d.m.Y H:i');

            Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::warning('Telegram bildirimi gönderilemedi: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Mesajınız başarıyla iletildi.',
        ], 201);
    }
}

