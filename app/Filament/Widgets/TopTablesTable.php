<?php

namespace App\Filament\Widgets;

use App\Models\StoreTable;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopTablesTable extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'En Çok Taranan Masalar';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StoreTable::query()
                    ->with('store')
                    ->withCount([
                        'visits as visits_7d_count' => function ($query) {
                            $query->where('visits.started_at', '>', now()->subDays(7));
                        },
                        'visits as visits_total_count',
                    ])
                    ->having('visits_total_count', '>', 0)
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
                Tables\Columns\TextColumn::make('visits_7d_count')
                    ->label('Son 7 Gün')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ]);
    }
}
