<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackVisitor;
use App\Models\Store;
use App\Models\StoreTable;
use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackVisitorMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private const UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 Safari/604.1';

    private function store(): Store
    {
        return Store::create(['name' => 'Görükle', 'slug' => 'gorukle', 'theme_color' => '#ffb000']);
    }

    public function test_unknown_slug_creates_no_visitor_or_visit(): void
    {
        $this->store();

        $this->withHeader('User-Agent', self::UA)->get('/.env')->assertNotFound();
        $this->withHeader('User-Agent', self::UA)->get('/wp-login.php')->assertNotFound();
        $this->withHeader('User-Agent', self::UA)->get('/phpinfo')->assertNotFound();

        $this->assertDatabaseCount('visitors', 0);
        $this->assertDatabaseCount('visits', 0);
    }

    public function test_bots_and_http_clients_are_not_tracked(): void
    {
        $this->store();

        foreach (['curl/8.7.1', 'Mozilla/5.0 (compatible; HealthCheck/1.0)', 'python-requests/2.32', 'Go-http-client/1.1', ''] as $ua) {
            $this->withHeader('User-Agent', $ua)->get('/gorukle');
        }

        $this->assertDatabaseCount('visitors', 0);
        $this->assertDatabaseCount('visits', 0);
    }

    public function test_real_menu_load_opens_visitor_and_visit_with_store_and_cookie(): void
    {
        $store = $this->store();

        $response = $this->withHeader('User-Agent', self::UA)
            ->withHeader('Referer', 'https://l.instagram.com/')
            ->get('/gorukle?utm_source=insta');

        $response->assertOk()->assertCookie(TrackVisitor::COOKIE);

        $this->assertDatabaseCount('visitors', 1);
        $this->assertDatabaseHas('visits', [
            'store_id' => $store->id,
            'referer_host' => 'l.instagram.com',
            'utm_source' => 'insta',
        ]);
    }

    public function test_same_cookie_within_session_window_reuses_visit(): void
    {
        $this->store();
        $first = $this->withHeader('User-Agent', self::UA)->get('/gorukle');
        $uuid = $first->getCookie(TrackVisitor::COOKIE)->getValue();

        $this->withCookie(TrackVisitor::COOKIE, $uuid)
            ->withHeader('User-Agent', self::UA)
            ->get('/gorukle');

        $this->assertDatabaseCount('visitors', 1);
        $this->assertDatabaseCount('visits', 1);
    }

    public function test_table_qr_attaches_table_to_visit(): void
    {
        $store = $this->store();
        $table = StoreTable::create(['store_id' => $store->id, 'name' => 'Masa 5', 'qr_token' => 'tok-5']);

        $this->withHeader('User-Agent', self::UA)->get('/gorukle?masa=tok-5')->assertOk();

        $this->assertDatabaseHas('visits', ['store_id' => $store->id, 'table_id' => $table->id]);
    }

    public function test_tracking_hit_without_live_visit_does_not_open_storeless_visit(): void
    {
        $store = $this->store();
        $visitor = Visitor::create(['uuid' => '0c4a6b2e-1d2f-4c3b-9a8e-123456789abc', 'last_seen_at' => now()]);
        Visit::create(['visitor_id' => $visitor->id, 'store_id' => $store->id, 'started_at' => now()->subHours(2)]);

        $this->withCookie(TrackVisitor::COOKIE, '0c4a6b2e-1d2f-4c3b-9a8e-123456789abc')
            ->withHeader('User-Agent', self::UA)
            ->withCredentials()->postJson('/tracking/hit', ['type' => 'click', 'model' => 'Product', 'id' => 1])
            ->assertStatus(400);

        $this->assertDatabaseCount('visits', 1);
        $this->assertDatabaseCount('interactions', 0);
    }
}
