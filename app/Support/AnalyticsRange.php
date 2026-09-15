<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Resolved analytics dashboard filters: a closed [from, to] window plus an optional store.
 */
final class AnalyticsRange
{
    public const PRESETS = [
        '24h' => 'Son 24 Saat',
        '7d' => 'Son 7 Gün',
        '30d' => 'Son 30 Gün',
        'custom' => 'Özel Aralık',
    ];

    public function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly ?int $storeId,
        public readonly string $preset,
    ) {}

    /**
     * @param  array<string, mixed>|null  $filters  Filament page filters (range, from, to, store_id)
     */
    public static function fromFilters(?array $filters): self
    {
        $preset = $filters['range'] ?? '24h';
        $storeId = filled($filters['store_id'] ?? null) ? (int) $filters['store_id'] : null;
        $now = CarbonImmutable::now();

        if ($preset === 'custom' && filled($filters['from'] ?? null)) {
            $from = CarbonImmutable::parse($filters['from'])->startOfDay();
            $to = filled($filters['to'] ?? null) ? CarbonImmutable::parse($filters['to'])->endOfDay() : $now;

            if ($to->lessThan($from)) {
                [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
            }

            return new self($from, $to, $storeId, 'custom');
        }

        $from = match ($preset) {
            '7d' => $now->subDays(7),
            '30d' => $now->subDays(30),
            default => $now->subDay(),
        };

        return new self($from, $now, $storeId, array_key_exists($preset, self::PRESETS) ? $preset : '24h');
    }

    /** Same length window immediately before this one, for comparisons. */
    public function previous(): self
    {
        $length = $this->from->diffInSeconds($this->to);

        return new self($this->from->subSeconds($length), $this->from, $this->storeId, $this->preset);
    }

    public function label(): string
    {
        return $this->preset === 'custom'
            ? $this->from->format('d.m.Y').' – '.$this->to->format('d.m.Y')
            : self::PRESETS[$this->preset];
    }

    /** Buckets shorter than or equal to 48 hours are drawn hourly. */
    public function isHourly(): bool
    {
        return $this->from->diffInHours($this->to) <= 48;
    }

    public function applyTo(EloquentBuilder|QueryBuilder $query, string $column): EloquentBuilder|QueryBuilder
    {
        return $query->whereBetween($column, [$this->from, $this->to]);
    }

    public function cacheKey(): string
    {
        return implode(':', [
            $this->preset,
            $this->from->format('YmdHi'),
            $this->to->format('YmdHi'),
            $this->storeId ?? 'all',
        ]);
    }
}
