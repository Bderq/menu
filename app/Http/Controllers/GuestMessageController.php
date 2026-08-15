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

        // WhatsApp Bot bildirimi — hata olursa müşteriyi etkileme, sadece logla
        try {
            Http::timeout(5)
                ->withHeaders(['X-Webhook-Secret' => env('WPBOT_WEBHOOK_SECRET')])
                ->post(env('WPBOT_WEBHOOK_URL'), [
                    'store'    => $store->name,
                    'message'  => $message->content,
                    'sent_at'  => now()->format('d.m.Y H:i'),
                ]);
        } catch (\Exception $e) {
            Log::warning('WA Bot bildirimi gönderilemedi: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Mesajınız başarıyla iletildi.',
        ], 201);
    }
}

