## ADDED Requirements

### Requirement: Admin mesajları listeler
Filament admin panelinde `GuestMessagesResource` üzerinden tüm store mesajları listelenir. Store adı, IP adresi, mesaj içeriği, okundu durumu ve gönderim zamanı görüntülenir.

#### Scenario: Admin mesaj listesini açar
- **WHEN** admin Filament'te "Müşteri Mesajları" menüsüne gider
- **THEN** tüm mesajlar tablo halinde listelenir (en yeni üstte)

#### Scenario: Admin store'a göre filtreler
- **WHEN** admin dropdown'dan bir store seçer
- **THEN** yalnızca o store'a ait mesajlar listelenir

---

### Requirement: Admin mesajı okundu olarak işaretler
Admin her mesajı tek tek "Okundu" olarak işaretleyebilir. İşaretlenen mesajlarda `is_read = true`, `read_at = now()` yazılır.

#### Scenario: Okunmamış mesajı işaretleme
- **WHEN** admin bir mesajın yanındaki "Okundu İşaretle" butonuna tıklar
- **THEN** `is_read` true olur, satır rengi değişir (görsel ayrım)

#### Scenario: Zaten okunmuş mesaj
- **WHEN** mesaj zaten okundu olarak işaretliyse
- **THEN** tekrar işaretlenemez (buton devre dışı veya gizli)

---

### Requirement: Admin okunmamış mesajları filtreler
Admin panelinde sadece okunmamış mesajları görmek için filtre uygulanabilir.

#### Scenario: "Okunmamış" filtresi aktif
- **WHEN** admin "Sadece Okunmamış" filtresini seçer
- **THEN** yalnızca `is_read = false` olan mesajlar listelenir
