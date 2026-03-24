<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MediaHubStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Product::count();
        $missingThumbnails = Product::whereNull('image_path')->orWhere('image_path', '')->count();
        $missingGallery = Product::whereNull('gallery')->orWhereJsonLength('gallery', 0)->count();

        return [
            Stat::make('Toplam Ürün', $total)
                ->description('Katalogdaki toplam ürün sayısı')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('gray'),

            Stat::make('Görseli Olmayanlar', $missingThumbnails)
                ->description('Thumbnail (ana fotoğraf) bekleyen')
                ->descriptionIcon('heroicon-m-photo')
                ->color($missingThumbnails > 0 ? 'danger' : 'success')
                ->chart([10, 5, 20, 15, 30, $missingThumbnails]),

            Stat::make('Galerisi Eksikler', $missingGallery)
                ->description('Kart görselleri yüklenmemiş')
                ->descriptionIcon('heroicon-m-photo')
                ->color($missingGallery > 0 ? 'warning' : 'success')
                ->chart([5, 12, 8, 15, 7, $missingGallery]),
        ];
    }
}
