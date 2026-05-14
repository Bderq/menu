## 1. Veritabanı

- [ ] 1.1 `create_guest_messages_table` migration'ı yaz: `store_id (FK)`, `ip_address (varchar 45)`, `content (text)`, `is_read (boolean, default false)`, `read_at (timestamp nullable)`, `timestamps`
- [ ] 1.2 `php artisan migrate` ile tabloyu oluştur

## 2. Model

- [ ] 2.1 `app/Models/GuestMessage.php` oluştur: `fillable`, `casts` (`is_read` → boolean, `read_at` → datetime), `store()` ilişkisi
- [ ] 2.2 `Store` modeline `guestMessages()` hasMany ilişkisini ekle

## 3. Controller & Route

- [ ] 3.1 `app/Http/Controllers/GuestMessageController.php` oluştur — `store()` method: validation (content min:10, max:1000), store_slug → store bul, kaydet, 201 dön
- [ ] 3.2 `routes/web.php`'ye rate limit tanımla: `RateLimiter::for('guest-message', ...)` — IP + store bazlı günde 2 istek
- [ ] 3.3 `routes/web.php`'ye `POST /api/{store_slug}/message` endpoint'ini ekle — throttle middleware ile

## 4. Frontend Entegrasyonu

- [ ] 4.1 `MenuInteractionDrawer.jsx`'te `handleFeedbackSubmit` fonksiyonunu gerçek API çağrısı yapacak şekilde güncelle (`fetch POST /api/{storeSlug}/message`)
- [ ] 4.2 429 (rate limit) durumunda `feedbackStatus = 'limited'` state'i ve "Bugün için limitine ulaştın." mesajı ekle
- [ ] 4.3 Network hatalarında `feedbackStatus = 'error'` state'i ve "Bir hata oluştu, tekrar dene." mesajı ekle

## 5. Filament Admin Paneli

- [ ] 5.1 `app/Filament/Resources/GuestMessagesResource.php` oluştur: table columns (store adı, IP, içerik önizleme, okundu, tarih), store filtresi, okunmamış filtresi
- [ ] 5.2 "Okundu İşaretle" action ekle: `is_read = true`, `read_at = now()` yazar, zaten okunmuşsa devre dışı
- [ ] 5.3 Filament navigasyon grubuna ekle (örn. "Müşteri Geri Bildirimleri" veya mevcut gruba)

## 6. Doğrulama

- [ ] 6.1 Geçerli mesaj gönderimi → 201 yanıtı ve DB kaydı kontrol et
- [ ] 6.2 Kısa/uzun mesaj → 422 yanıtı kontrol et
- [ ] 6.3 3. mesaj denemesinde → 429 yanıtı kontrol et
- [ ] 6.4 Frontend'de başarı, limit ve hata durumlarını manuel test et
- [ ] 6.5 Filament'te mesaj listesi, filtre ve okundu işaretleme kontrol et
