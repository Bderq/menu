<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaHubResource\Pages\ManageMediaHubs;
use App\Models\Product;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;

class MediaHubResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationLabel = 'Medya Operasyon';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-camera';

    protected static ?string $slug = 'media-hub';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): string => $record->category?->name ?? 'Kategorisiz'),
                
                ImageColumn::make('image_path')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->square()
                    ->size(75)
                    ->placeholder('📸 YÜKLE')
                    ->extraImgAttributes([
                        'class' => 'border-2 border-dashed border-gray-600 rounded-lg hover:border-primary-500 transition-colors cursor-pointer',
                        'style' => 'background: rgba(255,255,255,0.05)'
                    ])
                    ->action(Action::make('update_thumbnail_column')
                        ->modalHeading(fn (Product $record) => $record->name . ' - Thumbnail Yükle')
                        ->modalWidth('xl')
                        ->modalSubmitActionLabel('Tamamla')
                        ->form([
                            FileUpload::make('image_path')
                                ->label('Görseli buraya sürükleyip bırakın')
                                ->image()
                                ->directory('products/thumbnails')
                                ->maxSize(1024)
                                ->imageResizeMode('cover')
                                ->imageResizeTargetWidth(800)
                                ->getUploadedFileNameForStorageUsing(fn ($file, $record) => Str::slug($record->name) . '.webp')
                                ->required(),
                        ])
                        ->action(fn (Product $record, array $data) => $record->update(['image_path' => $data['image_path']]))
                    ),

                ImageColumn::make('gallery')
                    ->label('Kart Görseli')
                    ->disk('public')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->size(75)
                    ->placeholder('🖼️ YÜKLE')
                    ->extraImgAttributes([
                        'class' => 'border-2 border-dashed border-gray-600 rounded-lg hover:border-primary-500 transition-colors cursor-pointer',
                        'style' => 'background: rgba(255,255,255,0.05)'
                    ])
                    ->action(Action::make('update_gallery_column')
                        ->modalHeading(fn (Product $record) => $record->name . ' - Kart Görselleri Yükle')
                        ->modalWidth('2xl')
                        ->modalSubmitActionLabel('Galeriye Ekle')
                        ->form([
                            FileUpload::make('gallery')
                                ->label('Görselleri (tek veya çoklu) buraya sürükleyin')
                                ->image()
                                ->multiple()
                                ->reorderable()
                                ->directory('products/gallery')
                                ->maxSize(1024)
                                ->imageResizeMode('cover')
                                ->imageResizeTargetWidth(800)
                                ->getUploadedFileNameForStorageUsing(fn ($file, $record) => Str::slug($record->name) . '-gallery-' . time() . '-' . Str::random(4) . '.webp')
                                ->required(),
                        ])
                        ->action(fn (Product $record, array $data) => $record->update(['gallery' => $data['gallery']]))
                    ),

                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->getStateUsing(function (Product $record): string {
                        $hasThumbnail = !empty($record->image_path);
                        $hasGallery = !empty($record->gallery) && count((array)$record->gallery) > 0;

                        if (!$hasThumbnail && !$hasGallery) return 'missing';
                        if (!$hasThumbnail || !$hasGallery) return 'partial';
                        return 'ready';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'missing' => 'danger',
                        'partial' => 'warning',
                        'ready' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'missing' => '🚨 Eksik',
                        'partial' => '⚠️ Kısmi',
                        'ready' => '✅ Hazır',
                    }),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                
                TernaryFilter::make('has_images')
                    ->label('Görsel Durumu')
                    ->placeholder('Hepsi')
                    ->trueLabel('Fotoğraflar Tam')
                    ->falseLabel('Eksik Var')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('image_path')->whereNotNull('gallery'),
                        false: fn ($q) => $q->whereNull('image_path')->orWhereNull('gallery'),
                    ),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultPaginationPageOption(50);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMediaHubs::route('/'),
        ];
    }
}
