<?php

namespace App\Filament\Resources\DietTypes\Pages;

use App\Filament\Resources\DietTypes\DietTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDietTypes extends ListRecords
{
    protected static string $resource = DietTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
