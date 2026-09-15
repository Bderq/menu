<?php

namespace App\Filament\Widgets;

use App\Models\Interaction;
use App\Support\AnalyticsRange;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class TopInteractionsTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $range = AnalyticsRange::fromFilters($this->pageFilters);

        return $table
            ->heading('En Çok Etkileşim · '.$range->label())
            ->query(
                Interaction::query()
                    ->with(['interactable'])
                    ->fromSub(function ($query) use ($range) {
                        $query->from('interactions')
                            ->join('visits', 'interactions.visit_id', '=', 'visits.id')
                            ->leftJoin('stores', 'visits.store_id', '=', 'stores.id')
                            ->select([
                                DB::raw('MIN(interactions.id) as id'),
                                'interactions.interactable_type',
                                'interactions.interactable_id',
                                'stores.name as store_name',
                                'interactions.type',
                                DB::raw('COUNT(*) as count'),
                                DB::raw('SUM(interactions.duration_seconds) as total_duration'),
                            ])
                            ->whereNotNull('interactions.interactable_id')
                            ->whereBetween('interactions.created_at', [$range->from, $range->to])
                            ->when($range->storeId, fn ($q) => $q->where('visits.store_id', $range->storeId))
                            ->groupBy(['interactions.interactable_type', 'interactions.interactable_id', 'stores.name', 'interactions.type']);
                    }, 'interactions')
            )
            ->defaultSort('count', 'desc')
            ->columns([
                TextColumn::make('interactable.name')
                    ->label('Öğe Adı')
                    ->description(fn ($record) => match ($record->interactable_type) {
                        'App\Models\Product' => 'Ürün',
                        'App\Models\Category' => 'Kategori',
                        'App\Models\Campaign' => 'Kampanya',
                        default => 'Bilinmeyen',
                    }),
                TextColumn::make('store_name')
                    ->label('Şube')
                    ->placeholder('—')
                    ->badge()
                    ->color('primary'),
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
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('Etkileşim Türü')
                    ->options([
                        'click' => 'Tıklama',
                        'view' => 'Görüntüleme',
                        'heartbeat' => 'Vakit Geçirme',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('interactable_type')
                    ->label('Öğe Türü')
                    ->options([
                        'App\Models\Product' => 'Ürün',
                        'App\Models\Category' => 'Kategori',
                        'App\Models\Campaign' => 'Kampanya',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('store_name')
                    ->label('Şube')
                    ->options(fn () => \App\Models\Store::pluck('name', 'name')->toArray()),
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
