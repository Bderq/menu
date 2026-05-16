<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\PollImpression;
use App\Models\Store;
use App\Models\Visitor;
use App\Services\PollSchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    protected $schedulingService;

    public function __construct(PollSchedulingService $schedulingService)
    {
        $this->schedulingService = $schedulingService;
    }

    /**
     * Get the active poll for the popup.
     */
    public function active(Request $request, $store_slug)
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();
        $visitorId = $request->tracking_visitor_id ?? $request->header('X-Visitor-Id');

        if (!$visitorId) {
            return response()->json(null);
        }

        $poll = $this->schedulingService->resolveActivePoll($store->id);

        if (!$poll) {
            return response()->json(null);
        }

        // Check if this is at least the 2nd visit since the poll was published
        // We use the poll's creation date or the first schedule's start date
        $pollActivationDate = $poll->schedules()->min('starts_at') ?? $poll->created_at;
        
        $visitCount = \App\Models\Visit::where('visitor_id', $visitorId)
            ->where('started_at', '>=', $pollActivationDate)
            ->count();

        if ($visitCount < 2) {
            return response()->json(null);
        }

        // Check if visitor already voted
        $hasVoted = PollVote::where('poll_id', $poll->id)
            ->where('visitor_id', $visitorId)
            ->exists();

        if ($hasVoted) {
            return response()->json(null);
        }

        // Check show_once restriction
        if ($poll->show_once) {
            $hasSeen = PollImpression::where('poll_id', $poll->id)
                ->where('visitor_id', $visitorId)
                ->exists();
            
            if ($hasSeen) {
                return response()->json(null);
            }
        }

        // Record impression
        PollImpression::updateOrCreate(
            ['poll_id' => $poll->id, 'visitor_id' => $visitorId],
            ['shown_at' => now()]
        );

        return response()->json([
            'id' => $poll->id,
            'title' => $poll->title,
            'question' => $poll->question,
            'type' => $poll->type,
            'options' => $poll->options->map(fn($o) => [
                'id' => $o->id,
                'text' => $o->text,
                'emoji' => $o->emoji,
            ]),
        ]);
    }

    /**
     * Vote for a poll.
     */
    public function vote(Request $request, $store_slug, Poll $poll)
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();
        $visitorId = $request->tracking_visitor_id ?? $request->header('X-Visitor-Id');

        $request->validate([
            'option_id' => ['required', 'exists:poll_options,id'],
        ]);

        // Verify option belongs to poll
        $option = PollOption::where('id', $request->option_id)
            ->where('poll_id', $poll->id)
            ->firstOrFail();

        try {
            PollVote::create([
                'poll_id' => $poll->id,
                'poll_option_id' => $option->id,
                'visitor_id' => $visitorId,
                'store_id' => $store->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Zaten oy kullandınız.'], 409);
        }

        return $this->getPollResults($poll);
    }

    /**
     * Get all active polls (for the drawer tab).
     */
    public function index(Request $request, $store_slug)
    {
        $store = Store::where('slug', $store_slug)->firstOrFail();
        $visitorId = $request->tracking_visitor_id ?? $request->header('X-Visitor-Id');

        \Illuminate\Support\Facades\Log::info('Poll Index API Hit', [
            'slug' => $store_slug,
            'visitor_id' => $visitorId,
            'tracking_id' => $request->tracking_visitor_id
        ]);

        $polls = Poll::activeNow($store->id)
            ->with(['options', 'votes' => fn($q) => $q->where('visitor_id', $visitorId)])
            ->get();

        return response()->json($polls->map(function ($poll) {
            $votedOptionId = $poll->votes->first()?->poll_option_id;
            
            $data = [
                'id' => $poll->id,
                'title' => $poll->title,
                'question' => $poll->question,
                'type' => $poll->type,
                'voted_option_id' => $votedOptionId,
                'options' => $poll->options->map(fn($o) => [
                    'id' => $o->id,
                    'text' => $o->text,
                    'emoji' => $o->emoji,
                ]),
            ];

            if ($votedOptionId) {
                $data['results'] = $this->getPollResults($poll)->original;
            }

            return $data;
        }));
    }

    protected function getPollResults(Poll $poll)
    {
        $results = PollVote::where('poll_id', $poll->id)
            ->select('poll_option_id', DB::raw('count(*) as count'))
            ->groupBy('poll_option_id')
            ->pluck('count', 'poll_option_id');

        $total = $results->sum();

        return response()->json([
            'total_votes' => $total,
            'results' => $poll->options->map(fn($o) => [
                'id' => $o->id,
                'count' => $results[$o->id] ?? 0,
                'percentage' => $total > 0 ? round((($results[$o->id] ?? 0) / $total) * 100) : 0,
            ]),
        ]);
    }
}
