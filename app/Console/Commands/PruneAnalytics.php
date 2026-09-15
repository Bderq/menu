<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneAnalytics extends Command
{
    protected $signature = 'analytics:prune
        {--days=90 : Keep interactions, visits and review interactions newer than this many days}
        {--visitors-days=180 : Keep visitors seen within this many days (older ones are removed only if they have no visits, votes or review records)}
        {--dry-run : Report counts without deleting}';

    protected $description = 'Prune old analytics data (interactions, visits, orphan visitors, review interactions)';

    protected const CHUNK = 5000;

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $visitorDays = max($days, (int) $this->option('visitors-days'));
        $dryRun = (bool) $this->option('dry-run');

        $cutOff = now()->subDays($days);
        $visitorCutOff = now()->subDays($visitorDays);

        $steps = [
            'interactions' => DB::table('interactions')->where('created_at', '<', $cutOff),
            'visits' => DB::table('visits')->where('started_at', '<', $cutOff),
            'google_review_interactions' => DB::table('google_review_interactions')->where('showed_at', '<', $cutOff),
            'visitors' => DB::table('visitors')
                ->where('last_seen_at', '<', $visitorCutOff)
                ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('visits')->whereColumn('visits.visitor_id', 'visitors.id'))
                ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('votes')->whereColumn('votes.visitor_id', 'visitors.id'))
                ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('poll_votes')->whereColumn('poll_votes.visitor_id', 'visitors.id'))
                ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('google_review_interactions')->whereColumn('google_review_interactions.visitor_id', 'visitors.id')),
        ];

        $summary = [];

        foreach ($steps as $table => $query) {
            $summary[$table] = $dryRun ? $query->count() : $this->deleteInChunks($query);
            $this->info(sprintf('%s%s: %d', $dryRun ? '[dry-run] ' : '', $table, $summary[$table]));
        }

        Log::info('analytics:prune', ['dry_run' => $dryRun, 'days' => $days, 'visitors_days' => $visitorDays] + $summary);

        return self::SUCCESS;
    }

    protected function deleteInChunks(Builder $query): int
    {
        $total = 0;

        do {
            $deleted = (clone $query)->whereIn('id', (clone $query)->select('id')->limit(self::CHUNK))->delete();
            $total += $deleted;
        } while ($deleted > 0);

        return $total;
    }
}
