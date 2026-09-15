<?php

namespace App\Filament\Resources\StoreTables;

use App\Filament\Resources\StoreTables\Pages\CreateStoreTable;
use App\Filament\Resources\StoreTables\Pages\EditStoreTable;
use App\Filament\Resources\StoreTables\Pages\ListStoreTables;
use App\Filament\Resources\StoreTables\Schemas\StoreTableForm;
use App\Filament\Resources\StoreTables\Tables\StoreTablesTable;
use App\Models\StoreTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StoreTableResource extends Resource
{
    protected static ?string $model = StoreTable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'Masalar';

    protected static ?string $modelLabel = 'Masa';

    protected static ?string $pluralModelLabel = 'Masalar';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return StoreTableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoreTablesTable::configure($table);
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
            'index' => ListStoreTables::route('/'),
            'create' => CreateStoreTable::route('/create'),
            'edit' => EditStoreTable::route('/{record}/edit'),
        ];
    }
}
