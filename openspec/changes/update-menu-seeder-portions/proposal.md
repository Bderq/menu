## Why

Şu anki `MenuSeeder` yapısı her ürünü tek bir fiyat (Portion: Standart) ile kaydediyor. Ancak işletmenin (Crash Pub) menüsünde özellikle içkiler (Viski, Kokteyl, Cin vb.) için "Shot", "Tek" ve "Duble" gibi farklı porsiyon ve fiyat seçenekleri bulunuyor. Ayrıca mevcut menü verileri güncelliğini yitirmiş durumda. 

Bu değişiklik ile menüyü güncel web sitesi verileriyle modernize etmek ve çoklu porsiyon desteğini seeder düzeyinde sağlamak hedeflenmektedir.

## What Changes

- `MenuSeeder.php` dosyası, porsiyon bazlı (multi-portion) veri yapısını destekleyecek şekilde güncellenecek.
- Veri kaynağı olarak `crashtheroof.com/menu/gorukle` adresinden parse edilen güncel ürünler kullanılacak.
- Seeder sadece `gorukle` şubesi verilerini içerecek şekilde daraltılacak.
- Kampanya (Campaign) kategorileri ve verileri seeder'dan tamamen çıkarılacak.
- `StoreProductPortion` tablosuna her ürün için ilgili porsiyonlar (Shot, Tek, Duble, Standart vb.) otomatik olarak tanımlanacak.

## Capabilities

### New Capabilities
- `multi-portion-seeding`: Ürünlere seeder üzerinden birden fazla isimlendirilmiş porsiyon ve fiyat atama yeteneği.

### Modified Capabilities
- `menu-data-management`: Menü verilerinin şube bazlı ve güncel fiyatlarla yönetilmesi süreci güncellendi.

## Impact

- `MenuSeeder.php`: Veri yapısı ve döngü mantığı değişecek.
- `DatabaseSeeder.php`: `CampaignSeeder` referansı kaldırıldı.
- Veritabanı: `stores`, `categories`, `products` ve `store_product_portions` tablolarındaki veriler güncellenecek.
