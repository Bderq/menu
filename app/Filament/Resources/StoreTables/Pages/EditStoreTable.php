<?php

namespace App\Filament\Resources\StoreTables\Pages;

use App\Filament\Resources\StoreTables\StoreTableResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStoreTable extends EditRecord
{
    protected static string $resource = StoreTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
