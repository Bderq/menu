## Why

Mevcut admin panelinde mağazaya özel ürünlerin sıralanması (`sort_order`) manuel bir input alanına sayı girilerek yapılmaktadır. Filtrelenmiş bir liste üzerinde ürünleri sürükle-bırak (drag & drop) yöntemiyle sıralamak, özellikle menüyü hızlıca düzenlemek isteyen kullanıcılar için çok daha sezgisel ve profesyonel bir deneyim sağlayacaktır. Geleneksel sayı girme yöntemi artık eski ve verimsiz bir UX sunmaktadır.

## What Changes

- **Drag & Drop Reordering:** Tablo, sadece filtreleme yapıldıktan (örneğin bir kategori seçildikten) sonra aktifleşecek şekilde "sürüklenebilir" (reorderable) bir yapıya kavuşturulacaktır.
- **Canlı Pivot Güncellemesi:** Sürükleme yapıldığında sayfa yenilenmeden, arka planda ürünün ilgili mağazadaki sırası (`sort_order` pivot kolonu) live olarak güncellenecektir.
- **Input Alanlarının Kaldırılması:** Tablodaki mevcut "Sıra" (sort_order) TextInput silinerek görünüm basitleştirilecektir.

## Capabilities

### New Capabilities
- `store-product-reordering`: Mağaza ürünlerinin filtrelenmiş görünümlerde sürükle-bırak ile sıralanması.

### Modified Capabilities
- (None)

## Impact

- `StoreProducts.php` listeleme sayfası etkilenecektir.
- Veritabanı tarafında `store_product` pivot tablosu etkilenmektedir, performanslı güncelleme (Filament reorder callback) sağlanmalıdır.
