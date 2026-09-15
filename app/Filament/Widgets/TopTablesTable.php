<?php

namespace App\Filament\Widgets;

use App\Models\StoreTable;
use App\Support\AnalyticsRange;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class TopTablesTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $range = AnalyticsRange::fromFilters($this->pageFilters);

        return $table
            ->heading('En Çok Taranan Masalar · '.$range->label())
            ->query(
                StoreTable::query()
                    ->with('store')
                    ->withCount([
                        'visits as visits_range_count' => fn ($query) => $query->whereBetween('visits.started_at', [$range->from, $range->to]),
                        'visits as visits_total_count',
                    ])
                    ->when($range->storeId, fn ($q) => $q->where('store_id', $range->storeId))
                    ->having('visits_total_count', '>', 0)
                    ->orderByDesc('visits_range_count')
                    ->orderByDesc('visits_total_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Şube'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Masa'),
                Tables\Columns\TextColumn::make('visits_total_count')
                    ->label('Toplam Ziyaret')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('visits_range_count')
                    ->label('Aralıkta')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ]);
    }
}
