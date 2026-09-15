<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsStats;
use App\Filament\Widgets\TopInteractionsTable;
use App\Filament\Widgets\TopLikesTable;
use App\Filament\Widgets\TopTablesTable;
use App\Filament\Widgets\VisitorSourcesTable;
use App\Filament\Widgets\VisitsTrendChart;
use App\Models\Store;
use App\Support\AnalyticsRange;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AnalyticsDashboard extends Dashboard
{
    use HasFiltersForm;

    protected static string $routePath = '/analytics';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $title = 'Menü Analitiği';

    protected static ?string $navigationLabel = 'Analitik';

    protected static ?int $navigationSort = 1;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('range')
                ->label('Aralık')
                ->options(AnalyticsRange::PRESETS)
                ->default('24h')
                ->selectablePlaceholder(false),
            DatePicker::make('from')
                ->label('Başlangıç')
                ->native(false)
                ->maxDate(now())
                ->visible(fn (Get $get): bool => $get('range') === 'custom'),
            DatePicker::make('to')
                ->label('Bitiş')
                ->native(false)
                ->maxDate(now())
                ->visible(fn (Get $get): bool => $get('range') === 'custom'),
            Select::make('store_id')
                ->label('Şube')
                ->placeholder('Tüm şubeler')
                ->options(fn () => Store::orderBy('name')->pluck('name', 'id')),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            AnalyticsStats::class,
            VisitsTrendChart::class,
            TopInteractionsTable::class,
            TopLikesTable::class,
            TopTablesTable::class,
            VisitorSourcesTable::class,
        ];
    }
}
