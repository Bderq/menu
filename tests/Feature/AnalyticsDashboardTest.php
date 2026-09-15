<?php

namespace Tests\Feature;

use App\Filament\Pages\AnalyticsDashboard;
use App\Filament\Widgets\AnalyticsStats;
use App\Filament\Widgets\VisitsTrendChart;
use App\Models\Interaction;
use App\Models\Store;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\AnalyticsService;
use App\Support\AnalyticsRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::create(['name' => 'Görükle', 'slug' => 'gorukle', 'theme_color' => '#ffb000']);
    }

    private function visit(int $daysAgo, ?int $heartbeatSeconds = null): Visit
    {
        $visitor = Visitor::create(['uuid' => (string) Str::uuid(), 'last_seen_at' => now()]);
        $visit = Visit::create(['visitor_id' => $visitor->id, 'store_id' => $this->store->id, 'started_at' => now()->subDays($daysAgo)]);

        if ($heartbeatSeconds !== null) {
            Interaction::create(['visit_id' => $visit->id, 'type' => 'heartbeat', 'duration_seconds' => $heartbeatSeconds, 'created_at' => $visit->started_at]);
        }

        return $visit;
    }

    public function test_range_presets_resolve_expected_windows(): void
    {
        $this->assertSame('24h', AnalyticsRange::fromFilters(null)->preset);
        $this->assertSame('7d', AnalyticsRange::fromFilters(['range' => '7d'])->preset);
        $this->assertSame('24h', AnalyticsRange::fromFilters(['range' => 'bogus'])->preset);

        $custom = AnalyticsRange::fromFilters(['range' => 'custom', 'from' => '2026-09-10', 'to' => '2026-09-01']);
        $this->assertSame('2026-09-01', $custom->from->toDateString(), 'reversed dates are swapped');
        $this->assertSame('2026-09-10', $custom->to->toDateString());
        $this->assertFalse($custom->isHourly());
        $this->assertTrue(AnalyticsRange::fromFilters(['range' => '24h'])->isHourly());
    }

    public function test_summary_respects_range_and_divides_dwell_by_engaged_visits_only(): void
    {
        $this->visit(0, 30);      // today, 30 s heartbeat
        $this->visit(0);          // today, no interaction
        $this->visit(2, 60);      // 2 days ago
        $this->visit(10);         // 10 days ago

        $service = app(AnalyticsService::class);

        $day = $service->summary(AnalyticsRange::fromFilters(['range' => '24h']));
        $this->assertSame(2, $day['sessions']);
        $this->assertSame(1, $day['engaged']);
        $this->assertSame(30.0, $day['avg_dwell'], 'dwell is not diluted by the visit without heartbeats');

        $week = $service->summary(AnalyticsRange::fromFilters(['range' => '7d']));
        $this->assertSame(3, $week['sessions']);
        $this->assertSame(45.0, $week['avg_dwell']);

        $month = $service->summary(AnalyticsRange::fromFilters(['range' => '30d']));
        $this->assertSame(4, $month['sessions']);

        $other = Store::create(['name' => 'Floyd', 'slug' => 'floyd', 'theme_color' => '#000']);
        $this->assertSame(0, $service->summary(AnalyticsRange::fromFilters(['range' => '30d', 'store_id' => $other->id]))['sessions']);
    }

    public function test_trend_fills_empty_buckets(): void
    {
        $this->visit(0, 10);
        $this->visit(3);

        $trend = app(AnalyticsService::class)->trend(AnalyticsRange::fromFilters(['range' => '7d']));

        $this->assertFalse($trend['hourly']);
        $this->assertCount(8, $trend['labels']);
        $this->assertSame(2, array_sum($trend['sessions']));
        $this->assertSame(1, array_sum($trend['engaged']));

        $hourly = app(AnalyticsService::class)->trend(AnalyticsRange::fromFilters(['range' => '24h']));
        $this->assertTrue($hourly['hourly']);
        $this->assertCount(25, $hourly['labels']);
    }

    public function test_dashboard_page_and_widgets_render_with_filters(): void
    {
        $this->actingAs(User::factory()->create());
        $this->visit(1, 10);

        $this->get(AnalyticsDashboard::getUrl())->assertOk()->assertSee('Menü Analitiği');

        Livewire::test(AnalyticsStats::class, ['pageFilters' => ['range' => '7d']])
            ->assertSeeText('Son 7 Gün')
            ->assertSeeText('Etkileşimli Oturum');

        Livewire::test(VisitsTrendChart::class, ['pageFilters' => ['range' => '30d']])
            ->assertSeeText('Son 30 Gün');
    }
}
