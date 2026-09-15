<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Models\StoreTable;
use App\Models\Visit;
use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public const COOKIE = 'qr_menu_visitor_id';

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isUntrackedPath($request) || $this->isBot($request)) {
            return $next($request);
        }

        $storeId = null;
        if ($slug = $request->route('store_slug')) {
            $storeId = $this->resolveStoreId($slug);

            // Unknown slug (scanners hitting /.env, /wp-login.php, …): never open a record.
            if (! $storeId) {
                return $next($request);
            }
        }

        $uuid = $request->cookie(self::COOKIE);
        $visitor = $uuid ? Visitor::where('uuid', $uuid)->first() : null;

        if (! $visitor) {
            $uuid = (string) Str::uuid();
            $visitor = Visitor::create([
                'uuid' => $uuid,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_seen_at' => now(),
            ]);
        } else {
            $visitor->update(['last_seen_at' => now()]);
        }

        $tableId = null;
        if ($storeId && $request->query('masa')) {
            $tableId = StoreTable::where('store_id', $storeId)
                ->where('qr_token', $request->query('masa'))
                ->value('id');
        }

        // Live session: most recent visit within the inactivity window
        // (for the same store when the route names one).
        $visit = Visit::where('visitor_id', $visitor->id)
            ->where('started_at', '>', now()->subMinutes((int) config('analytics.session_minutes', 30)))
            ->when($storeId, fn ($q) => $q->where('store_id', $storeId))
            ->latest('started_at')
            ->first();

        if ($visit && $tableId && ! $visit->table_id) {
            $visit->update(['table_id' => $tableId]);
        }

        // Only a menu page load (store known) may open a new visit. A tracking hit
        // arriving after the window closed is dropped instead of creating a store-less visit.
        if (! $visit && $storeId) {
            $visit = Visit::create([
                'visitor_id' => $visitor->id,
                'store_id' => $storeId,
                'table_id' => $tableId,
                'referer_host' => $this->externalRefererHost($request),
                'utm_source' => $request->query('utm_source'),
                'started_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $request->merge([
            'tracking_visitor_id' => $visitor->id,
            'tracking_visit_id' => $visit?->id,
            'tracking_uuid' => $uuid,
        ]);

        $response = $next($request);

        if ($request->cookie(self::COOKIE) !== $uuid) {
            $response->headers->setCookie(cookie()->forever(self::COOKIE, $uuid));
        }

        return $response;
    }

    protected function isUntrackedPath(Request $request): bool
    {
        return $request->is('_debugbar*', 'telescope*', 'horizon*', 'admin*', 'storage/*')
            || preg_match('/\.(ico|png|jpg|jpeg|gif|svg|webp|css|js|woff|woff2|ttf|map|txt|xml|php|env)$/i', $request->path());
    }

    protected function isBot(Request $request): bool
    {
        $userAgent = trim((string) $request->userAgent());

        if ($userAgent === '') {
            return true;
        }

        foreach ((array) config('analytics.bot_user_agents', []) as $fragment) {
            if (stripos($userAgent, $fragment) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function resolveStoreId(string $slug): ?int
    {
        $id = Cache::remember(
            'analytics:store_id_by_slug:'.$slug,
            now()->addMinutes(5),
            fn () => Store::where('slug', $slug)->value('id') ?? 0,
        );

        return $id ?: null;
    }

    protected function externalRefererHost(Request $request): ?string
    {
        $referer = $request->headers->get('referer');
        $host = $referer ? parse_url($referer, PHP_URL_HOST) : null;

        // Same-site referer (reload / tab switch) is not a source.
        return ($host && $host !== $request->getHost()) ? $host : null;
    }
}
