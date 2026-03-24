## Why

Mevcut mağaza bazlı ürün yönetimi sayfasında (`/admin/products/store/{id}`), ürün bulmak ve filtreleme yapmak zor, ayrıca mevcut altkategori yapısına tam entegre değil. Adminlerin bu sayfada en çok yaptığı kritik işlem (stok/aktiflik yönetimi) mevcut tasarımda yeterince hızlı yapılamıyor. Bu değişiklikle, profesyonel kullanıma yönelik, veri girişini ve stok yönetimini hızlandıracak daha sıkışık, çok sütunlu ve tamamen veriye odaklı bir "Data Table" tasarımı (Option A) hedeflenmektedir. Bu sayede adminler çok daha hızlı bir şekilde mağaza envanterini yönetebilecektir.

## What Changes

- **Veri Odaklı Tablo Tasarımı (Data Table):** Görsellerin daha az yer kapladığı, çok sütunlu, kompakt ve hızlı veri girişine uygun bir UI yapısı.
- **Gelişmiş Filtreleme ve Arama:** Üst kategori ve alt kategori ilişkisini doğru kullanan, ürün bulmayı kolaylaştıran gelişmiş filtreleme araçları eklenecek.
- **Hızlı Stok ve Aktiflik Yönetimi:** Tablodan çıkmadan (inline) veya çok daha pratik bir şekilde "stokta var/yok", "aktif/pasif" durumlarının yönetilebileceği UX geliştirmesi.
- **Toplu İşlemler (Bulk Actions):** İhtiyaç halinde seçili ürünlerin stok durumlarını ve fiyatlarını topluca güncelleyebilecek altyapı.

## Capabilities

### New Capabilities
- `store-product-management`: Mağaza bazlı ürün yönetimi, gelişmiş filtreleme ve stok yönetimini içeren kapasite.

### Modified Capabilities
- (Boş - Mevcut yeteneklerin temel gereksinimleri değişmiyor, UI/UX iyileştirmesi yapılıyor.)

## Impact

- `StoreProducts.php` Filament sayfasındaki Tablo ve Filtreleme yapıları güncellenecektir.
- Kategori ve Alt Kategori ilişkilerini daha iyi yansıtacak şekilde Filament tablosundaki query modifiye edilecek.
- Inline düzenleme (Inline Editing) veya toggle bileşenleri (stok/aktiflik) tabloda daha kompakt ve hızlı erişilebilir hale getirilecek.
