<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Campaign;
use Carbon\Carbon;

$now = now();
$dayOfWeek = strtolower($now->format('l'));
echo "Current Day: $dayOfWeek, Current Time: " . $now->toDateTimeString() . "\n";

$campaigns = Campaign::where('is_active', true)->get();
echo "Total Active Campaigns: " . $campaigns->count() . "\n";

foreach($campaigns as $c) {
    echo "ID: {$c->id}, Name: {$c->name}\n";
    echo "  Date Range: " . ($c->start_date ?? 'NULL') . " to " . ($c->end_date ?? 'NULL') . "\n";
    
    $inRange = true;
    if ($c->start_date && $c->start_date > $now) $inRange = false;
    if ($c->end_date && $c->end_date < $now) $inRange = false;
    echo "  In Date Range: " . ($inRange ? 'YES' : 'NO') . "\n";

    if ($c->schedules->count() > 0) {
        foreach($c->schedules as $s) {
            echo "  Schedule Days: " . json_encode($s->days) . " Time: {$s->start_time} - {$s->end_time}\n";
            $days = $s->days ?? [];
            if (in_array($dayOfWeek, $days)) {
                echo "    Matches Today: YES\n";
            } else {
                echo "    Matches Today: NO\n";
            }
        }
    } else {
        echo "  No schedules (24/7)\n";
    }
}
