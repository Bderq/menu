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

        $uuid = $request->cookie($cookieName);
        $visitor = null;

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
        $visit = \App\Models\Visit::where('visitor_id', $visitor->id)
            ->where('started_at', '>', now()->subMinutes(30))
            ->latest('started_at')
            ->first();

        if (!$visit) {
            $visit = \App\Models\Visit::create([
                'visitor_id' => $visitor->id,
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
