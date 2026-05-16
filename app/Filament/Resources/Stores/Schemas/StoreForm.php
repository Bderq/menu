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
                \Filament\Schemas\Components\Section::make('Google Business')
                    ->description('Set up Google review redirection for this branch.')
                    ->schema([
                        TextInput::make('google_review_url')
                            ->label('Google Review URL')
                            ->url()
                            ->placeholder('https://g.page/r/isletme-id/review')
                            ->maxLength(255),
                        \Filament\Forms\Components\Textarea::make('google_review_question')
                            ->label('Review Question')
                            ->placeholder('Bu akşam iyi geçti mi? 🍺')
                            ->rows(2)
                            ->maxLength(255),
                    ])
                    ->collapsed(),
            ]);
    }
}
