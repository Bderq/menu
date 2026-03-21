<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StoresRelationManager extends RelationManager
{
    protected static string $relationship = 'stores';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('custom_name')
                    ->label('Override Name')
                    ->placeholder('Leave empty to use main name'),
                
                \Filament\Forms\Components\TextInput::make('custom_price')
                    ->label('Override Price')
                    ->numeric()
                    ->prefix('₺'),

                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label('Available in Store')
                    ->default(true),

                \Filament\Forms\Components\Toggle::make('is_featured')
                    ->label('Featured in Store'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Store Name')
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('custom_price')
                    ->label('Custom Price (₺)')
                    ->type('number')
                    ->placeholder(fn ($record) => $this->getOwnerRecord()->price), 

                Tables\Columns\TextInputColumn::make('custom_name')
                    ->label('Custom Name Overlay'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Add to Store'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
