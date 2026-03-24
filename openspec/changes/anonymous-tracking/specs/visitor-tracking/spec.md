## ADDED Requirements

### Requirement: Anonymous Device Identification
Sistem, QR menüye giren her cihazı (tarayıcıyı) üyelik gerektirmeden tekil olarak tanımlamalıdır.

#### Scenario: New Visitor Recognition
- **WHEN** Kullanıcı tarayıcısında `qr_menu_visitor_id` çerezi (cookie) veya LocalStorage kaydı yoksa.
- **THEN** Backend tarafında yeni bir UUID üretilmeli, `visitors` tablosuna kaydedilmeli ve bu ID hem çerez (1 yıl süreli) hem de LocalStorage olarak tarayıcıya atanmalıdır.

#### Scenario: Returning Visitor (Cookie persistence)
- **WHEN** Kullanıcıda kayıtlı bir `qr_menu_visitor_id` varsa.
- **THEN** Mevcut `visitors` kaydı üzerinden `last_seen_at` bilgisi güncellenmeli ve yeni bir ziyaret (Visit) kaydı başlatılmalıdır.

#### Scenario: Identity Recovery (Cookie cleared)
- **WHEN** Kullanıcı çerezleri silmiş ancak LocalStorage kaydı hala mevcutsa.
- **THEN** Frontend script'i LocalStorage'daki UUID'yi okumalı ve backend üzerinden çerezi tekrar oluşturup kimliği geri yüklemelidir.

#### Scenario: Identity Recovery (Fingerprint fallback)
- **WHEN** Hem çerez hem de LocalStorage silinmişse.
- **THEN** JavaScript tarafından üretilen Canvas/WebGL Fingerprint hash'i ile veri tabanında arama yapılmalı ve eşleşme bulunursa kimlik (UUID) geri yüklenmelidir.
