# Priority Gaps Analysis Tasks

Aşağıdaki adımlar Priority Gaps listesindeki teknik borçlara yönelik çıkartılmış görevlerdir. Her bir görev sırasıyla çözülmeli ve ardından bir sonraki göreve geçilmelidir.

## 1. Veritabanı ve Model İlişkisi (Kampanya ve Porsiyonlar)
- [x] `campaign_items` tablosundaki `portion_name` sütununu düşürüp, yerine `store_product_portion_id` (migration) değerini ekle. Eğer `product_id` ile birlikte tutulacaksa tutarlılığını sağla.
- [x] `App\Models\CampaignItem` içindeki `$fillable` alanını ve Model ilişkilerini (BelongsTo -> StoreProductPortion) bu yeni alan doğrultusunda güncelle.
- [x] Database seeder'larını (`CampaignSeeder` vb.) bu ID yapısını dikkate alacak şekilde revize et.

## 2. Tip Güvenliği İçin Enum Geçişi
- [x] `App\Enums\CategoryType.php` oluştur (`FOOD`, `DRINK`, `CAMPAIGN`).
- [x] `App\Enums\CampaignType.php` oluştur (`BUNDLE`, `PERCENTAGE`, `FIXED_PRICE`, `X_GET_Y`).
- [x] Bu enum tiplerini `CampaignService`, `MenuController` ve DB Migration'larındaki default valuelarda kullanarak string yığınlarını temizle.

## 3. N+1 Problem Çözümü ve Eager Loading
- [x] `MenuController@index` içerisinde ürünleri çekerken `portions` tablosunu `with(['portions' => function($q) use ($store) {...}])` şeklinde Eager Load et, böylece formatProduct içindeki döngü performans darboğazı ortadan kalksın.
- [x] Aynı şekilde ürünlerin `gallery` veya `badges` detaylarının gereksiz defalarca çekilmesi engellenmiş mi kontrol et.

## 4. Servis Mimarisine Geçiş (Refactoring)
- [x] `MenuController` içerisindeki karmaşık Best Sellers ve kategori formatlama işlemlerini oluşturulacak `App\Services\MenuService.php` içerisine taşı. Controller sadece datayı alıp Inertia'ya return etmeli.

## 5. Doğrulama (Testing)
- [x] Kodlamalar bittikten sonra uygulamanın `/gorukle` adresini tarayıcıda & terminal tarafında test et ve kampanyaların (ve porsiyon override'ların) eskisi gibi stabil çalışmaya devam ettiğini doğrula.
