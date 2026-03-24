<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Interaction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;

class TopInteractionsTable extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Interaction::query()
                    ->fromSub(function($query) {
                        $query->from('interactions')
                            ->select([
                                DB::raw('MIN(id) as id'),
                                'interactable_type', 
                                'interactable_id', 
                                'type', 
                                DB::raw('COUNT(*) as count'), 
                                DB::raw('SUM(duration_seconds) as total_duration')
                            ])
                            ->whereNotNull('interactable_id')
                            ->where('created_at', '>', now()->subDays(30))
                            ->groupBy(['interactable_type', 'interactable_id', 'type']);
                    }, 'interactions')
            )
            ->defaultSort('count', 'desc')
            ->columns([
                TextColumn::make('interactable.name')
                    ->label('Öğe Adı')
                    ->description(fn ($record) => $record->interactable_type === 'App\Models\Product' ? 'Ürün' : 'Kategori'),
                TextColumn::make('type')
                    ->label('Etkileşim')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'click' => 'success',
                        'view' => 'info',
                        'heartbeat' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('count')
                    ->label('Sayı')
                    ->sortable(),
                TextColumn::make('total_duration')
                    ->label('Dwell (sn)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->paginated([5, 10, 25])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
