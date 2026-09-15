<?php

namespace App\Services;

use App\Models\Visit;
use App\Support\AnalyticsRange;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    protected const CACHE_SECONDS = 60;

    /**
     * Headline numbers for a range.
     *
     * @return array{sessions:int, visitors:int, engaged:int, engaged_rate:float, avg_dwell:float}
     */
    public function summary(AnalyticsRange $range): array
    {
        return Cache::remember('analytics:summary:'.$range->cacheKey(), self::CACHE_SECONDS, function () use ($range) {
            $visits = $this->visitsIn($range);

            $row = (clone $visits)
                ->selectRaw('count(*) as sessions, count(distinct visitor_id) as visitors')
                ->first();

            $engaged = (clone $visits)
                ->whereExists(fn ($q) => $q->selectRaw('1')->from('interactions')->whereColumn('interactions.visit_id', 'visits.id'))
                ->count();

            // Dwell = heartbeat seconds, averaged only over visits that produced heartbeats.
            $dwell = DB::table('interactions')
                ->join('visits', 'visits.id', '=', 'interactions.visit_id')
                ->where('interactions.type', 'heartbeat')
                ->whereBetween('visits.started_at', [$range->from, $range->to])
                ->when($range->storeId, fn ($q) => $q->where('visits.store_id', $range->storeId))
                ->selectRaw('coalesce(sum(interactions.duration_seconds), 0) as seconds, count(distinct interactions.visit_id) as visits')
                ->first();

            $sessions = (int) $row->sessions;

            return [
                'sessions' => $sessions,
                'visitors' => (int) $row->visitors,
                'engaged' => $engaged,
                'engaged_rate' => $sessions > 0 ? round($engaged / $sessions * 100, 1) : 0.0,
                'avg_dwell' => $dwell->visits > 0 ? round($dwell->seconds / $dwell->visits, 1) : 0.0,
            ];
        });
    }

    /**
     * Sessions and engaged sessions per hour/day bucket, gaps filled with zeros.
     *
     * @return array{labels: string[], sessions: int[], engaged: int[], hourly: bool}
     */
    public function trend(AnalyticsRange $range): array
    {
        return Cache::remember('analytics:trend:'.$range->cacheKey(), self::CACHE_SECONDS, function () use ($range) {
            $hourly = $range->isHourly();
            $unit = $hourly ? 'hour' : 'day';

            $rows = $this->visitsIn($range)
                ->selectRaw("date_trunc('{$unit}', started_at) as bucket, count(*) as sessions, count(*) filter (where exists (select 1 from interactions i where i.visit_id = visits.id)) as engaged")
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get()
                ->keyBy(fn ($r) => CarbonImmutable::parse($r->bucket)->format('Y-m-d H:i'));

            $start = $hourly ? $range->from->startOfHour() : $range->from->startOfDay();
            $period = CarbonPeriod::create($start, $hourly ? '1 hour' : '1 day', $range->to);

            $labels = $sessions = $engaged = [];
            foreach ($period as $bucket) {
                $bucket = CarbonImmutable::instance($bucket);
                $key = $bucket->format('Y-m-d H:i');
                $labels[] = $hourly ? $bucket->format('d.m H:00') : $bucket->format('d.m');
                $sessions[] = (int) ($rows[$key]->sessions ?? 0);
                $engaged[] = (int) ($rows[$key]->engaged ?? 0);
            }

            return compact('labels', 'sessions', 'engaged', 'hourly');
        });
    }

    protected function visitsIn(AnalyticsRange $range)
    {
        return Visit::query()
            ->whereBetween('started_at', [$range->from, $range->to])
            ->when($range->storeId, fn ($q) => $q->where('store_id', $range->storeId));
    }
}
