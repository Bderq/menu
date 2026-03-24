## Context

QR Menü sistemi Laravel + Inertia + React + Filament mimarisi üzerine kuruludur. Mevcut sistemde `/menu/{store_slug}` rotası üzerinden erişilen menüde kullanıcıların hangi ürünlere tıkladığı veya kategorilerde ne kadar zaman geçirdiği takip edilmemektedir. Cihazları anonim olarak tanımak ve birer "Visitor" (Ziyaretçi) olarak kaydetmek, hem analiz hem de ileride planlanan anket sistemi için gereklidir.

## Goals / Non-Goals

**Goals:**
- Her cihaza benzersiz bir kimlik (UUID) atamak ve çerezler silinse bile "parmak izi" (fingerprint) ile tanımaya devam etmek.
- Ürün tıklamaları ve kategori görüntüleme verilerini en az %95 doğrulukla toplamak.
- Kullanıcıların kategorilerde geçirdiği süreyi (dwell time) ölçmek.
- Filament admin panelinde işletme sahiplerinin görebileceği basit bir analiz tablosu sunmak.

**Non-Goals:**
- Kişisel verilerin (ad, soyad, e-posta) toplanması (bu sistem tamamen anonimdir).
- Üyelik sistemi entegrasyonu.
- Gerçek zamanlı trafik monitörü (canlı dashboard şu an kapsam dışıdır).

## Decisions

- **Identification Stratejisi**: Browser çerezi (Cookie) ana yöntem, LocalStorage yedek yöntem ve Canvas/WebGL tabanlı donanım hash'i (Fingerprint) ise "çerez temizleme" durumunda kullanılan son çare olacaktır.
- **Tracking Middleware**: Tüm frontend isteklerini karşılayan bir `TrackVisitor` ara yazılımı, `visitor_uuid` çerezini kontrol edecek ve yoksa oluşturacaktır.
- **Inertia/React Entegrasyonu**: Etkileşim takibi için frontend tarafında React component lifecycle (`useEffect`) ve `trackInteraction` fonksiyonları kullanılacaktır.
- **API tabanlı Loglama**: Tıklamalar ve görüntüleme süreleri `POST /api/tracking/hit` endpointine gönderilerek asenkron (async) olarak kaydedilecektir.
- **Heartbeat Mekanizması**: Kullanıcının aktifliğini ve sayfada kalma süresini ölçmek için 30 saniyede bir backend'e "buradayım" sinyali (heartbeat) gönderilecektir.

## Risks / Trade-offs

- **Gizlilik (KVKK)**: Sistem tamamen anonim olduğu için "zorunlu çerez" (Strictly Necessary) kategorisinde değerlendirilebilir, ancak yine de sadece fonksiyonel veri toplandığı belirtilmelidir.
- **Veri Boyutu**: Günde 400 ziyaretçi ve her birinden gelen onlarca log, `interactions` tablosunun hızla büyümesine neden olacaktır. Bu yüzden 30 gün sonra eski logların temizlenmesi veya arşivlenmesi politikası uygulanmalıdır.
- **Performans**: Her tıklamanın veri tabanına yazılması IO maliyeti yaratabilir. Yük arttığında Laravel `Queue` yapısına geçiş planlanmalıdır.
