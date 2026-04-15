<?php

namespace App\Filament\Resources\DietTypes\Pages;

use App\Filament\Resources\DietTypes\DietTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDietType extends EditRecord
{
    protected static string $resource = DietTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
