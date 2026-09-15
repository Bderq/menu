<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use App\Support\AnalyticsRange;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $range = AnalyticsRange::fromFilters($this->pageFilters);
        $service = app(AnalyticsService::class);

        $now = $service->summary($range);
        $prev = $service->summary($range->previous());
        $label = $range->label();

        return [
            $this->stat('Tekil Ziyaretçi', $now['visitors'], $prev['visitors'], "{$label} · benzersiz cihaz", 'heroicon-m-user-group'),
            $this->stat('Toplam Oturum', $now['sessions'], $prev['sessions'], "{$label} · menü açılışı", 'heroicon-m-cursor-arrow-rays'),
            $this->stat('Etkileşimli Oturum', $now['engaged'], $prev['engaged'], "Oturumların %{$now['engaged_rate']}'i menüde gezindi", 'heroicon-m-hand-raised'),
            $this->stat('Ort. Etkileşim Süresi', $now['avg_dwell'], $prev['avg_dwell'], 'Etkileşimli oturum başına', 'heroicon-m-clock', ' s'),
        ];
    }

    protected function stat(string $title, float|int $value, float|int $previous, string $description, string $icon, string $suffix = ''): Stat
    {
        $delta = $this->delta($value, $previous);

        return Stat::make($title, $value.$suffix)
            ->description($delta === null ? $description : "{$description} · {$delta} önceki döneme göre")
            ->descriptionIcon($icon)
            ->color(match (true) {
                $delta === null => 'gray',
                str_starts_with($delta, '+') => 'success',
                str_starts_with($delta, '-') => 'danger',
                default => 'gray',
            });
    }

    protected function delta(float|int $value, float|int $previous): ?string
    {
        if ($previous <= 0) {
            return null;
        }

        $pct = round(($value - $previous) / $previous * 100);

        return ($pct >= 0 ? '+' : '').$pct.'%';
    }
}
