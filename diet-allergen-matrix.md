# Diet & Allergen Matrix Yönetim Sayfası

## Goal
Admin, tek bir sayfada **kategori filtresini** seçip, o kategorideki tüm ürünleri satırlarda, tüm diyet türü + alerjenleri sütunlarda görerek checkbox tıklamalarıyla toplu atama/kaldırma yapabilsin.

## İlkeler
- **Renk Zıtlığı Zorunlu:** Her sütun için `color` alanı kullanılır. Yazı rengi arka plandan belirgin olacak şekilde otomatik belirlenir (`is_dark(color) ? white : black`).
- **Minimalist:** Thumbnail yok; sadece ürün ismi, kategori adı ve checkbox hücreleri.
- **Kategori Filtreli:** Sayfa açıldığında ilk kategori seçili gelir; seçim değişince liste yenilenir.

## Tasks

- [ ] 1.1 `DietAllergenMatrix` Filament Custom Page sınıfı oluştur. → Verify: `app/Filament/Pages/DietAllergenMatrix.php` mevcut ve kayıtsız hata yok.
- [ ] 1.2 `getMatrixData()` metodunu yaz: Seçili kategorinin ürünlerini + tüm DietType + Allergen'leri fetch et, mevcut pivot kayıtlarını bir map'e al. → Verify: `dd()` ile output doğru.
- [ ] 1.3 Blade view oluştur (`resources/views/filament/pages/diet-allergen-matrix.blade.php`). Üstte kategori seçimi + Kaydet butonu, altta scroll edilebilir matrix tablosu (Livewire Alpine.js). → Verify: Sayfa panelde hatasız açılıyor.
- [ ] 1.4 Sütun header'larındaki arka plan için `color` alanını kullan; yazı rengini arka plana göre kontrast hesabıyla (yüzde 50 lightness) beyaz/siyah olarak ayarla. → Verify: Tüm başlıklar okunabilir.
- [ ] 1.5 Checkbox tıklandığında Livewire ile `toggleTag($productId, $type, $tagId)` metodunu çağır — `type = 'diet'|'allergen'`. Immediately save (no batch). → Verify: Tıklama anında DB kaydı oluşuyor/siliniyor.
- [ ] 1.6 Filament navigation'a "Diyet & Alerjen Matrix" linkini 'Ürün Yönetimi' grubuna ekle. → Verify: Sol menüde link görünüyor.

## Done When
- [ ] Admin, bir kategori seçip tüm ürünlerin diyet ve alerjenlerini checkbox matrisinden yönetebiliyor.
- [ ] Sütun başlıkları her renkte okunabilir (siyah bg'de beyaz yazı, açık bg'de siyah yazı).
- [ ] Tıklama anında kayıt yapılıyor, ayrıca Save butonu gerekmiyor.

## Notes
- Referans: `BulkPricing.php` — aynı Custom Page + Alpine.js pattern'i kullanılacak.
- Dietary types önce (3 sütun), ardından Allergens (12 sütun) gelecek; aralarına dikey bir border ayırıcı eklenecek.
- Canlı veritabanı — `sync` yerine `attach`/`detach` kullanılacak (pivot'u silmeden ekleme/çıkarma).
