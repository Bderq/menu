## ADDED Requirements

### Requirement: Visit Store Association
Sistem, QR Menu üzerinden menüye erişen Ziyaretçilerin oluşturduğu oturum (Visit) kayıtlarını, gezindikleri "Store" kimliğiyle (`store_id`) ilişkilendirmelidir.

#### Scenario: Oturum Başlaması ve Şubenin Kaydedilmesi
- **WHEN** Müşteri URL üzerinden belirli bir şubenin (`/{store_slug}`) menüsüne giriş yaptığında.
- **THEN** `TrackVisitor` (veya controller), oluşan veya güncellenen `Visit` objesine o şubeye karşılık gelen `store_id` bilgisini de kaydetmelidir.

### Requirement: Hatalı Tracking İsteği Koruması
TrackingController `hit` metodu işlenemeyecek (`Product` ve `Category` harici) model isteklerini engellemelidir.

#### Scenario: Geçersiz Model ile Hit Atılması
- **WHEN** Frontend veya bir bot, `model = 'Cart'` veya geçersiz bir string ile `hit` isteği attığında.
- **THEN** Sistem `interactable_type`'ı `null` olarak kaydetmek yerine HTTP 400 Bad Request döndürmeli ve işlemi reddetmelidir.
