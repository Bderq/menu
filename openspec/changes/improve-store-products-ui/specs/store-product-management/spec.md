## ADDED Requirements

### Requirement: Tablo Görseli Optimizasyonu
Sistem `ImageColumn` boyutunu minimize etmeli ve kullanıcının göz önünde olmamasını sağlamalı.

#### Scenario: Liste görünümünde görsellerin küçülmesi
- **WHEN** Admin mağaza ürünleri sayfasına (`/admin/products/store/{id}`) girdiğinde
- **THEN** Tablodaki ürün görselleri 40px yerine çok daha küçük veya gizlenebilir bir formda görüntülenir.

### Requirement: Gelişmiş Kategori Filtrelemesi
Sistem, ürünleri Kategori ve Alt Kategori hiyerarşisine göre (Parent/Child) filtrelemeye olanak tanımalıdır.

#### Scenario: Admin üst veya alt kategoriye göre ürünleri listeler
- **WHEN** Admin filtrelerden bir kategori seçtiğinde
- **THEN** Tablo o kategoriye (ve seçildiyse alt kategorilerine) ait ürünleri listeler.

### Requirement: Hızlı Inline Stok Yönetimi
Sistem, tablo içerisindeki `store_active` ve `is_featured` gibi toggle sütunlarını tablonun daha erişilebilir bir yerinde tutmalı ve sayfa yenilemeden durum değişikliğini kaydetmelidir.

#### Scenario: Bir ürünün satıştan kaldırılması
- **WHEN** Admin tabloda ilgili ürünün `Aktif` toggle'ını kapalı konuma getirdiğinde
- **THEN** Sistem arkada AJAX isteğini gönderir ve sadece ilgili toggle güncellenir, sayfa yenilenmez.

### Requirement: Kompakt Veri Sütunları
Sistem gereksiz boşlukları (white space) azaltmalı, bir ekranda görünen satır sayısını artırmalı ve fiyat bilgisini sadeleştirmelidir.

#### Scenario: Porsiyon özetinin incelenmesi
- **WHEN** Admin fiyat özetine baktığında
- **THEN** Tablo hücresinde gereksiz rozetler (badge vb.) azaltılmış olarak sadece metin formatında net fiyatlar ve artı porsiyon sayısı görünür, satır boyutları sıkışık tasarımdadır.
