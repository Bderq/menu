# Image Optimization Tasks: "The 100-Product Speed"

## 1. Frontend: Lazy Loading
- [x] React tarafındaki (örn. `resources/js/Pages/Menu/Index.jsx` vb.) tüm `<img>` etiketlerine `loading="lazy"` özelliğini ekle. (Eğer Framer Motion veya benzeri kullanılıyorsa ona uygun entegrasyon yap).

## 2. Filament: Sert Kurallar (Hard Constraints)
- [x] `App\Filament\Resources\Products\Schemas\ProductForm.php` içindeki `image_path` ve `gallery` FileUpload'larına şu kuralları ekle:
  - `->maxSize(300)`
  - `->optimize('webp')`
  - `->imageResizeTargetWidth(800)`
  - `->imageResizeMode('cover')`
- [x] `App\Filament\Resources\CampaignResource.php` içindeki `image_path` alanına aynı kuralları uygula.

## 3. "Batch" Dönüştürme: Artisan Command
- [x] `App\Console\Commands\OptimizeMedia.php` komutunu oluştur (`php artisan media:optimize`).
- [x] Komut; `Product` (image_path, gallery), `Campaign` (image_path) kayıtlarını döngüye almalı. Eski fotoları diskten okuyup 800px'e resize edip WebP olarak kaydetmeli ve DB yollarını güncellemelidir.
