<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cookieName = 'qr_menu_visitor_id';

        // Skip non-page requests
        if ($request->is('_debugbar*', 'telescope*', 'horizon*', 'admin*', 'storage/*') || 
            preg_match('/\.(ico|png|jpg|jpeg|gif|svg|css|js|woff|woff2|ttf|map)$/i', $request->path())) {
            return $next($request);
        }

        // Detect Bots/Crawlers
        $userAgent = $request->userAgent();
        $bots = [
            'Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider', 'YandexBot', 'facebot', 'facebookexternalhit',
            'ia_archiver', 'WhatsApp', 'TelegramBot', 'Twitterbot', 'LinkedInBot', 'Pinterestbot', 'Slackbot', 'Discordbot',
            'Google-Structured-Data-Testing-Tool', 'CriteoBot', 'Applebot', 'HeadlessChrome', 'UptimeRobot'
        ];
        
        foreach ($bots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return $next($request);
            }
        }

        $uuid = $request->cookie($cookieName);
        $visitor = null;
        $storeId = null;

        if ($request->route('store_slug')) {
            $storeId = \App\Models\Store::where('slug', $request->route('store_slug'))->value('id');
        }

        if ($uuid) {
            $visitor = \App\Models\Visitor::where('uuid', $uuid)->first();
        }

        if (!$visitor) {
            $uuid = (string) \Illuminate\Support\Str::uuid();
            $visitor = \App\Models\Visitor::create([
                'uuid' => $uuid,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_seen_at' => now(),
            ]);
        } else {
            $visitor->update(['last_seen_at' => now()]);
        }

        // Manage Visit (Session)
        $visit = null;
        $visitQuery = \App\Models\Visit::where('visitor_id', $visitor->id)
            ->where('started_at', '>', now()->subMinutes(30));

        // If explicitly provided via route or we can infer it from the visitor's last active visit
        if ($storeId) {
            $visitQuery->where('store_id', $storeId);
        } else {
            // If it's a tracking hit without a store slug, try to attach it to the most recent visit of this visitor
            $lastRecentVisit = \App\Models\Visit::where('visitor_id', $visitor->id)
                ->where('started_at', '>', now()->subMinutes(30))
                ->latest()
                ->first();
            
            if ($lastRecentVisit) {
                $visit = $lastRecentVisit;
            }
        }

        if (!$visit) {
            $visit = $visitQuery->latest('started_at')->first();
        }

        if (!$visit) {
            $referer = $request->headers->get('referer');
            $refererHost = $referer ? parse_url($referer, PHP_URL_HOST) : null;
            
            // Kendi sitemizden geliyorsa (sayfa yenileme / sekme değiştirme) Referer saymayız.
            if ($refererHost === $request->getHost()) {
                $refererHost = null;
            }
            
            $utmSource = $request->query('utm_source');

            $visit = \App\Models\Visit::create([
                'visitor_id' => $visitor->id,
                'store_id' => $storeId,
                'referer_host' => $refererHost,
                'utm_source' => $utmSource,
                'started_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Store IDs in request for controller/logging access
        $request->merge([
            'tracking_visitor_id' => $visitor->id,
            'tracking_visit_id' => $visit->id,
            'tracking_uuid' => $uuid,
        ]);

        $response = $next($request);

        // Ensure cookie is set/extended (1 year)
        if ($request->cookie($cookieName) !== $uuid) {
            $response->headers->setCookie(cookie()->forever($cookieName, $uuid));
        }

        return $response;
    }
}
