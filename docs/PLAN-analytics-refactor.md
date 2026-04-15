# PLAN: Analytics Dashboard Refactor

## Context
Kullanıcının QR Menu projesinde bulunan "Analytics Dashboard" ve backend "Tracking" sisteminin hatalarını, eksiklerini ve performans sorunlarını giderme planı. Daha önce tespit edilen 4 temel eksikliği kapsamaktadır: `store_id` eksikliği, spec ile uyuşmazlıklar, Controller güvenlik zaafları ve N+1/Index/Performans darboğazları.

## Phase 1: Veritabanı ve Migration (Store Entegrasyonu)
*   **Görev 1:** `visits` tablosuna `store_id` ForeignKey eklenmesi için bir migration oluşturulması. (Nullable yapılabilir, geçmiş verilerin etkilenmemesi için.)
*   **Görev 2:** `interactions` tablosuna performans için index ekleyen yeni bir migration oluşturulması. (Örn: `INDEX(created_at, interactable_type, interactable_id)`)

## Phase 2: Backend Logic Güncellemeleri
*   **Görev 3:** `TrackVisitor` middleware'inin ve/veya sistemin `visit` oturumu oluştururken `store_id` bilgisini de kaydetmesinin sağlanması. (Route parametresinden `$store_slug` çekilerek veya header/payload üzerinden).
*   **Görev 4:** `TrackingController@hit` metoduna validasyon (guard) eklenmesi. Bilinmeyen bir `model` gönderildiğinde 400 Bad Request dönülmesi.

## Phase 3: Filament Dashboard Düzeltmeleri (Spec Uyum)
*   **Görev 5:** `AnalyticsStats` Widget'ının veya Dashboard'un sayfasının "Store" (Şube) seçeneğini içerecek şekilde düzenlenmesi veya varsayılan global bakışta tüm şubelerin ayrımının yapılması.
*   **Görev 6:** `TopInteractionsTable` bileşeninin güncellenmesi.
    *   30 Gün yerine "Son 24 Saat" tabanlı filtre kurulması.
    *   N+1 Probleminin giderilmesi (Alt sorguya `with('interactable')` eklenmesi eksikse veya ilişkilerin daha performanslı çözülmesi).
    *   Kolonların arasına "Store" bilgisinin eklenmesi (Product'ın bağlı olduğu mağaza).
*   **Görev 7:** `TopLikesTable` bileşeninin güncellenmesi.
    *   "Tüm Zamanlar" yerine "Son 24 Saat" kısıtlamasının ya da tarihe göre filtrenin eklenmesi.

## Verification Checklist
- [ ] Yeni visit kayıtlarında `store_id` sütunu veritabanında başarılı bir şekilde doluyor mu?
- [ ] Yanlış/boş bir obje için (Örnek: `model: 'Hacker'`) tracking isteği atıldığında sunucu hata fırlatıyor mu?
- [ ] Filament "Top Interactions" ve "Top Likes" tabloları ilgili veriyi sadece 24 saat içinde gerçekleşecek şekilde listeliyor mu?
- [ ] Filament panellerinde "Store" ayrımı yapılabiliyor mu?

## Agent Görev Dağılımı
- `@backend-specialist`: Migration yazımı, Middleware entegrasyonu ve Validation güncellemeleri.
- `@frontend-specialist`: Filament Page / Widget güncellemeleri, kolon ve filtre eklentileri (Filament PHP kullanmasına rağmen UI Dashboard mantığı olduğu için).
