<?php

namespace App\Http\Controllers;

use App\Models\GuestMessage;
use App\Models\Store;
use Illuminate\Http\Request;
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
        
        // Bu IP ve Store için bugün kaç mesaj atılmış kontrolü RateLimiter ile web.php'de yapılacak
        // Ancak burada manuel bir ek kontrol veya loglama yapılabilir.

        $message = GuestMessage::create([
            'store_id' => $store->id,
            'ip_address' => $ip,
            'content' => $request->content,
        ]);

        if ($request->has('review_interaction_id')) {
            \App\Models\GoogleReviewInteraction::where('id', $request->review_interaction_id)
                ->update([
                    'feedback_submitted' => true,
                    'guest_message_id' => $message->id,
                ]);
        }

        return response()->json([
            'message' => 'Mesajınız başarıyla iletildi.',
        ], 201);
    }
}
