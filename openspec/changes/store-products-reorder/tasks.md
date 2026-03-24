## 1. Filament Altyapısı (Reorder Aktifleştirme)

- [x] 1.1 `StoreProducts.php` içindeki tabloda `sort_order` kolonunu manuel giriş özelliği olan `TextInputColumn` halinden çıkarıp gizli bir sütuna kalibre et veya sil.
- [x] 1.2 `reorderable()` fonksiyonunu `->reorderable('sort_order')` (tabloya özelleştirilmiş veya pivot'a manuel eklenecek logik ile) aktif et.

## 2. Pivot Güncelleme Logic (Backend)

- [x] 2.1 Filament'in varsayılan `reorderable` yapısı pivot tablodaki field'ı güncellemezse doğrudan tabloya `reorderRecordsUsing(fn (...) => ...)` gibi bir callback ekleyerek sürüklenen Product'ların `$currentStore->id` ile eşleştiği `store_product` satırını güncelleyecek kodu yaz.

## 3. Liste Kısıtlaması (Filtre veya Tab Bazlı Aktifleşme)

- [x] 3.1 Tablonun `reorderable()` durumuna mantıksal bir şart bağla. `fn() => request('tableFilters.category') !== null` tarzı bir şekilde, sadece ve sadece kategori seçili olduğunda Drag&Drop özelliğinin aktif kalmasını sağla. Aksi takdirde kapalı olsun. 

## 4. Testler 

- [x] 4.1 Tüm menüdeyken reordering işleminin kapalı olduğunu doğrula.
- [x] 4.2 Bir alt kategoriye filtre atılıp, ilk elemanın sona atıldığında sadece pivot tablosundaki (mevcut mağazaya ait) sıranın canlı olarak kaydedildiğini doğrula.
