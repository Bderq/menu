<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\Action;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ürün Detayları')
                    ->description('Master ürün kütüphanesi için temel bilgileri girin.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Ürün Adı')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('category_id')
                                    ->label('Kategori (Alt Kategori Seçiniz)')
                                    ->relationship('category', 'name', modifyQueryUsing: function ($query) {
                                        return $query->whereHas('parent', fn ($q) => $q->whereNotNull('parent_id'));
                                    })
                                    ->required()
                                    ->preload()
                                    ->searchable(),
                            ]),
                        Textarea::make('description')
                            ->label('Açıklama')
                            ->rows(3)
                            ->columnSpanFull(),
                        TagsInput::make('tags')
                            ->label('Ürün Özellikleri (Hashtag)')
                            ->placeholder('Örn: vegan, glutensiz, lager, sert')
                            ->helperText('Virgül veya Enter ile ayırarak girin. Bu etiketler # karakteri ile gösterilir.'),
                    ]),

                // The original Grid containing 'Medya ve Fiyat' and 'Yayın Ayarları' is replaced.
                // The FileUpload for image_path is moved to a new section.
                Section::make('Medya')
                    ->description('Ürün görsellerini yönetin. Liste ve kart görünümü için farklı fotoğraflar seçebilirsiniz.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Thumbnail (Liste Fotoğrafı)')
                                    ->disk('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['1:1'])
                                    ->directory('products/thumbnails')
                                    ->maxSize(300)
                                    ->imageResizeMode('cover')
                                    ->imageResizeTargetWidth(800)
                                    ->getUploadedFileNameForStorageUsing(function ($file, $get) {
                                        $name = $get('name') ? Str::slug($get('name')) : 'product-' . time();
                                        return $name . '.webp';
                                    })
                                    ->helperText('Menü listesinde görünecek kare görsel.'),
                                
                                FileUpload::make('gallery')
                                    ->label('Ürün Galerisi (Kart Detay)')
                                    ->disk('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->multiple()
                                    ->reorderable()
                                    ->imageEditor()
                                    ->directory('products/gallery')
                                    ->maxSize(300)
                                    ->imageResizeMode('cover')
                                    ->imageResizeTargetWidth(800)
                                    ->getUploadedFileNameForStorageUsing(function ($file, $get, $component) {
                                        $name = $get('name') ? Str::slug($get('name')) : 'product-' . time();
                                        // For multiple files, we need to append a unique index or time
                                        return $name . '-gallery-' . time() . '-' . Str::random(4) . '.webp';
                                    })
                                    ->helperText('Ürün kartına tıklandığında görünecek detay görselleri.'),
                            ]),
                    ]),

                Grid::make(1)
                    ->schema([
                        Section::make('Dükkan Atamaları ve Fiyatlandırma')
                            ->description('Her şube için ayrı fiyat ve porsiyonları sekmelerden yönetin.')
                            ->schema([
                                Tabs::make('Şube Ayarları')
                                    ->tabs(
                                        \App\Models\Store::all()->map(function ($store) {
                                            return Tab::make($store->name)
                                                ->schema([
                                                    Toggle::make("store_{$store->id}_active")
                                                        ->label('Satışta')
                                                        ->live()
                                                        ->dehydrated(false)
                                                        ->afterStateHydrated(function ($component, $record) use ($store) {
                                                            $component->state($record?->stores->contains($store->id) ?? false);
                                                        }),
                                                    
                                                    Section::make('Şubeye Özel Porsiyonlar')
                                                        ->visible(fn ($get) => $get("store_{$store->id}_active"))
                                                        ->headerActions([
                                                            Action::make('copy_to_stores')
                                                                ->label('Diğer Şubelere Kopyala')
                                                                ->icon('heroicon-m-share')
                                                                ->color('success')
                                                                ->form([
                                                                    CheckboxList::make('target_store_ids')
                                                                        ->label('Kopyalanacak Şubeler')
                                                                        ->options(\App\Models\Store::where('id', '!=', $store->id)->pluck('name', 'id'))
                                                                        ->required()
                                                                        ->columns(2),
                                                                ])
                                                                ->action(function (array $data, $component, $get, $set) use ($store) {
                                                                    // Get current portions of THIS store from the form state
                                                                    $currentPortions = $get("store_{$store->id}_portions");
                                                                    
                                                                    if (empty($currentPortions)) {
                                                                        \Filament\Notifications\Notification::make()
                                                                            ->title('Kopyalanacak porsiyon bulunamadı.')
                                                                            ->warning()
                                                                            ->send();
                                                                        return;
                                                                    }

                                                                    foreach ($data['target_store_ids'] as $targetStoreId) {
                                                                        // Copy portions
                                                                        $set("store_{$targetStoreId}_portions", $currentPortions);
                                                                        // Also mark that store as active
                                                                        $set("store_{$targetStoreId}_active", true);
                                                                    }

                                                                    \Filament\Notifications\Notification::make()
                                                                        ->title('Porsiyonlar seçilen şubelere kopyalandı!')
                                                                        ->success()
                                                                        ->send();
                                                                })
                                                        ])
                                                        ->collapsible()
                                                        ->schema([
                                                            Repeater::make("store_{$store->id}_portions")
                                                                ->label('')
                                                                ->dehydrated(false)
                                                                ->schema([
                                                                    Grid::make(2)
                                                                        ->schema([
                                                                            TextInput::make('name')
                                                                                ->label('Porsiyon Adı')
                                                                                ->placeholder('Örn: Standart, 1.5 Porsiyon')
                                                                                ->required()
                                                                                ->default('Standart'),
                                                                            TextInput::make('price')
                                                                                ->label('Fiyat')
                                                                                ->numeric()
                                                                                ->prefix('₺')
                                                                                ->required(),
                                                                        ])
                                                                ])
                                                                ->itemLabel(fn (array $state): ?string => ($state['name'] ?? null) . ' - ' . ($state['price'] ?? '0') . ' ₺')
                                                                ->grid(1)
                                                                ->reorderable()
                                                                ->reorderableWithButtons()
                                                                ->createItemButtonLabel('Porsiyon Ekle')
                                                                ->defaultItems(1) // Ensures it starts with at least one 'Standart'
                                                                ->afterStateHydrated(function ($component, $record) use ($store) {
                                                                    $portions = \App\Models\StoreProductPortion::where('product_id', $record?->id)
                                                                        ->where('store_id', $store->id)
                                                                        ->orderBy('sort_order')
                                                                        ->get()
                                                                        ->map(fn($p) => ['name' => $p->name, 'price' => $p->price])
                                                                        ->toArray();
                                                                    
                                                                    if (empty($portions)) {
                                                                        $portions = [['name' => 'Standart', 'price' => null]];
                                                                    }
                                                                    
                                                                    $component->state($portions);
                                                                }),
                                                        ]),
                                                ]);
                                        })->toArray()
                                    )
                                    ->columnSpanFull()
                                    ->persistTabInQueryString()
                                    ->saveRelationshipsUsing(function ($record, $state) {
                                        $stores = \App\Models\Store::all();
                                        $syncData = [];

                                        foreach ($stores as $store) {
                                            $isActive = $state["store_{$store->id}_active"] ?? false;
                                            
                                            if ($isActive) {
                                                $portions = $state["store_{$store->id}_portions"] ?? [];
                                                
                                                $syncData[$store->id] = [
                                                    'is_active' => true,
                                                ];

                                                // Save Portions
                                                \App\Models\StoreProductPortion::where('product_id', $record->id)
                                                    ->where('store_id', $store->id)
                                                    ->delete();

                                                foreach (array_values($portions) as $index => $portionData) {
                                                    \App\Models\StoreProductPortion::create([
                                                        'product_id' => $record->id,
                                                        'store_id' => $store->id,
                                                        'name' => $portionData['name'],
                                                        'price' => $portionData['price'],
                                                        'sort_order' => $index,
                                                    ]);
                                                }
                                            }
                                        }

                                        $record->stores()->sync($syncData);
                                    }),
                            ]),
                    ]),


                Section::make('Diyet & Alerjen Bilgileri')
                    ->description('Ürünün diyet türlerini ve alerjen uyarılarını belirtin. Müşteriler bu bilgileri menüde görebilir.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('dietTypes')
                                    ->label('Diyet Türleri')
                                    ->multiple()
                                    ->relationship('dietTypes', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->placeholder('Vegan, Vejetaryen, Glutensiz...')
                                    ->helperText('Birden fazla seçebilirsiniz.'),
                                Select::make('allergens')
                                    ->label('Alerjenler')
                                    ->multiple()
                                    ->relationship('allergens', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->placeholder('Fıstık, Süt, Gluten...')
                                    ->helperText('İçerdiği alerjenleri seçin.'),
                            ]),
                    ]),

                Section::make('Yayın Ayarları')
                    ->schema([
                        Repeater::make('badges')
                            ->label('Satış Belirteçleri (Banner, Kampanya vb.)')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Belirteç Metni')
                                            ->placeholder('Örn: YENİ, %20 İNDİRİM, POPÜLER')
                                            ->required(),
                                        ColorPicker::make('bg_color')
                                            ->label('Zemin Rengi')
                                            ->default('#ffb000'),
                                        ColorPicker::make('text_color')
                                            ->label('Yazı Rengi')
                                            ->default('#000000'),
                                    ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Yeni Belirteç')
                            ->reorderable()
                            ->collapsible()
                            ->createItemButtonLabel('Yeni Etiket Ekle')
                            ->grid(1),
                        Toggle::make('is_active')
                            ->label('Genel Satış Durumu (Global)')
                            ->helperText('Bu kapalıysa ürün hiçbir şubede görünmez.')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
