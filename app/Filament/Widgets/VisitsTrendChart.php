<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use App\Support\AnalyticsRange;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class VisitsTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public function getHeading(): string
    {
        $range = AnalyticsRange::fromFilters($this->pageFilters);

        return 'Oturum Trendi · '.$range->label().($range->isHourly() ? ' (saatlik)' : ' (günlük)');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $trend = app(AnalyticsService::class)->trend(AnalyticsRange::fromFilters($this->pageFilters));

        return [
            'labels' => $trend['labels'],
            'datasets' => [
                [
                    'label' => 'Oturum',
                    'data' => $trend['sessions'],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Etkileşimli Oturum',
                    'data' => $trend['engaged'],
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            'plugins' => ['legend' => ['display' => true]],
        ];
    }
}
