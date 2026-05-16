<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PollResultsWidget extends Widget
{
    protected string $view = 'filament.widgets.poll-results-widget';

    protected int | string | array $columnSpan = 'full';

    public ?int $pollId = null;
    public ?int $storeId = null;

    protected $listeners = ['filterUpdated' => 'updateFilter'];

    public function updateFilter($filters)
    {
        $this->pollId = $filters['pollId'] ?? null;
        $this->storeId = $filters['storeId'] ?? null;
    }

    public function getResults()
    {
        if (!$this->pollId) {
            return null;
        }

        $poll = \App\Models\Poll::with('options')->find($this->pollId);
        
        if (!$poll) {
            return null;
        }

        $votes = \App\Models\PollVote::where('poll_id', $this->pollId)
            ->when($this->storeId, fn($q) => $q->where('store_id', $this->storeId))
            ->select('poll_option_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('poll_option_id')
            ->pluck('count', 'poll_option_id');

        $totalVotes = $votes->sum();

        $results = $poll->options->map(function ($option) use ($votes, $totalVotes) {
            $count = $votes[$option->id] ?? 0;
            return [
                'id' => $option->id,
                'text' => $option->text,
                'emoji' => $option->emoji,
                'count' => $count,
                'percentage' => $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0,
            ];
        });

        return [
            'poll_title' => $poll->title,
            'poll_question' => $poll->question,
            'total_votes' => $totalVotes,
            'options' => $results,
        ];
    }
}
