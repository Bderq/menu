<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\PollDisplayLog;
use Illuminate\Support\Facades\DB;

class PollSchedulingService
{
    /**
     * Resolve the active poll for a store at the current time.
     * Implements fair rotation if multiple polls are active.
     */
    public function resolveActivePoll($storeId = null)
    {
        $activePolls = Poll::activeNow($storeId)->get();

        if ($activePolls->isEmpty()) {
            return null;
        }

        if ($activePolls->count() === 1) {
            $selectedPoll = $activePolls->first();
            $this->logDisplay($selectedPoll, $storeId);
            return $selectedPoll;
        }

        // Fair Rotation Logic
        $today = now()->toDateString();
        
        // Find polls that haven't been shown today for this store
        $shownTodayIds = PollDisplayLog::where('store_id', $storeId)
            ->where('shown_date', $today)
            ->pluck('poll_id');

        $notShownToday = $activePolls->whereNotIn('id', $shownTodayIds->toArray());

        if ($notShownToday->isNotEmpty()) {
            $selectedPoll = $notShownToday->random();
        } else {
            // All active polls were shown today, pick one at random
            $selectedPoll = $activePolls->random();
        }

        $this->logDisplay($selectedPoll, $storeId);

        return $selectedPoll;
    }

    protected function logDisplay(Poll $poll, $storeId = null)
    {
        if (!$storeId) {
            return; // Cannot log without store context
        }

        PollDisplayLog::updateOrCreate(
            [
                'poll_id' => $poll->id,
                'store_id' => $storeId,
                'shown_date' => now()->toDateString(),
            ]
        );
    }
}
