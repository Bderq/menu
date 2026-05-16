<?php

namespace App\Filament\Resources\Polls\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anket Detayları')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('question')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options([
                                'single_choice' => 'Tekli Seçim',
                                'emoji_reaction' => 'Emoji Reaksiyon',
                                'star_rating' => '1-5 Yıldız',
                            ])
                            ->required()
                            ->default('single_choice'),
                        Select::make('status')
                            ->options([
                                'draft' => 'Taslak',
                                'active' => 'Aktif',
                                'archived' => 'Arşivlendi',
                            ])
                            ->required()
                            ->default('draft'),
                        Toggle::make('show_once')
                            ->label('Parmak izi ile 1 kez göster')
                            ->default(true),
                        Select::make('store_id')
                            ->label('Şube Kısıtlaması')
                            ->relationship('store', 'name')
                            ->nullable()
                            ->placeholder('Tüm Şubeler'),
                    ])->columns(2),

                Section::make('Cevap Seçenekleri')
                    ->schema([
                        Repeater::make('options')
                            ->relationship()
                            ->schema([
                                TextInput::make('text')
                                    ->required()
                                    ->placeholder('Seçenek metni'),
                                TextInput::make('emoji')
                                    ->placeholder('Emoji (opsiyonel)'),
                                Hidden::make('sort_order')
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->reorderable('sort_order')
                            ->collapsible()
                            ->minItems(2),
                    ]),

                Section::make('Zamanlama Takvimi')
                    ->schema([
                        Repeater::make('schedules')
                            ->relationship()
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->required(),
                                DateTimePicker::make('ends_at')
                                    ->required(),
                                CheckboxList::make('days_of_week')
                                    ->options([
                                        '1' => 'Pazartesi',
                                        '2' => 'Salı',
                                        '3' => 'Çarşamba',
                                        '4' => 'Perşembe',
                                        '5' => 'Cuma',
                                        '6' => 'Cumartesi',
                                        '0' => 'Pazar',
                                    ])
                                    ->columns(4)
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ]),
            ]);
    }
}
