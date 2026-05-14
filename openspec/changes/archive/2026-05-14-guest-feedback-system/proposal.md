## Why

Kullanıcılar QR menü üzerinden mekan yönetimiyle iletişim kuramıyor. Dilek, istek ve şikayet bildirimi için harici bir kanal gerekiyor. "Ses Ver" sekmesi UI olarak hazır fakat backend entegrasyonu eksik — bu değişiklik sistemi çalışır hale getirir.

## What Changes

- Yeni `guest_messages` veritabanı tablosu eklenir (store, IP, mesaj içeriği, okundu durumu)
- `GuestMessage` Eloquent model oluşturulur
- `POST /api/{store_slug}/message` endpoint'i eklenir (günde 2 mesaj rate limit — IP bazlı)
- `MenuInteractionDrawer.jsx` içindeki simüle API çağrısı gerçek endpoint'e bağlanır
- Filament admin panelinde `GuestMessagesResource` oluşturulur (okuma, işaretleme, filtreleme)

## Capabilities

### New Capabilities

- `guest-message-submission`: Müşterilerin store'a anonim mesaj göndermesi (rate limit dahil)
- `guest-message-management`: Admin panelinde gelen mesajları görüntüleme ve yönetme

### Modified Capabilities

_(Mevcut spec'lerde gereksinim seviyesinde değişiklik yok)_

## Impact

- **Yeni dosyalar:** migration, `GuestMessage.php` model, `GuestMessageController.php`, `GuestMessagesResource.php` (Filament)
- **Değiştirilen dosyalar:** `routes/web.php`, `MenuInteractionDrawer.jsx`
- **Veritabanı:** Yeni `guest_messages` tablosu
- **Rate Limiting:** Laravel'in `throttle` middleware'i IP bazlı (günde 2 istek)
- **Bağımlılık yok:** Spotify, kampanya veya ziyaretçi sistemleriyle çakışma yok
