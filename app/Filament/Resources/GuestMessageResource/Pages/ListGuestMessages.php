<?php

namespace App\Filament\Resources\GuestMessageResource\Pages;

use App\Filament\Resources\GuestMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListGuestMessages extends ListRecords
{
    protected static string $resource = GuestMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Gerekli değil çünkü mesajlar müşteriden geliyor
        ];
    }
}
