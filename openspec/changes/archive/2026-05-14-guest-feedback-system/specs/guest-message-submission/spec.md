## ADDED Requirements

### Requirement: Guest submits a message to a store
Bir müşteri, `POST /api/{store_slug}/message` endpoint'ine istek atarak o store'a anonim bir mesaj gönderebilir. Mesaj içeriği en az 10, en fazla 1000 karakter olmalıdır. Sistem isteği yapan IP adresi ile store'u ilişkilendirir.

#### Scenario: Başarılı mesaj gönderimi
- **WHEN** geçerli bir store_slug ile 10–1000 karakter arası `content` gönderilir
- **THEN** sistem 201 döner ve mesaj `guest_messages` tablosuna kaydedilir

#### Scenario: Mesaj çok kısa
- **WHEN** content 10 karakterden az gönderilir
- **THEN** sistem 422 döner, validation hatası içerir

#### Scenario: Mesaj çok uzun
- **WHEN** content 1000 karakterden fazla gönderilir
- **THEN** sistem 422 döner, validation hatası içerir

#### Scenario: Geçersiz store_slug
- **WHEN** var olmayan bir store_slug kullanılır
- **THEN** sistem 404 döner

---

### Requirement: Günlük mesaj limiti uygulanır
Aynı IP adresinden aynı store'a günde en fazla 2 mesaj gönderilebilir. Limit aşıldığında kullanıcı bilgilendirilir.

#### Scenario: İlk ve ikinci mesaj başarılı
- **WHEN** aynı IP, aynı store'a günde 1. veya 2. mesajını gönderir
- **THEN** sistem 201 döner ve mesaj kaydedilir

#### Scenario: Günlük limit aşılmış
- **WHEN** aynı IP, aynı store'a günde 3. mesajını gönderir
- **THEN** sistem 429 (Too Many Requests) döner, `message` alanında Türkçe açıklama içerir

---

### Requirement: Frontend gerçek API'ye bağlanır
`MenuInteractionDrawer.jsx` içindeki simüle gönderim gerçek API endpoint'ine bağlanır. Başarı, hata ve rate limit durumları kullanıcıya gösterilir.

#### Scenario: Başarılı gönderim sonrası feedback
- **WHEN** kullanıcı mesajı gönderir ve API 201 döner
- **THEN** form temizlenir, "İletildi!" durumu gösterilir

#### Scenario: Rate limit UI geri bildirimi
- **WHEN** API 429 döner
- **THEN** "Bugün için limitine ulaştın." mesajı gösterilir, form submit edilemez
