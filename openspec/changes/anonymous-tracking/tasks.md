## 1. Veri Tabanı ve Model Hazırlığı (PHASE 1)

- [x] 1.1 `visitors` tablosu için migration ve model oluşturulması (`uuid`, `fingerprint_hash`, `ip_address`, `user_agent`, `last_seen_at`).
- [x] 1.2 `visits` tablosu için migration ve model oluşturulması (`visitor_id`, `started_at`, `ended_at`).
- [x] 1.3 `interactions` tablosu için migration ve model oluşturulması (`visit_id`, `interactable_type`, `interactable_id`, `type`, `duration_seconds`).
- [x] 1.4 Modeller arası ilişkilerin (Visitor -> Visits -> Interactions) Eloquent ile tanımlanması.

## 2. Backend Kimlik ve Oturum Logic'i (PHASE 2)

- [x] 2.1 `TrackVisitor` Middleware oluşturulması (Cookie/UUID atama ve Visitor güncelleme).
- [x] 2.2 Middleware'in `bootstrap/app.php` altında frontend rotaları için kaydedilmesi.
- [x] 2.3 `POST /api/tracking/hit` endpoint'i ve Controller metodunun oluşturulması (Interactable log kaydı).
- [x] 2.4 Fingerprint verisini alıp `Visitor` kaydıyla eşleştiren backend logic'inin yazılması.

## 3. Frontend Takip ve React Entegrasyonu (PHASE 3)

- [x] 3.1 `resources/js/Pages/Menu/Index.jsx` içinde `TrackVisitor` bileşeni veya hook'u entegrasyonu.
- [x] 3.2 Lightweight Canvas Fingerprint fonksiyonunun JS tarafında yazılması.
- [x] 3.3 Her 30 saniyede bir `heartbeat` gönderen ve kategori değişimlerini izleyen `activeCategoryTime` mekanizması.
- [x] 3.4 Ürün kartlarına tıklandığında `axios` ile backend'e `click` logu gönderilmesi.

## 4. Filament Analiz Dashboard'u (PHASE 4)

- [x] 4.1 Filament üzerinde `AnalyticsDashboard` sayfası oluşturulması.
- [x] 4.2 "Unique Visitors" ve "Daily Sessions" için Stats Widget'lar.
- [x] 4.3 En çok tıklanan ürünler ve en çok vakit geçirilen kategoriler için Tablo/Grafik Widget'lar.
- [x] 4.4 `analytics:prune` Artisan command'i (90 günden eski verileri temizleme).
