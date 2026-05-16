<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Vote;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopLikesTable extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'En Çok Beğenilen Ürünler';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*')
                    ->with('stores')
                    ->withCount([
                        'votes as votes_24h_count' => function ($query) {
                            $query->where('votes.created_at', '>', now()->subDay());
                        },
                        'votes as votes_total_count',
                    ])
                    ->whereHas('votes')
                    ->orderByDesc('votes_total_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
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
                Tables\Columns\TextColumn::make('votes_24h_count')
                    ->label('24s Beğeni')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ]);
    }
}
