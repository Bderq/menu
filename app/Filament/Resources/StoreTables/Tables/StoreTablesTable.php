<?php

namespace App\Filament\Resources\StoreTables\Tables;

use App\Models\StoreTable;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StoreTablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('qr_token')
                    ->label('QR')
                    ->getStateUsing(fn (StoreTable $record) => 'data:image/svg+xml;base64,' . base64_encode(
                        QrCode::size(100)->generate($record->menuUrl())
                    ))
                    ->height(60),
                TextColumn::make('store.name')
                    ->label('Şube')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Masa Adı')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('location')
                    ->label('Konum')
                    ->toggleable(),
                TextColumn::make('capacity')
                    ->label('Kapasite')
                    ->toggleable(),
                TextColumn::make('visits_count')
                    ->counts('visits')
                    ->label('Ziyaret'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('store_id')
                    ->label('Şube')
                    ->relationship('store', 'name'),
            ])
            ->recordActions([
                Action::make('downloadQr')
                    ->label('QR indir')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (StoreTable $record) {
                        $svg = QrCode::size(400)->generate($record->menuUrl());

                        return response()->streamDownload(
                            fn () => print($svg),
                            \Illuminate\Support\Str::slug($record->store->name . '-' . $record->name) . '.svg'
                        );
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
