## Context

QR menüde "Ses Ver" sekmesi (MenuInteractionDrawer.jsx) UI olarak mevcuttur. Form gönderimi şu an `setTimeout` ile simüle edilmektedir — gerçek bir API çağrısı yapılmamaktadır. Proje Laravel 12 + Inertia.js + React 19 kullanmaktadır. Mevcut anonimlik altyapısı (Visitor modeli, TrackVisitor middleware, IP bazlı parmak izi) bu özellikte referans alınabilir.

## Goals / Non-Goals

**Goals:**
- Müşterilerin store bazlı anonim mesaj göndermesi
- IP bazlı günde 2 mesaj rate limit uygulanması
- Admin panelinde (Filament) mesajları listeleme, filtreleme, okundu olarak işaretleme
- Frontend'de gerçek API bağlantısı (hata ve rate limit geri bildirimleri dahil)

**Non-Goals:**
- Kullanıcı kimlik doğrulaması (anonim sistem)
- E-posta bildirimi (ileride eklenebilir)
- Mesajlara admin tarafından yanıt verme
- Anket (survey) sekmesinin backend'i

## Decisions

### 1. Veri Modeli: `guest_messages` tablosu

`visitors` tablosuna bağlamak yerine bağımsız bir tablo tercih edildi.

**Neden:** Ziyaretçi takibi ile mesaj sistemi farklı amaçlara hizmet eder; birleştirmek her ikisini de karmaşıklaştırır. Bağımsız tablo daha temiz sınır çizer.

| Alan | Tip | Açıklama |
|------|-----|----------|
| `id` | bigint PK | |
| `store_id` | FK → stores | Hangi şubeye ait |
| `ip_address` | varchar(45) | Rate limit için |
| `content` | text | Mesaj içeriği |
| `is_read` | boolean | Admin takibi |
| `read_at` | timestamp, null | Okunma zamanı |
| `timestamps` | | created_at, updated_at |

### 2. Rate Limiting: Laravel Throttle Middleware

Laravel'in `RateLimiter` facade'i ile `guest-message:{ip}:{store_slug}` anahtarı kullanılarak günde 2 istek sınırı uygulanır.

**Neden:** Filament rate limiter ya da veritabanı sorgusu yerine cache tabanlı throttle tercih edildi — daha hızlı ve framework-native.

### 3. Controller: Ayrı GuestMessageController

TrackingController'a eklemek yerine bağımsız controller oluşturulur.

**Neden:** Sorumluluk ayrımı. TrackingController zaten farklı bir domain'e hizmet ediyor.

### 4. Frontend Hata Yönetimi

Rate limit (HTTP 429) durumunda kullanıcıya Türkçe geri bildirim gösterilir: "Bugün için limitine ulaştın."

## Risks / Trade-offs

- **IP Spoofing** → Orta risk. Proxy arkasındaki kullanıcılar aynı IP'yi paylaşabilir. `REMOTE_ADDR` yerine `X-Forwarded-For` ile de kontrol sağlanabilir, ancak bu MVP için kabul edilebilir.
- **Cache temizlenirse limit sıfırlanır** → Düşük risk. Redis yerine database cache kullanan ortamlarda bu olabilir; günlük limit düşük olduğu için etkisi minimaldir.
- **Spam içerik** → Minimum metin uzunluğu (10 karakter) ve maksimum (1000 karakter) zorunlu kılınır.
