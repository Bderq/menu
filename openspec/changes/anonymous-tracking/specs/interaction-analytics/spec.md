## ADDED Requirements

### Requirement: Interaction Logging (Views and Clicks)
Sistem, kullanıcıların menü içindeki etkileşimlerini (tıklama, görüntüleme) gerçek zamanlı loglamalıdır.

#### Scenario: Product Click Tracking
- **WHEN** Kullanıcı menüdeki bir ürün kartına tıkladığında.
- **THEN** Frontend tarafı Inertia/React üzerinden asenkron (asynchronous) bir `hit` isteği göndermeli ve bu istek veri tabanındaki `interactions` tablosuna `click` tipinde kaydedilmelidir.

#### Scenario: Category View & Dwell Time
- **WHEN** Kullanıcı bir kategori sekmesini açtığında ve o kategoride vakit geçirdiğinde.
- **THEN** Her 30 saniyede bir `heartbeat` sinyali gönderilmeli ve o kategorinin görüntülenme süresi (duration) kaydedilmelidir.

#### Scenario: Visit Session Boundary
- **WHEN** Kullanıcı menüden ayrıldıktan sonra veya 30 dakika boyunda sinyal göndermediğinde.
- **THEN** Otomatik olarak mevcut ziyaret (Visit) kaydı kapatılmalı ve bir sonraki girişte yeni bir ziyaret başlatılmalıdır.
