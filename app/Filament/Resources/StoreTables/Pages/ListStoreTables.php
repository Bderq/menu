<?php

namespace App\Filament\Resources\StoreTables\Pages;

use App\Filament\Resources\StoreTables\StoreTableResource;
use App\Models\StoreTable;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use ZipArchive;

class ListStoreTables extends ListRecords
{
    protected static string $resource = StoreTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulkCreate')
                ->label('Toplu Masa Oluştur')
                ->icon('heroicon-o-squares-plus')
                ->form([
                    Select::make('store_id')
                        ->label('Şube')
                        ->relationship('store', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('name_prefix')
                        ->label('Ad Öneki')
                        ->default('Masa')
                        ->required(),
                    TextInput::make('start')
                        ->label('Başlangıç No')
                        ->numeric()
                        ->default(1)
                        ->required(),
                    TextInput::make('end')
                        ->label('Bitiş No')
                        ->numeric()
                        ->default(10)
                        ->required(),
                    TextInput::make('location')
                        ->label('Konum (opsiyonel)'),
                    TextInput::make('capacity')
                        ->label('Kapasite (opsiyonel)')
                        ->numeric(),
                ])
                ->action(function (array $data) {
                    $start = (int) $data['start'];
                    $end = (int) $data['end'];

                    abort_unless($end >= $start, 422, 'Bitiş numarası başlangıçtan küçük olamaz.');
                    abort_unless(($end - $start) < 500, 422, 'Tek seferde en fazla 500 masa oluşturabilirsin.');

                    for ($i = $start; $i <= $end; $i++) {
                        StoreTable::create([
                            'store_id' => $data['store_id'],
                            'name' => trim($data['name_prefix'] . ' ' . $i),
                            'location' => $data['location'] ?: null,
                            'capacity' => $data['capacity'] ?: null,
                            'is_active' => true,
                        ]);
                    }

                    Notification::make()
                        ->title(($end - $start + 1) . ' masa oluşturuldu')
                        ->success()
                        ->send();
                }),
            Action::make('bulkDownload')
                ->label('Toplu İndir')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->form([
                    Select::make('store_id')
                        ->label('Şube (boş bırakırsan tüm şubeler)')
                        ->relationship('store', 'name')
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data) {
                    $tables = StoreTable::query()
                        ->with('store')
                        ->when($data['store_id'] ?? null, fn ($query, $storeId) => $query->where('store_id', $storeId))
                        ->orderBy('store_id')
                        ->orderBy('name')
                        ->get();

                    abort_if($tables->isEmpty(), 422, 'İndirilecek masa bulunamadı.');

                    $zipPath = tempnam(sys_get_temp_dir(), 'masa_qr_') . '.zip';
                    $zip = new ZipArchive();
                    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

                    $usedNames = [];

                    foreach ($tables as $table) {
                        $folder = Str::of($table->store->name)->replace(['/', '\\'], '-')->trim();
                        $fileBase = Str::of($table->name)->replace(['/', '\\'], '-')->trim();

                        $entryPath = $folder . '/' . $fileBase . '.svg';

                        // Avoid collisions if two tables in the same store share a name
                        $suffix = 1;
                        while (isset($usedNames[$entryPath])) {
                            $entryPath = $folder . '/' . $fileBase . ' (' . (++$suffix) . ').svg';
                        }
                        $usedNames[$entryPath] = true;

                        $svg = QrCode::size(400)->generate($table->menuUrl());
                        $zip->addFromString($entryPath, $svg);
                    }

                    $zip->close();

                    return response()->download($zipPath, 'masa-qr-kodlari.zip')->deleteFileAfterSend(true);
                }),
            Action::make('printAll')
                ->label('Tüm QR\'ları yazdır')
                ->icon('heroicon-o-printer')
                ->url(fn () => route('store-tables.print'))
                ->openUrlInNewTab(),
            CreateAction::make(),
        ];
    }
}
