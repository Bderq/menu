## Context

Mevcut `MenuSeeder.php` yapısında ürünler tek fiyatlı olarak kurgulanmıştır. Bu tasarım dokümanı, seeder'ın çoklu porsiyon (portions) desteği kazanmasını ve veritabanına bu opsiyonları doğru bir şekilde (porsiyon isimleri ve fiyatları ile) aktarmasını sağlar.

## Goals / Non-Goals

**Goals:**
- `MenuSeeder` veri dizisine porsiyon desteği eklemek.
- `StoreProductPortion` tablosunu her ürün için (Shot, Tek, Duble) dinamik olarak doldurmak.
- Menü yapısını web sitesindeki `Aperitif`, `Ana Yemek`, `Pizza` vb. ana kategorilere göre basitleştirmek.
- Sadece `gorukle` şubesi verilerini yüklemek.

**Non-Goals:**
- Ürünlerin admin paneli (Filament) üzerinden porsiyon ekleme arayüzünü değiştirmek (Bu zaten mevcut).
- Yeni bir veritabanı tablosu oluşturmak (Mevcut `StoreProductPortion` kullanılacak).
- Kampanyaların teknik altyapısını değiştirmek (Sadece pasifize edilecek).

## Decisions

- **Dinamik Veri Kontrolü:** Seeder içinde her ürün için `price` (tekil) veya `portions` (çoklu) anahtarları kontrol edilecek.
- **Porsiyon İsimleri:** Viski porsiyon başlıkları için standart olarak "Shot", "Tek", "Duble" kullanılacak; kokteyller için "Tek", "Duble" kullanılacak.
- **Seeding Mantığı:** `MenuSeeder` içindeki `$allStores` dizisi sadece `gorukle` kaydını barındıracak şekilde daraltılacak.
- **Görseller:** Ürün slug'ları üzerinden `storage/app/public` dizinindeki mevcut resimlerle eşleştirme yapılmaya devam edilecek.

## Risks / Trade-offs

- **Fiyat Karmaşası:** Bazı içeceklerin (şişe bira vb.) web sitesinde porsiyon bilgisi yokken, bazılarının (Votka, Viski) var. Bu durum seeder dizisi içinde her ürün için özel tanımlama yapılmasını zorunlu kılıyor (Otomatik porsiyon yaratma riskli, manuel tanım güvenli).
- **Eski Veriler:** Seeder çalıştığında `gorukle` dışındaki şubelerin menüleri boş kalacaktır (Kullanıcı tercihi).
