<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackVisitor;
use App\Models\Store;
use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingHitValidationTest extends TestCase
{
    use RefreshDatabase;

    private const UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 Safari/604.1';

    protected function setUp(): void
    {
        parent::setUp();

        $store = Store::create(['name' => 'Görükle', 'slug' => 'gorukle', 'theme_color' => '#ffb000']);
        $visitor = Visitor::create(['uuid' => '7f1c2d3e-4a5b-4c6d-8e9f-0a1b2c3d4e5f', 'last_seen_at' => now()]);
        Visit::create(['visitor_id' => $visitor->id, 'store_id' => $store->id, 'started_at' => now()]);
    }

    private function hit(array $payload)
    {
        return $this->withCookie(TrackVisitor::COOKIE, '7f1c2d3e-4a5b-4c6d-8e9f-0a1b2c3d4e5f')
            ->withHeader('User-Agent', self::UA)
            ->withCredentials()->postJson('/tracking/hit', $payload);
    }

    public function test_valid_hit_is_stored(): void
    {
        $this->hit(['type' => 'heartbeat', 'model' => 'Category', 'id' => 7, 'duration' => 10])->assertOk();

        $this->assertDatabaseHas('interactions', [
            'interactable_type' => \App\Models\Category::class,
            'interactable_id' => 7,
            'type' => 'heartbeat',
            'duration_seconds' => 10,
        ]);
    }

    public function test_invalid_payloads_are_rejected(): void
    {
        $this->hit(['type' => 'foo', 'model' => 'Product', 'id' => 1])->assertStatus(422);
        $this->hit(['type' => 'click', 'model' => 'Hacker', 'id' => 1])->assertStatus(422);
        $this->hit(['type' => 'click', 'model' => 'Product', 'id' => 'abc'])->assertStatus(422);
        $this->hit(['type' => 'heartbeat', 'model' => 'Category', 'id' => 1, 'duration' => 9999])->assertStatus(422);

        $this->assertDatabaseCount('interactions', 0);
    }

    public function test_hit_without_cookie_is_rejected(): void
    {
        $this->withHeader('User-Agent', self::UA)
            ->withCredentials()->postJson('/tracking/hit', ['type' => 'click', 'model' => 'Product', 'id' => 1])
            ->assertStatus(400);

        $this->assertDatabaseCount('interactions', 0);
        // No throwaway visitor is created for a cookie-less tracking call.
        $this->assertDatabaseCount('visitors', 1);
    }
}
