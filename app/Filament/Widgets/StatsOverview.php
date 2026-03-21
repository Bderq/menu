<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Stores', \App\Models\Store::count())
                ->description('Active locations')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary')
                ->chart([7, 3, 10, 5, 10, 15, 20]),

            Stat::make('Total Products', \App\Models\Product::count())
                ->description('Master catalog size')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success')
                ->chart([15, 4, 10, 2, 12, 4, 12]),

            Stat::make('Categories', \App\Models\Category::count())
                ->description('Menu structure')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
        ];
    }
}
