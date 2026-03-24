## 1. Veri Tablosu Görünümü Optimizasyonu

- [ ] 1.1 `StoreProducts.php` içindeki `ImageColumn` boyutunu minimize et (veya toggle ile gizlenebilir yap).
- [ ] 1.2 `portions_summary` TextColumn kısmındaki badge veya gereksiz stilleri kaldırarak sadeliğe kavuştur.
- [ ] 1.3 `name` sütunundaki Description kısmını kaldırıp Kategori için ayrı ve özlü bir sütun oluştur.

## 2. Gelişmiş Navigasyon & Filtreleme

- [ ] 2.1 Tablonun varsayılan `SelectFilter` kategori filtresini, Ana Kategori ve Alt Kategori hiyerarşisine duyarlı (Tree select) yapıya çevir.
- [ ] 2.2 Kategoriler arasındaki ilişkiyi hiyerarşik olarak sunabilmek için filtre seçeneklerini yapılandır.
- [ ] 2.3 `getTabs` modülünü ya kategori bazlı filtreyle çakışmayacak şekilde güncelle ya da yalnızca ana kategoriler bazında sadeleştir.

## 3. Inline Stok & Aktiflik Yönetimi

- [ ] 3.1 `store_active` ve `is_featured` toggle sütunlarını tablonun sağında daha hızlı erişilebilir ilk alana taşı.
- [ ] 3.2 Sütun genişliklerini kompakt ayarlara (`width()`) döndürerek yatay ekseni daralt ve ekrana daha çok ürünün sığmasını sağla.
- [ ] 3.3 Sıralama `TextInputColumn` alanının form boyutunu en küçült `extraAttributes` vb ile Padding'lerini yok et.
