<?php

namespace App\Filament\Resources\Allergens\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AllergenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Alerjen Adı')
                    ->required()
                    ->maxLength(255),
                TextInput::make('icon')
                    ->label('İkon (Heroicon)')
                    ->placeholder('heroicon-o-exclamation-triangle')
                    ->helperText('heroicon-o-* formatında Heroicon adı girin.')
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label('Renk')
                    ->default('#6B7280'),
            ]);
    }
}

