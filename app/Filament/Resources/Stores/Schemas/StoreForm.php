<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('logo_path'),
                \Filament\Forms\Components\ColorPicker::make('theme_color')
                    ->required()
                    ->default('#ffb000'),
                \Filament\Schemas\Components\Section::make('Spotify Integration')
                    ->description('Enter Spotify API credentials for this specific branch.')
                    ->schema([
                        TextInput::make('spotify_client_id')
                            ->label('Client ID')
                            ->maxLength(255),
                        TextInput::make('spotify_client_secret')
                            ->label('Client Secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        TextInput::make('spotify_refresh_token')
                            ->label('Refresh Token')
                            ->password()
                            ->revealable()
                            ->maxLength(1000),
                    ])
                    ->collapsed(),
            ]);
    }
}
