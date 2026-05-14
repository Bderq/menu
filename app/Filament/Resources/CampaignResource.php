<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use BackedEnum;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Kampanyalar';
    protected static ?string $modelLabel = 'Kampanya';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. General Info
                Section::make('Temel Bilgiler')
                    ->description('Kampanyanın görünen yüzünü oluşturun.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Kampanya Adı (Admin)')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('display_title')
                                    ->label('Görünen Başlık (Badge)')
                                    ->placeholder('Örn: HAPPY HOUR')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('priority')
                                    ->label('Öncelik (Priority)')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Yüksek öncelikli kampanyalar önce uygulanır.'),
                            ]),
                        Textarea::make('description')
                            ->label('Açıklama')
                            ->columnSpanFull(),
                        FileUpload::make('image_path')
                            ->label('Kampanya Görseli (Opsiyonel)')
                            ->disk('public')
                            ->image()
                            ->directory('campaigns')
                            ->maxSize(300)
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(800)
                            ->getUploadedFileNameForStorageUsing(function ($file, $get) {
                                $name = $get('name') ? Str::slug($get('name')) : 'campaign-' . time();
                                return $name . '.webp';
                            }),
                        Toggle::make('is_active')
                            ->label('Kampanya Aktif')
                            ->default(true),
                    ]),

                // 2. Logic Engine
                Section::make('Kampanya Motoru')
                    ->description('İndirim veya paket kuralını belirleyin.')
                    ->schema([
                        Select::make('type')
                            ->label('Kampanya Türü')
                            ->options([
                                \App\Enums\CampaignType::FIXED_PRICE->value => 'Sabit Fiyat (Happy Hour)',
                                \App\Enums\CampaignType::PERCENTAGE->value => 'Yüzde İndirim (%)',
                                \App\Enums\CampaignType::BUNDLE->value => 'Paket Menü (Bundle)',
                                \App\Enums\CampaignType::X_GET_Y->value => 'Promosyon (1 Alana 1 Bedava vb.)',
                                \App\Enums\CampaignType::COLLECTIVE->value => 'Kolektif (Çoklu Alım)',
                            ])
                            ->required()
                            ->live(), // Reactive to show/hide fields
                        
                        Grid::make(2)
                            ->schema([
                                // Percentage / Fixed / Bundle Values
                                TextInput::make('value')
                                    ->label(fn (Get $get) => match ($get('type')) {
                                        \App\Enums\CampaignType::PERCENTAGE->value => 'İndirim Oranı (%)',
                                        \App\Enums\CampaignType::FIXED_PRICE->value => 'Sabit Fiyat (TL)',
                                        \App\Enums\CampaignType::BUNDLE->value => 'Paket Fiyatı (TL)',
                                        default => 'Değer',
                                    })
                                    ->numeric()
                                    ->visible(fn (Get $get) => in_array($get('type'), [\App\Enums\CampaignType::PERCENTAGE->value, \App\Enums\CampaignType::FIXED_PRICE->value, \App\Enums\CampaignType::BUNDLE->value]))
                                    ->required(fn (Get $get) => in_array($get('type'), [\App\Enums\CampaignType::PERCENTAGE->value, \App\Enums\CampaignType::FIXED_PRICE->value, \App\Enums\CampaignType::BUNDLE->value])),

                                // X Get Y Specific Fields
                                TextInput::make('buy_qty')
                                    ->label('Alınması Gereken Adet (X)')
                                    ->numeric()
                                    ->visible(fn (Get $get) => $get('type') === \App\Enums\CampaignType::X_GET_Y->value)
                                    ->required(fn (Get $get) => $get('type') === \App\Enums\CampaignType::X_GET_Y->value),
                                
                                TextInput::make('get_qty')
                                    ->label('İndirimli/Hediye Adet (Y)')
                                    ->numeric()
                                    ->visible(fn (Get $get) => $get('type') === \App\Enums\CampaignType::X_GET_Y->value)
                                    ->required(fn (Get $get) => $get('type') === \App\Enums\CampaignType::X_GET_Y->value),
                            ]),

                        Repeater::make('tiers')
                            ->label('Fiyat Kademeleri (Örn: 4 Adet -> Birim Fiyat: 145 TL)')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('quantity')
                                        ->label('Alım Adedi (Paket)')
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->required(),
                                    TextInput::make('price')
                                        ->label('Birim Fiyat (TL/Adet)')
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->required(),
                                    \Filament\Forms\Components\Placeholder::make('total')
                                        ->label('Hesaplama Özeti')
                                        ->content(function (Get $get) {
                                            $qty = (float) ($get('quantity') ?: 0);
                                            $price = (float) ($get('price') ?: 0);
                                            
                                            $originalPrice = 0;
                                            $items = $get('../../items');
                                            if (is_array($items) && count($items) > 0) {
                                                $firstItem = reset($items);
                                                if (!empty($firstItem['product_id'])) {
                                                    $product = \App\Models\Product::find($firstItem['product_id']);
                                                    // If portion is selected, maybe check portion price.. 
                                                    // But for simplicity, we check base product price.
                                                    if ($product) {
                                                        $originalPrice = (float) $product->price;
                                                    }
                                                }
                                            }
                                            
                                            $total = $qty * $price;
                                            
                                            if ($originalPrice > 0 && $qty > 0) {
                                                $listTotal = $qty * $originalPrice;
                                                $totalDiscount = $listTotal - $total;
                                                $unitDiscount = $originalPrice - $price;
                                                
                                                return new \Illuminate\Support\HtmlString(
                                                    "<div class='flex flex-col gap-1 text-sm'>" .
                                                    "<div class='flex justify-between text-gray-500'><span>Referans Liste:</span> <span><del>₺" . number_format($listTotal, 2, ',', '.') . "</del> <span class='text-[10px]'>(₺" . number_format($originalPrice, 2, ',', '.') . "/Adet)</span></span></div>" .
                                                    "<div class='flex justify-between font-bold text-gray-900 dark:text-gray-100'><span>Paket Toplam Tutar:</span> <span>₺" . number_format($total, 2, ',', '.') . "</span></div>" .
                                                    "<div class='flex justify-between text-success-600 font-medium'><span>Paket Toplam İndirim:</span> <span>₺" . number_format($totalDiscount, 2, ',', '.') . "</span></div>" .
                                                    "<div class='flex justify-between text-success-600 font-medium text-xs'><span>Paket Birim İndirim:</span> <span>₺" . number_format($unitDiscount, 2, ',', '.') . "</span></div>" .
                                                    "</div>"
                                                );
                                            }

                                            return new \Illuminate\Support\HtmlString(
                                                "<div class='font-bold text-lg text-gray-900 dark:text-gray-100'>₺" . number_format($total, 2, ',', '.') . "</div>" .
                                                "<div class='text-[10px] text-gray-500 mt-1'>(Kapsam bölümünden ürün seçerseniz indirimler hesaplanır)</div>"
                                            );
                                        }),
                                ]),
                            ])
                            ->visible(fn (Get $get) => $get('type') === \App\Enums\CampaignType::COLLECTIVE->value)
                            ->defaultItems(1)
                            ->addActionLabel('Kademe Ekle'),
                    ]),

                // 3. Store & Scope
                Section::make('Kapsam')
                    ->description('Bu kampanya nerelerde geçerli?')
                    ->schema([
                        Select::make('stores')
                            ->label('Şubeler')
                            ->relationship('stores', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Ürün')
                                            ->options(Product::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->columnSpan(2)
                                            ->afterStateUpdated(fn ($state, Set $set) => $set('store_product_portion_id', null)),
                                        
                                        Select::make('store_product_portion_id')
                                            ->label('Porsiyon (Opsiyonel)')
                                            ->options(function (Get $get) {
                                                $productId = $get('product_id');
                                                if (!$productId) return [];
                                                
                                                return \App\Models\StoreProductPortion::where('product_id', $productId)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->searchable()
                                            ->live()
                                            ->placeholder('Tüm porsiyonlar'),

                                        TextInput::make('price_override')
                                            ->label('Özel Fiyat (Opsiyonel)')
                                            ->numeric()
                                            ->suffix('TL'),
                                        
                                        Toggle::make('is_optional')
                                            ->label('Seçmeli Ürün')
                                            ->visible(fn (Get $get) => $get('../../type') === \App\Enums\CampaignType::BUNDLE->value)
                                            ->inline(false)
                                            ->helperText('Paket içinde opsiyonel mi?'),
                                    ])
                            ])
                            ->label('Kampanya Ürünleri')
                            ->defaultItems(1),
                    ]),
                
                // 4. Scheduling
                Section::make('Zamanlama')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('start_date')
                                    ->label('Başlangıç Tarihi & Saati')
                                    ->displayFormat('d.m.Y H:i')
                                    ->native(false)
                                    ->placeholder('Tarih seçilmezse hemen başlar'),
                                
                                DateTimePicker::make('end_date')
                                    ->label('Bitiş Tarihi & Saati')
                                    ->displayFormat('d.m.Y H:i')
                                    ->native(false)
                                    ->placeholder('Tarih seçilmezse süresiz'),
                            ]),
                        
                        Repeater::make('schedules')
                            ->relationship()
                            ->schema([
                                Section::make()
                                    ->compact()
                                    ->schema([
                                        CheckboxList::make('days')
                                            ->label('Geçerli Günler')
                                            ->options([
                                                'monday' => 'Pzt',
                                                'tuesday' => 'Salı',
                                                'wednesday' => 'Çarş',
                                                'thursday' => 'Perş',
                                                'friday' => 'Cuma',
                                                'saturday' => 'Cmt',
                                                'sunday' => 'Paz',
                                            ])
                                            ->columns(7)
                                            ->required(),
                                        Grid::make(3)
                                            ->schema([
                                                TimePicker::make('start_time')
                                                    ->label('Başlangıç')
                                                    ->required(),
                                                TimePicker::make('end_time')
                                                    ->label('Bitiş')
                                                    ->helperText('Gece yarısını geçiyorsa otomatik algılanır.')
                                                    ->required(),
                                            ])
                                    ])
                            ])
                            ->label('Aktif Saatler (Zorunlu Değil)')
                            ->defaultItems(0)
                            ->collapsed(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Kampanya')
                    ->searchable()
                    ->description(fn (Campaign $record): string => $record->display_title),
                
                TextColumn::make('priority')
                    ->label('Öncelik')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('Tür')
                    ->badge()
                    ->formatStateUsing(fn (\App\Enums\CampaignType $state): string => match ($state) {
                        \App\Enums\CampaignType::FIXED_PRICE => 'Sabit Fiyat',
                        \App\Enums\CampaignType::PERCENTAGE => '% İndirim',
                        \App\Enums\CampaignType::BUNDLE => 'Paket',
                        \App\Enums\CampaignType::X_GET_Y => 'Fırsat (X-Y)',
                        \App\Enums\CampaignType::COLLECTIVE => 'Kolektif',
                        default => $state->value,
                    })
                    ->color(fn (\App\Enums\CampaignType $state): string => match ($state) {
                        \App\Enums\CampaignType::FIXED_PRICE => 'warning',
                        \App\Enums\CampaignType::PERCENTAGE => 'info',
                        \App\Enums\CampaignType::BUNDLE => 'success',
                        \App\Enums\CampaignType::X_GET_Y => 'danger',
                        \App\Enums\CampaignType::COLLECTIVE => 'primary',
                        default => 'gray',
                    }),
                
                TextColumn::make('validity_status')
                    ->label('Durum')
                    ->badge()
                    ->state(function (Campaign $record): string {
                        $now = Carbon::now();
                        
                        if (!$record->is_active) {
                            return 'Pasif';
                        }
                        
                        if ($record->start_date && $now->lt($record->start_date)) {
                            return 'Gelecek';
                        }
                        
                        if ($record->end_date && $now->gt($record->end_date)) {
                            return 'Süresi Doldu';
                        }
                        
                        return 'Aktif';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Gelecek' => 'info',
                        'Süresi Doldu' => 'danger',
                        'Pasif' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('start_date')
                    ->label('Tarih Aralığı')
                    ->date('d.m.Y')
                    ->description(fn (Campaign $record) => $record->end_date ? 'Bitiş: ' . $record->end_date->format('d.m.Y') : 'Süresiz'),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
