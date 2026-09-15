<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Interaction;
use App\Models\Product;
use App\Models\Store;
use App\Models\Visit;
use App\Models\Visitor;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PruneAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function visitor(int $daysAgo): Visitor
    {
        return Visitor::create(['uuid' => (string) Str::uuid(), 'last_seen_at' => now()->subDays($daysAgo), 'created_at' => now()->subDays($daysAgo)]);
    }

    public function test_prunes_old_rows_and_keeps_recent_or_referenced_ones(): void
    {
        $store = Store::create(['name' => 'G', 'slug' => 'gorukle', 'theme_color' => '#fff']);
        $category = Category::create(['name' => 'İçecek', 'slug' => 'icecek']);
        $product = Product::create(['name' => 'Ayran', 'category_id' => $category->id]);

        $recent = $this->visitor(1);
        $recentVisit = Visit::create(['visitor_id' => $recent->id, 'store_id' => $store->id, 'started_at' => now()->subDay()]);
        Interaction::create(['visit_id' => $recentVisit->id, 'type' => 'click', 'created_at' => now()->subDay()]);

        $old = $this->visitor(100);
        $oldVisit = Visit::create(['visitor_id' => $old->id, 'store_id' => $store->id, 'started_at' => now()->subDays(100)]);
        Interaction::create(['visit_id' => $oldVisit->id, 'type' => 'click', 'created_at' => now()->subDays(100)]);

        $orphanOld = $this->visitor(200);          // no visits → removed
        $orphanRecent = $this->visitor(30);        // no visits but recent → kept
        $voter = $this->visitor(200);              // old but has a vote → kept
        Vote::create(['visitor_id' => $voter->id, 'product_id' => $product->id]);

        $this->artisan('analytics:prune', ['--days' => 90, '--visitors-days' => 180])->assertSuccessful();

        $this->assertDatabaseHas('visits', ['id' => $recentVisit->id]);
        $this->assertDatabaseMissing('visits', ['id' => $oldVisit->id]);
        $this->assertDatabaseCount('interactions', 1);

        $this->assertDatabaseHas('visitors', ['id' => $recent->id]);
        $this->assertDatabaseHas('visitors', ['id' => $orphanRecent->id]);
        $this->assertDatabaseHas('visitors', ['id' => $voter->id]);
        $this->assertDatabaseMissing('visitors', ['id' => $orphanOld->id]);
        // $old was seen 100 days ago, inside the 180-day visitor window → kept even though its visit was pruned.
        $this->assertDatabaseHas('visitors', ['id' => $old->id]);
    }

    public function test_dry_run_deletes_nothing(): void
    {
        $this->visitor(400);

        $this->artisan('analytics:prune', ['--dry-run' => true])
            ->expectsOutputToContain('[dry-run] visitors: 1')
            ->assertSuccessful();

        $this->assertDatabaseCount('visitors', 1);
    }
}
