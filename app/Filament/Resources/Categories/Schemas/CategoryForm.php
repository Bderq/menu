<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(function (?\App\Models\Category $record) {
                        return \App\Models\Category::query()
                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                            ->get()
                            ->mapWithKeys(fn ($category) => [$category->id => $category->hierarchical_name]);
                    })
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('type')
                    ->options([
                        \App\Enums\CategoryType::FOOD->value => 'Food',
                        \App\Enums\CategoryType::DRINK->value => 'Drink',
                        \App\Enums\CategoryType::CAMPAIGN->value => 'Campaign',
                    ])
                    ->required()
                    ->default(\App\Enums\CategoryType::FOOD->value),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
