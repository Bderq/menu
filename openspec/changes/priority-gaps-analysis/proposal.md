# Priority Gaps Analysis Proposal

## Özet
Bu değişiklik, uygulamanın çekirdek mimarisindeki kırılgan yapıları, kod tekrarlarını (Fat Controller) ve sistemi büyük ölçüde yavaşlatan veritabanı performans sorunlarını (N+1 problemleri) tespit etmek ve onarmak amacıyla oluşturulmuştur.

## Tespit Edilen Kritik Sorunlar ve Teknik Borçlar (Technical Debts)

### 1. Kırılgan Porsiyon-Kampanya İlişkisi
Şu an `CampaignItem` tablosundaki hedeflenen porsiyonlar `portion_name` adında bir string (metin) üzerinden eşleşmektedir (`str_contains(strtolower($option['name']), strtolower($targetItem->portion_name))`). Bu durum, ürün porsiyon isminde yapılacak ufak bir harf hatası veya değişiklikte (`"50 cl"` -> `"50cl"` vb.) tüm kampanyanın kırılmasına yol açacak kadar risklidir. Güçlü bir veritabanı (Foreign Key ID) ilişkisi gereklidir.

### 2. Controller'da N+1 Sorgu Problemi (Performans Darboğazı)
`MenuController@index` içindeki kategori döngülerinde her ürün için:
```php
$portions = \App\Models\StoreProductPortion::where('product_id', $product->id)...
```
şeklinde ek sorgular (DB Call) atılmaktadır. Menüde yüzlerce ürün olduğunda bu durum sayfa yükleme süresini dramatik ölçüde (Time to First Byte - TTFB) etkilemektedir. Bu verilerin `with('portions')` kullanılarak Eager Load edilmesi zorunludur.

### 3. "Fat Controller" ve İş Mantığı Karmaşası
Best Sellers hiyerarşisinin hesaplanması, veri formatlama adımları (`formatProduct`) ve kampanya detay hesaplamaları gibi işlemlerin tamamı `MenuController` içine yığılmıştır (300+ satır). Bu logic'lerin Service sınıflarına aktarılması test edilebilirliği artıracaktır.

### 4. Hardcoded (Sabit) Sihirli Kelimeler
Routing, veritabanı sorguları ve Controller genelinde `'campaign'`, `'food'`, `'drink'`, `'bundle'`, `'fixed_price'` gibi string'ler dağınık olarak kullanılmıştır. Bunların Typo (yazım hatası) riskini engellemek için PHP Enum sınıflarına geçirilmesi şarttır.
