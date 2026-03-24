<?php

namespace App\Filament\Resources\MediaHubResource\Pages;

use App\Filament\Resources\MediaHubResource;
use Filament\Resources\Pages\ManageRecords;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Product;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ManageMediaHubs extends ManageRecords
{
    use WithFileUploads;

    protected static string $resource = MediaHubResource::class;

    public $inline_thumbnail;
    public $inline_gallery_files = [];

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\MediaHubStats::class,
        ];
    }

    public function processThumbnailUpload($recordId, $file)
    {
        try {
            $record = Product::find($recordId);
            if (!$record || !$this->inline_thumbnail) {
                \Filament\Notifications\Notification::make()
                    ->title('Hata')
                    ->body('Kayıt veya dosya bulunamadı.')
                    ->danger()
                    ->send();
                return;
            }

            $manager = new ImageManager(new Driver());
            $filename = Str::slug($record->name) . '-' . time() . '.webp';
            $path = 'products/thumbnails/' . $filename;
            
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('products/thumbnails');

            $image = $manager->read($this->inline_thumbnail->getRealPath());
            $image->cover(800, 800)->toWebp(80)->save(storage_path('app/public/' . $path));

            $record->update(['image_path' => $path]);
            $this->inline_thumbnail = null;

            \Filament\Notifications\Notification::make()
                ->title('Başarılı')
                ->body('Ürün görseli güncellendi.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            \Log::error('Media Hub Thumbnail Upload Error: ' . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Yükleme Hatası')
                ->body('Hata: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function processGalleryUpload($recordId, $files)
    {
        try {
            $record = Product::find($recordId);
            if (!$record || empty($this->inline_gallery_files)) {
                \Filament\Notifications\Notification::make()
                    ->title('Hata')
                    ->body('Dosyalar bulunamadı.')
                    ->danger()
                    ->send();
                return;
            }

            $manager = new ImageManager(new Driver());
            $currentGallery = $record->gallery ?? [];
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('products/gallery');

            foreach ($this->inline_gallery_files as $file) {
                $filename = Str::slug($record->name) . '-gallery-' . time() . '-' . Str::random(4) . '.webp';
                $path = 'products/gallery/' . $filename;
                
                $image = $manager->read($file->getRealPath());
                $image->cover(800, 800)->toWebp(80)->save(storage_path('app/public/' . $path));
                
                $currentGallery[] = $path;
            }

            $record->update(['gallery' => array_values(array_unique($currentGallery))]);
            $this->inline_gallery_files = [];

            \Filament\Notifications\Notification::make()
                ->title('Başarılı')
                ->body('Galeri güncellendi.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            \Log::error('Media Hub Gallery Upload Error: ' . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Hata')
                ->body('Galeri yüklenirken bir hata oluştu.')
                ->danger()
                ->send();
        }
    }
}
