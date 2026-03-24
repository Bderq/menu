<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $last24h = now()->subDay();

        return [
            Stat::make('Tekil Ziyaretçi (24s)', \App\Models\Visitor::where('last_seen_at', '>', $last24h)->count())
                ->description('Son 24 saat içindeki benzersiz cihazlar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Toplam Oturum (24s)', \App\Models\Visit::where('started_at', '>', $last24h)->count())
                ->description('Menü açılış sayısı')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('info'),

            Stat::make('Ort. Etkileşim Süresi', round(\App\Models\Interaction::where('created_at', '>', $last24h)->where('type', 'heartbeat')->sum('duration_seconds') / max(1, \App\Models\Visit::where('started_at', '>', $last24h)->count()), 1) . ' s')
                ->description('Ziyaret başına ortalama vakit')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
