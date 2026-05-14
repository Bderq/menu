## ADDED Requirements

### Requirement: Kısa mesaj denemesi client-side'da engellenir
Kullanıcı `Ses Ver` formuna 10 karakterden az içerik girip formu göndermek istediğinde, sistem backend'e istek göndermeden kullanıcıyı görsel olarak uyarmalıdır.

#### Scenario: Kullanıcı çok kısa mesaj göndermeye çalışır
- **WHEN** kullanıcı 10 karakterden az metin girip "Yolla Gelsin" butonuna basar
- **THEN** textarea shake (titreşim) animasyonu oynar, buton 2 saniye boyunca "EN AZ 10 HARF YAZ!" metnini kırmızı arka planla gösterir ve backend'e hiçbir istek gönderilmez

#### Scenario: 2 saniye sonra form normale döner
- **WHEN** kısa mesaj uyarısı tetiklendikten 2 saniye sonra
- **THEN** feedbackStatus `idle`'a döner, buton orijinal "Yolla Gelsin" görünümüne kavuşur ve textarea tekrar kullanılabilir hale gelir

#### Scenario: Yeterli karakter yazıldıktan sonra gönderim başarılı olur
- **WHEN** kullanıcı 10 veya daha fazla karakter girip butona basar
- **THEN** sistem normal gönderim akışını (API çağrısı → 201 → "İletildi!") yürütür
