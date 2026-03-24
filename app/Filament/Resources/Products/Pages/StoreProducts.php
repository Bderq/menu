<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Store;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class StoreProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public ?Store $store = null;

    public function mount(): void
    {
        parent::mount();
        $storeParam = request()->route('store');
        $this->store = $storeParam instanceof Store ? $storeParam : Store::findOrFail($storeParam);
    }

    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        $currentStore = $this->store;
        
        foreach ($order as $index => $recordId) {
            \Illuminate\Support\Facades\DB::table('store_products')
                ->where('store_id', $currentStore->id)
                ->where('product_id', $recordId)
                ->update(['sort_order' => $index]);
        }
    }

    public function getTitle(): string
    {
        return "{$this->store->name} - Ürün Yönetimi";
    }

    public function table(Table $table): Table
    {
        $currentStore = $this->store;

        $table = $table
            ->query(fn () => \App\Models\Product::query()
                ->select('products.*', 'store_products.sort_order as pivot_sort_order')
                ->leftJoin('store_products', function ($join) use ($currentStore) {
                    $join->on('products.id', '=', 'store_products.product_id')
                         ->where('store_products.store_id', '=', $currentStore->id);
                })
                ->with([
                    'stores' => fn($q) => $q->where('stores.id', $this->store->id),
                    'portions' => fn($q) => $q->where('store_id', $this->store->id),
                    'category.parent'
                ])
            )
            ->defaultSort('pivot_sort_order')
            ->modifyQueryUsing(function (Builder $query) {
                // Additional global modify if needed
            });

        // Sadece kategori filtreli iken (Tab veya Filtre) Reorder işlemini aktif et
        if ($this->activeTab !== 'all' || !empty($this->getTableFilterState('category')['value'])) {
            $table->reorderable('pivot_sort_order');
        }

        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->width(32)
                    ->toggleable(),
                
                TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->description(fn ($record) => $record->category?->parent?->name ? "{$record->category->parent->name} >" : null)
                    ->toggleable()
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('store_active')
                    ->label('Aktif')
                    ->onColor('success')
                    ->offColor('danger')
                    ->getStateUsing(function ($record) {
                        return $record->stores->first()?->pivot?->is_active ?? false;
                    })
                    ->updateStateUsing(function ($record, $state) use ($currentStore) {
                        $record->stores()->syncWithoutDetaching([$currentStore->id => ['is_active' => $state]]);
                    })
                    ->alignCenter()
                    ->width(80),

                ToggleColumn::make('is_featured')
                    ->label('Star')
                    ->onIcon('heroicon-s-star')
                    ->offIcon('heroicon-o-star')
                    ->onColor('warning')
                    ->getStateUsing(function ($record) {
                        return $record->stores->first()?->pivot?->is_featured ?? false;
                    })
                    ->updateStateUsing(function ($record, $state) use ($currentStore) {
                        $record->stores()->syncWithoutDetaching([$currentStore->id => ['is_featured' => $state]]);
                    })
                    ->alignCenter()
                    ->width(80),

                TextColumn::make('portions_summary')
                    ->label('Fiyatlar')
                    ->getStateUsing(function ($record) {
                        $portions = $record->portions->sortBy('sort_order');
                        if ($portions->isEmpty()) return '-';
                        
                        $count = $portions->count();
                        $firstPrice = number_format($portions->first()->price, 2) . ' ₺';
                        
                        return $count > 1 ? "{$firstPrice} (+{$count})" : $firstPrice;
                    })
                    ->color('success')
                    ->fontFamily('mono')
                    ->alignRight()
                    ->width(120),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(fn() => \App\Models\Category::all()->pluck('hierarchical_name', 'id'))
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        $category = \App\Models\Category::with('children')->find($data['value']);
                        if (! $category) return $query;

                        $ids = [$category->id];
                        if ($category->children->isNotEmpty()) {
                            $ids = array_merge($ids, $category->children->pluck('id')->toArray());
                        }

                        return $query->whereIn('category_id', $ids);
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->form([
                        Section::make('Şube Özel Porsiyonlar')
                            ->description('Bu dükkan için özel porsiyon ve fiyatlandırma tanımlayın.')
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('portions')
                                    ->relationship('portions', fn($query) => $query->where('store_id', $this->store->id)->orderBy('sort_order'))
                                    ->label('')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('name')
                                            ->label('Porsiyon Adı')
                                            ->required()
                                            ->default('Standart')
                                            ->placeholder('Örn: 150gr, Double'),
                                        \Filament\Forms\Components\TextInput::make('price')
                                            ->label('Fiyat')
                                            ->numeric()
                                            ->prefix('₺')
                                            ->required(),
                                    ])
                                    ->grid(1)
                                    ->reorderable()
                                    ->createItemButtonLabel('Porsiyon Ekle')
                                    ->saveRelationshipsUsing(function ($record, $state) {
                                        $record->portions()->where('store_id', $this->store->id)->delete();
                                        foreach (array_values($state) as $index => $portionData) {
                                            $record->portions()->create([
                                                'store_id' => $this->store->id,
                                                'name' => $portionData['name'],
                                                'price' => $portionData['price'],
                                                'sort_order' => $index,
                                            ]);
                                        }
                                    }),
                            ]),
                    ])
                    ->iconButton(),
            ]);
    }

    public function getTabs(): array
    {
        $categories = \App\Models\Category::whereNull('parent_id')->get();
        $tabs = ['all' => \Filament\Schemas\Components\Tabs\Tab::make('Tüm Menü')];

        foreach ($categories as $category) {
            $tabs[$category->slug] = \Filament\Schemas\Components\Tabs\Tab::make($category->name)
                ->query(function ($query) use ($category) {
                    $categoryIds = [$category->id];
                    $categoryIds = array_merge($categoryIds, $category->children->pluck('id')->toArray());
                    return $query->whereIn('category_id', $categoryIds);
                });
        }

        return $tabs;
    }
}
