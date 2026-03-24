## 1. MenuSeeder Mimari Güncellemesi

- [ ] 1.1 `MenuSeeder.php` içerisindeki `$menuTree` yapısını `price` veya `portions` anahtarlarını destekleyecek şekilde genişlet.
- [ ] 1.2 `processMenuTree` metodunu çoklu porsiyonları (Portion Name ve Price) `StoreProductPortion` tablosunda oluşturacak şekilde güncelle.
- [ ] 1.3 `$allStores` dizisini sadece `gorukle` şubesini içerecek şekilde sınırla.

## 2. Veri Güncelleme ve Temizlik

- [ ] 2.1 `extracted_menu_data.md` dosyasındaki ürünleri, porsiyonlarıyla birlikte seeder'a aktar.
- [ ] 2.2 Kampanya (Campaign) kategorilerini ve seeder çağrılarını tamamen kaldır.
- [ ] 2.3 Resim eşleştirmeleri için slug mantığının doğru çalıştığını doğrula (Eşlikçiler ve İçecekler için slug kontrolü).

## 3. Dağıtım ve Doğrulama

- [ ] 3.1 `php artisan migrate:fresh --seed --force` komutunu çalıştır (Dikkat: Mevcut verileri temizler).
- [ ] 3.2 `http://45.43.152.119:8080/admin` sayfasında "Görükle" şubesi altında çoklu fiyatlı ürünlerin görünürlüğünü doğrula.
- [ ] 3.3 Menü sayfasında (`/gorukle`) porsiyon seçiminin doğru çalıştığını kontrol et.
