<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class VisitorSourcesTable extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Ziyaretçi Kaynakları (Son 24 Saat)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Visit::query()
                    ->fromSub(function($query) {
                        $query->from('visits')
                            ->select('referer_host', 'utm_source', DB::raw('count(*) as count'), DB::raw('MIN(id) as id'))
                            ->where('started_at', '>', now()->subDay())
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
