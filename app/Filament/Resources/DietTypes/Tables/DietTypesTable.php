<?php

namespace App\Filament\Resources\DietTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DietTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('Renk'),
                TextColumn::make('name')
                    ->label('Diyet Türü')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('icon')
                    ->label('İkon')
                    ->placeholder('—'),
                TextColumn::make('products_count')
                    ->label('Ürün Sayısı')
                    ->counts('products')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

