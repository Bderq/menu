<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Campaign;
use App\Models\Store;

$store = Store::first(); // Assuming first store for debug
if (!$store) { echo "No stores found!"; exit; }
$storeId = $store->id;
echo "Debugging for Store: {$store->name} (ID: $storeId)\n";

$now = now();
$dayOfWeek = strtolower($now->format('l'));
$time = $now->format('H:i:s');
echo "Current Day: $dayOfWeek, Current Time: " . $now->toDateTimeString() . " DB Time: $time\n";

$campaigns = Campaign::query()
    ->where('is_active', true)
    ->whereHas('stores', function ($q) use ($storeId) {
        $q->where('stores.id', $storeId)
          ->where('campaign_store.is_active', true);
    })
    ->get();

echo "Total Active Campaigns for this store: " . $campaigns->count() . "\n";

foreach($campaigns as $c) {
    echo "ID: {$c->id}, Name: {$c->name}\n";
    
    // Check Date
    $dateCheck = ($c->start_date === null || $c->start_date <= $now) && ($c->end_date === null || $c->end_date >= $now);
    echo "  Date Check: " . ($dateCheck ? 'PASS' : 'FAIL') . " (Start: {$c->start_date}, End: {$c->end_date})\n";

    // Check Schedules
    if ($c->schedules->count() === 0) {
        echo "  Schedule Check: PASS (No schedules = 24/7)\n";
    } else {
        $anyScheduleMatch = false;
        foreach($c->schedules as $s) {
            $days = $s->days ?? [];
            $dayMatch = in_array($dayOfWeek, $days);
            
            // Time logic
            $timeMatch = false;
            if ($s->start_time <= $s->end_time) {
                if ($time >= $s->start_time && $time <= $s->end_time) $timeMatch = true;
            } else {
                // Overnight
                if ($time >= $s->start_time || $time <= $s->end_time) $timeMatch = true;
            }

            echo "    Schedule [".implode(',', $days)."] {$s->start_time}-{$s->end_time}: Day=".($dayMatch?'OK':'NO')." Time=".($timeMatch?'OK':'NO')."\n";
            if ($dayMatch && $timeMatch) $anyScheduleMatch = true;
        }
        echo "  Schedule Check: " . ($anyScheduleMatch ? 'PASS' : 'FAIL') . "\n";
    }
}
