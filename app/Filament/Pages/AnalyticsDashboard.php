<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AnalyticsDashboard extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $title = 'Menü Analitiği';
    protected static ?string $navigationLabel = 'Analitik';
    protected static ?string $slug = 'analytics';

    protected string $view = 'filament.pages.analytics-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\AnalyticsStats::class,
            \App\Filament\Widgets\TopInteractionsTable::class,
        ];
    }
}
