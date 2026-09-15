<?php

namespace App\Filament\Resources\StoreTables\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StoreTableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')
                    ->label('Şube')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Masa Adı')
                    ->placeholder('Masa 5')
                    ->required(),
                TextInput::make('location')
                    ->label('Konum')
                    ->placeholder('Bahçe, Teras...'),
                TextInput::make('capacity')
                    ->label('Kapasite')
                    ->numeric(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
