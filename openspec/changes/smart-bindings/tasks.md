# Smart Binding Implementation Tasks

Bu görevler, görsel dosyaları ile veritabanı kayıtları arasında kalıcı bir bağ kurarak `migrate:fresh` sonrası otomatik eşleşme sağlamayı amaçlar.

## 1. Filament: Dosya İsimlendirme Standardı
- [ ] `App\Filament\Resources\Products\Schemas\ProductForm.php` dosyasını güncelleyin:
    - `image_path` için `getUploadedFileNameForStorageUsing` kullanarak ürün slug'ı bazlı isimlendirme ekleyin.
    - `gallery` için her görselin sırasıyla `slug-1.webp`, `slug-2.webp` şeklinde kaydedilmesini sağlayın.
- [ ] `App\Filament\Resources\CampaignResource.php` dosyasını güncelleyin:
    - `image_path` alanına kampanya slug'ı bazlı isimlendirme ekleyin.

## 2. MenuSeeder: Auto-Discovery Mantığı
- [ ] `Database\Seeders\MenuSeeder.php` içerisindeki `processMenuTree` metodunu güncelleyin:
    - Veritabanı kaydı oluşturulmadan önce `Storage::disk('public')` üzerinden o ürünün slug'ına uygun bir dosya (`.webp`) olup olmadığını kontrol eden bir logic ekleyin.
    - Dosya varsa, model oluşturulurken `image_path` alanına bu yolu otomatik atayın.
- [ ] Kampanyalar için de benzer bir dosya kontrolü logic'i ekleyin.

## 3. Doğrulama (Testing)
- [ ] Önce bir ürüne Panel üzerinden (Filament) bir fotoğraf yükleyin.
- [ ] `storage/app/public/products/thumbnails/` klasörüne gidip dosya isminin beklendiği gibi (örn: `cheeseburger.webp`) olduğunu doğrulayın.
- [ ] Terminalden `php artisan migrate:fresh --seed` komutunu çalıştırın.
- [ ] Menü sayfasını açtığınızda fotoğrafın otomatik olarak geri geldiğini ve eşleştiğini görün.
