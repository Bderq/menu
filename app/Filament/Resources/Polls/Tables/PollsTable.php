<?php

namespace App\Filament\Resources\Polls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class PollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color('info'),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'archived' => 'danger',
                    }),
                \Filament\Tables\Columns\TextColumn::make('store.name')
                    ->label('Şube')
                    ->placeholder('Tüm Şubeler'),
                \Filament\Tables\Columns\TextColumn::make('votes_count')
                    ->label('Oy')
                    ->counts('votes')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Taslak',
                        'active' => 'Aktif',
                        'archived' => 'Arşivlendi',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('store')
                    ->relationship('store', 'name'),
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
