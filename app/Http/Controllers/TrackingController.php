<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrackingController extends Controller
{
    public function hit(Request $request)
    {
        $visitId = $request->input('tracking_visit_id');

        if (! $visitId) {
            return response()->json(['error' => 'No visit session'], 400);
        }

        $models = config('analytics.interactable_models');

        $data = $request->validate([
            'type' => ['required', Rule::in(config('analytics.interaction_types'))],
            'model' => ['required', Rule::in(array_keys($models))],
            'id' => ['required', 'integer', 'min:1'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:'.config('analytics.max_hit_duration', 60)],
        ]);

        \App\Models\Interaction::create([
            'visit_id' => $visitId,
            'interactable_type' => $models[$data['model']],
            'interactable_id' => $data['id'],
            'type' => $data['type'],
            'duration_seconds' => $data['duration'] ?? 0,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function fingerprint(Request $request)
    {
        $visitorId = $request->input('tracking_visitor_id');
        $hash = $request->input('hash');

        if (! $visitorId || ! $hash) {
            return response()->json(['error' => 'Missing data'], 400);
        }

        // Find current visitor
        $visitor = \App\Models\Visitor::find($visitorId);
        if (! $visitor) {
            return response()->json(['error' => 'Visitor not found'], 404);
        }

        // Check if this fingerprint hash already exists for another visitor
        $existingVisitor = \App\Models\Visitor::where('fingerprint_hash', $hash)
            ->where('id', '!=', $visitorId)
            ->first();

        if ($existingVisitor) {
            // Found a match! We should "Restore" identity to the old one.
            // 1. Move the current visit (the one started during this incognito load) to the old visitor
            \App\Models\Visit::where('visitor_id', $visitorId)->update(['visitor_id' => $existingVisitor->id]);

            // 2. Delete the temporary visitor record created at the start of this request
            $visitor->delete();

            return response()->json([
                'status' => 'recovered',
                'uuid' => $existingVisitor->uuid,
                'message' => 'Identity merged',
            ])->withCookie(cookie()->forever('qr_menu_visitor_id', $existingVisitor->uuid));
        }

        // No existing found, just save the hash to current visitor
        if (! $visitor->fingerprint_hash) {
            $visitor->update(['fingerprint_hash' => $hash]);
        }

        return response()->json(['status' => 'saved']);
    }

    public function toggleVote(Request $request)
    {
        $visitorId = $request->input('tracking_visitor_id');
        $productId = $request->input('product_id');

        if (! $visitorId || ! $productId) {
            return response()->json(['error' => 'Missing data'], 400);
        }

        $vote = \App\Models\Vote::where('visitor_id', $visitorId)
            ->where('product_id', $productId)
            ->first();

        if ($vote) {
            $vote->delete();

            return response()->json(['status' => 'removed']);
        }

        \App\Models\Vote::create([
            'visitor_id' => $visitorId,
            'product_id' => $productId,
        ]);

        return response()->json(['status' => 'added']);
    }
}
