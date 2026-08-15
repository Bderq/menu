<?php

namespace App\Filament\Resources\PosterResource\Pages;

use App\Filament\Resources\PosterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPoster extends EditRecord
{
    protected static string $resource = PosterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
