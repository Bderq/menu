<?php

namespace App\Http\Controllers;

use App\Models\GoogleReviewInteraction;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoogleReviewInteractionController extends Controller
{
    public function store(Request $request, $store_slug)
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();
        $visitorId = $request->tracking_visitor_id;

        if (!$visitorId) {
            return response()->json(['message' => 'Visitor not tracked'], 400);
        }

        $interaction = GoogleReviewInteraction::create([
            'visitor_id' => $visitorId,
            'store_id' => $store->id,
            'status' => 'showed',
            'showed_at' => now(),
        ]);

        return response()->json([
            'id' => $interaction->id,
            'status' => $interaction->status,
        ]);
    }

    public function update(Request $request, $store_slug, $id)
    {
        $interaction = GoogleReviewInteraction::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:accepted,rejected,dismissed',
        ]);

        $interaction->update([
            'status' => $request->status,
            'responded_at' => now(),
        ]);

        return response()->json([
            'id' => $interaction->id,
            'status' => $interaction->status,
        ]);
    }

    public function googleClicked($store_slug, $id)
    {
        $interaction = GoogleReviewInteraction::findOrFail($id);
        
        $interaction->update([
            'google_redirected' => true,
        ]);

        return response()->json([
            'id' => $interaction->id,
            'google_redirected' => $interaction->google_redirected,
        ]);
    }
}
