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
                    ->withCount(['votes' => function ($query) {
                        $query->where('votes.created_at', '>', now()->subDay());
                    }])
                    ->whereHas('votes', function ($query) {
                        $query->where('votes.created_at', '>', now()->subDay());
                    })
                    ->orderByDesc('votes_count')
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
                Tables\Columns\TextColumn::make('votes_count')
                    ->label('24s Beğeni')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
            ]);
    }
}
