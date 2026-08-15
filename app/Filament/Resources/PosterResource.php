<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosterResource\Pages;
use App\Models\Poster;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PosterResource extends Resource
{
    protected static ?string $model = Poster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Posterler';
    protected static ?string $modelLabel = 'Poster';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Temel İçerik')
                    ->schema([
                        TextInput::make('title')
                            ->label('Ana Başlık')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('subtitle')
                            ->label('Alt Başlık')
                            ->maxLength(255),
                        TextInput::make('badge_text')
                            ->label('Rozet Metni')
                            ->placeholder('Örn: BUGÜNÜN ÖZEL MENÜSÜ')
                            ->maxLength(255),
                        Textarea::make('body')
                            ->label('Kısa Açıklama')
                            ->maxLength(65535),
                        Select::make('color_scheme')
                            ->label('Tema Rengi')
                            ->options([
                                'dark' => 'Koyu (Açık renk yazı)',
                                'light' => 'Açık (Koyu renk yazı)',
                            ])
                            ->default('dark')
                            ->required(),
                    ]),
                
                Section::make('Görsel')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Kapak / Arka Plan Görseli')
                            ->disk('public')
                            ->directory('posters')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(1200)
                            ->getUploadedFileNameForStorageUsing(function ($file, $get) {
                                $name = $get('title') ? Str::slug($get('title')) : 'poster-' . time();
                                return $name . '.webp';
                            }),
                    ]),

                Section::make('Aksiyon (Opsiyonel)')
                    ->schema([
                        TextInput::make('cta_text')
                            ->label('Buton Etiketi')
                            ->placeholder('Örn: Menüye Git'),
                        TextInput::make('cta_url')
                            ->label('Buton Linki')
                            ->placeholder('Örn: #menu'),
                    ]),

                Section::make('Yayın Ayarları')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('Başlangıç')
                                    ->native(false)
                                    ->displayFormat('d.m.Y H:i')
                                    ->placeholder('Hemen başlar'),
                                DateTimePicker::make('ends_at')
                                    ->label('Bitiş')
                                    ->native(false)
                                    ->displayFormat('d.m.Y H:i')
                                    ->placeholder('Süresiz'),
                            ]),
                        TextInput::make('priority')
                            ->label('Öncelik')
                            ->numeric()
                            ->default(0)
                            ->helperText('Birden fazla poster aktifse yüksek öncelikli olan gösterilir.'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Görsel')
                    ->square(),
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->description(fn (Poster $record): string => $record->subtitle ?? ''),
                TextColumn::make('badge_text')
                    ->label('Rozet')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('priority')
                    ->label('Öncelik')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->state(function (Poster $record): string {
                        $now = Carbon::now();
                        
                        if (!$record->is_active) {
                            return 'Pasif';
                        }
                        
                        if ($record->starts_at && $now->lt($record->starts_at)) {
                            return 'Gelecek';
                        }
                        
                        if ($record->ends_at && $now->gt($record->ends_at)) {
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
                TextColumn::make('starts_at')
                    ->label('Tarih Aralığı')
                    ->dateTime('d.m.Y H:i')
                    ->description(fn (Poster $record) => $record->ends_at ? 'Bitiş: ' . $record->ends_at->format('d.m.Y H:i') : 'Süresiz'),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPosters::route('/'),
            'create' => Pages\CreatePoster::route('/create'),
            'edit' => Pages\EditPoster::route('/{record}/edit'),
        ];
    }
}
