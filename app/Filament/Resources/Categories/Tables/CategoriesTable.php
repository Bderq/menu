<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent'))
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Category')
                    ->formatStateUsing(fn ($record) => $record->hierarchical_name)
                    ->description(fn ($record) => $record->slug)
                    ->searchable(['name', 'slug']),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (\App\Enums\CategoryType $state): string => match ($state) {
                        \App\Enums\CategoryType::FOOD => 'Food',
                        \App\Enums\CategoryType::DRINK => 'Drink',
                        \App\Enums\CategoryType::CAMPAIGN => 'Campaign',
                        default => $state->value,
                    })
                    ->color(fn (\App\Enums\CategoryType $state): string => match ($state) {
                        \App\Enums\CategoryType::FOOD => 'warning',
                        \App\Enums\CategoryType::DRINK => 'info',
                        \App\Enums\CategoryType::CAMPAIGN => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
