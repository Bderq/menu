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

    public function getTitle(): string
    {
        return "{$this->store->name} - Ürün Yönetimi";
    }

    public function table(Table $table): Table
    {
        $currentStore = $this->store;

        return $table
            ->query(fn () => \App\Models\Product::query()) // Start with all products
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->square()
                    ->width(40),
                
                TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->description(fn ($record) => $record->category?->name)
                    ->searchable(),

                TextColumn::make('portions_summary')
                    ->label('Fiyat / Porsiyon')
                    ->getStateUsing(function ($record) use ($currentStore) {
                        $portions = $record->portions()->where('store_id', $currentStore->id)->orderBy('sort_order')->get();
                        if ($portions->isEmpty()) return 'Fiyat Girilmemiş';
                        
                        $count = $portions->count();
                        $firstPrice = number_format($portions->first()->price, 2) . ' ₺';
                        
                        return $count > 1 ? "{$firstPrice} (+{$count})" : $firstPrice;
                    })
                    ->badge()
                    ->color('success'),

                ToggleColumn::make('store_active')
                    ->label('Aktif')
                    ->onColor('success')
                    ->offColor('danger')
                    ->getStateUsing(function ($record) use ($currentStore) {
                        return $record->stores->where('id', $currentStore->id)->first()?->pivot?->is_active ?? false;
                    })
                    ->updateStateUsing(function ($record, $state) use ($currentStore) {
                        $record->stores()->syncWithoutDetaching([$currentStore->id => ['is_active' => $state]]);
                    })
                    ->alignCenter(),

                ToggleColumn::make('is_featured')
                    ->label('Öne Çıkan')
                    ->onIcon('heroicon-s-star')
                    ->offIcon('heroicon-o-star')
                    ->onColor('warning')
                    ->getStateUsing(function ($record) use ($currentStore) {
                        return $record->stores->where('id', $currentStore->id)->first()?->pivot?->is_featured ?? false;
                    })
                    ->updateStateUsing(function ($record, $state) use ($currentStore) {
                        $record->stores()->syncWithoutDetaching([$currentStore->id => ['is_featured' => $state]]);
                    })
                    ->alignCenter(),

                TextInputColumn::make('sort_order')
                    ->label('Sıra')
                    ->type('number')
                    ->getStateUsing(function ($record) use ($currentStore) {
                        return $record->stores->where('id', $currentStore->id)->first()?->pivot?->sort_order ?? 0;
                    })
                    ->updateStateUsing(function ($record, $state) use ($currentStore) {
                        $record->stores()->syncWithoutDetaching([$currentStore->id => ['sort_order' => $state]]);
                    })
                    ->alignCenter()
                    ->width(80),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
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
        // Reuse category tabs if possible, but localized for the store
        $categories = \App\Models\Category::all();
        $tabs = ['all' => \Filament\Schemas\Components\Tabs\Tab::make('Tüm Menü')];

        foreach ($categories as $category) {
            $tabs[$category->slug] = \Filament\Schemas\Components\Tabs\Tab::make($category->name)
                ->query(fn ($query) => $query->where('category_id', $category->id));
        }

        return $tabs;
    }
}
