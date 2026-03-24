<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:prune {days=90 : The number of days to keep}';

    protected $description = 'Prune old analytics data (interactions and visits)';

    public function handle()
    {
        $days = (int) $this->argument('days') ?: 90;
        $cutOff = now()->subDays($days);

        $interactionCount = \App\Models\Interaction::where('created_at', '<', $cutOff)->delete();
        $visitCount = \App\Models\Visit::where('started_at', '<', $cutOff)->delete();

        $this->info("Pruning complete: Deleted {$interactionCount} interactions and {$visitCount} visits older than {$days} days.");
    }
}
