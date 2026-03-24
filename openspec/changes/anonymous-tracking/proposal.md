## Why

QR Menü kullanıcılarının menü içindeki davranışlarını (hangi ürünlerin ilgi çektiği, hangi kategorilerde ne kadar vakit geçirildiği vb.) analiz etmek, mönü optimizasyonu ve satış artırımı için kritik öneme sahiptir. Mevcut sistemde kullanıcı etkileşimlerine dair bir veri toplanmamaktadır. Ayrıca, ileride eklenebilecek anket/oylama sistemleri için cihazları çerezler silinse dahi tanıyabilecek sağlam bir anonim kimlik doğrulama altyapısına ihtiyaç vardır.

## What Changes

Bu değişiklik ile menüye giren her kullanıcı için benzersiz bir cihaz kimliği (UUID + Fingerprint) oluşturulacak ve bu kimlik üzerinden tüm etkileşimler kaydedilecektir. Kaydedilen veriler (tıklamalar, görüntüleme süreleri) Filament admin paneli üzerinden takip edilebilir hale getirilecektir.

Temel bileşenler:
- `Visitor`, `Visit` ve `Interaction` modelleri ve ilgili veri tabanı tabloları.
- Cihaz tanıma ve oturum yönetimi için Laravel Middleware.
- Cihaz parmak izi (Fingerprint) ve etkileşim takibi için frontend JavaScript katmanı.
- Filament üzerinde raporlama ekranı.

## Capabilities

### New Capabilities
- `visitor-tracking`: UUID, LocalStorage ve Gelişmiş Browser Fingerprinting (Canvas/GPU) kullanarak cihaz bazlı anonim kimlik tanıma.
- `interaction-analytics`: Ürün tıklamaları, kategori görüntülemeleri ve sayfada kalma sürelerinin (Dwell Time) takibi.
- `analytics-dashboard`: Toplanan verilerin Filament admin panelinde özet tablolar ve widget'lar ile raporlanması.

### Modified Capabilities
- (Hiçbir mevcut gereksinim değişmiyor, sadece yeni bir izleme katmanı ekleniyor.)

## Impact

- **Database**: 3 yeni tablo eklenecek.
- **Backend**: Frontend rotalarına yeni bir middleware eklenecek.
- **Frontend**: Menü sayfasına hafif bir takip script'i (JS) eklenecek.
- **Performance**: Yoğun trafik altında veri tabanı yükünü minimize etmek için hafif loglama mimarisi kullanılacak.
