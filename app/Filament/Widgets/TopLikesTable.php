<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Support\AnalyticsRange;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class TopLikesTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $range = AnalyticsRange::fromFilters($this->pageFilters);

        return $table
            ->heading('En Çok Beğenilen Ürünler · '.$range->label())
            ->query(
                Product::query()
                    ->select('products.*')
                    ->with('stores')
                    ->withCount([
                        'votes as votes_range_count' => fn ($query) => $query->whereBetween('votes.created_at', [$range->from, $range->to]),
                        'votes as votes_total_count',
                    ])
                    ->whereHas('votes')
                    ->when($range->storeId, fn ($q) => $q->whereHas('stores', fn ($s) => $s->where('stores.id', $range->storeId)))
                    ->orderByDesc('votes_range_count')
                    ->orderByDesc('votes_total_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Görsel')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stores.name')
                    ->label('Şube(ler)')
                    ->listWithLineBreaks()
                    ->limitList(2),
                Tables\Columns\TextColumn::make('votes_total_count')
                    ->label('Toplam Beğeni')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('votes_range_count')
                    ->label('Aralıkta Beğeni')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ]);
    }
}
