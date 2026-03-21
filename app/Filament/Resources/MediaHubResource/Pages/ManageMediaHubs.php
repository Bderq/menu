<?php

namespace App\Filament\Resources\MediaHubResource\Pages;

use App\Filament\Resources\MediaHubResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMediaHubs extends ManageRecords
{
    protected static string $resource = MediaHubResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
