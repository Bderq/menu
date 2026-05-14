## ADDED Requirements

### Requirement: Referrer Tracking
Sistem, bir oturum (Visit) başladığında kullanıcının hangi web sitesinden yönlendiğini otomatik olarak kaydetmelidir.

#### Scenario: Instagram Üzerinden Giriş
- **WHEN** Müşteri Instagram bio linkinden menüye tıkladığında.
- **THEN** `visits` tablosundaki `referer_host` kolnu `instagram.com` (veya l.instagram.com) olarak kaydedilmelidir.

### Requirement: UTM Parameter Capture
URL'de bulunan `utm_source` parametresi, kampanya/kaynak takibi için kaydedilmelidir.

#### Scenario: UTM Parametreli QR Okutma
- **WHEN** Kullanıcı `?utm_source=masa_qr` içeren bir URL ile giriş yaptığında.
- **THEN** `visits` tablosundaki `utm_source` kolonu `masa_qr` değerini almalıdır.
