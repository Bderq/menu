## ADDED Requirements

### Requirement: Drag & Drop Reordering Control
Sistem, kullanıcının `StoreProducts` listesinde manuel `TextInput` girmesi yerine, tablo üzerinden `row`'ları sürükle bırak yaparak sıralamalarını (`sort_order`) ayarlamasını sağlamalıdır.

#### Scenario: Sürüklenebilir Liste
- **WHEN** Admin tablo satırındaki handle ikonundan (veya satırdan) tutup ürünü yukarı veya aşağı taşıdığında
- **THEN** Sürüklenen ürün, bırakıldığı konumdaki sıraya yerleşecek şekilde görsel olarak arka planda güncellenir.

### Requirement: Filtre Bağımlı Reordering
Liste sayfalama kısıtlamaları veya rastgele sıralamanın bozulması ihtimaline karşı Reorder işlemi sadece Mantıklı bir Filtre uygulandığı durumda (Örneğin belirli bir kategori filtrelenmiş iken) çalışmalıdır. (İsteğe bağlı veya best-practice)

#### Scenario: Kategori Filtresi Olmadan Sıralama Yapılamaması (Optional)
- **WHEN** Admin hiçbir kategori filtresi koymadığında (binlerce ürün)
- **THEN** Tabloda Reorder ikonları gözükmez veya sürükleme kapatılarak kullanıcı uyarılarak performans kaybı yaşanması engellenir.

### Requirement: Canlı VeriTabanı (Pivot) Kaydı
StoreProducts yapısı Product Model'ini query'lese dahi, sıralama Product ve Store arasındaki *pivot tablodaki* `sort_order` satırını etkilemelidir.

#### Scenario: Pivot Sort Order Güncelleme
- **WHEN** Element yeni pozisyonuna bırakıldığında
- **THEN** Sistem livewire callback'ini kullanarak (`reorderRecordsUsing(...)` veya eşdeğeri), ilgili store_id ve etkilenen product_id'lerin `store_product` tablosundaki `sort_order` sayısını otomatik olarak yazar.
