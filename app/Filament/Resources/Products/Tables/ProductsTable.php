<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkAction; // Custom Bulk Action
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->square()
                    ->width(40)
                    ->toggleable(),

                TextInputColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->grow()
                    ->extraAttributes(['class' => 'font-semibold'])
                    ->rules(['required', 'max:255']),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('stores.name')
                    ->label('Bulunduğu Dükkanlar')
                    ->badge()
                    ->color('info')
                    ->wrap()
                    ->searchable(),

                ToggleColumn::make('is_active')
                    ->label('Global Aktif')
                    ->alignCenter(),
            ])
            ->defaultSort('id')
            ->defaultPaginationPageOption(50)
            ->striped()
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
