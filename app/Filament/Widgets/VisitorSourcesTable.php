<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use App\Support\AnalyticsRange;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class VisitorSourcesTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $range = AnalyticsRange::fromFilters($this->pageFilters);

        return $table
            ->heading('Ziyaretçi Kaynakları · '.$range->label())
            ->query(
                Visit::query()
                    ->fromSub(function ($query) use ($range) {
                        $query->from('visits')
                            ->select('referer_host', 'utm_source', DB::raw('count(*) as count'), DB::raw('MIN(id) as id'))
                            ->whereBetween('started_at', [$range->from, $range->to])
                            ->when($range->storeId, fn ($q) => $q->where('store_id', $range->storeId))
                            ->groupBy('referer_host', 'utm_source');
                    }, 'visits')
            )
            ->defaultSort('count', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('referer_host')
                    ->label('Kaynak (Referer)')
                    ->placeholder('Doğrudan / QR')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('utm_source')
                    ->label('UTM Kaynağı')
                    ->placeholder('-')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('count')
                    ->label('Ziyaret Sayısı')
                    ->sortable()
                    ->alignEnd(),
            ]);
    }
}
