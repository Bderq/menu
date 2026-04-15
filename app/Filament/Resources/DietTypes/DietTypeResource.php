<?php

namespace App\Filament\Resources\DietTypes;

use App\Filament\Resources\DietTypes\Pages\CreateDietType;
use App\Filament\Resources\DietTypes\Pages\EditDietType;
use App\Filament\Resources\DietTypes\Pages\ListDietTypes;
use App\Filament\Resources\DietTypes\Schemas\DietTypeForm;
use App\Filament\Resources\DietTypes\Tables\DietTypesTable;
use App\Models\DietType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DietTypeResource extends Resource
{
    protected static ?string $model = DietType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $modelLabel = 'Diyet Türü';

    protected static ?string $pluralModelLabel = 'Diyet Türleri';

    public static function form(Schema $schema): Schema
    {
        return DietTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DietTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDietTypes::route('/'),
            'create' => CreateDietType::route('/create'),
            'edit' => EditDietType::route('/{record}/edit'),
        ];
    }
}
