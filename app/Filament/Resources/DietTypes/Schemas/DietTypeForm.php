<?php

namespace App\Filament\Resources\DietTypes\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DietTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Diyet Türü Adı')
                    ->required()
                    ->maxLength(255),
                TextInput::make('icon')
                    ->label('İkon (Heroicon)')
                    ->placeholder('heroicon-o-leaf')
                    ->helperText('heroicon-o-* formatında Heroicon adı girin.')
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label('Renk')
                    ->default('#10B981'),
            ]);
    }
}

