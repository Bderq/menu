## ADDED Requirements

### Requirement: Multi-Portion Seeding
Ürün porsiyonları artık seeder verisi üzerinden dinamik olarak isimlendirilebilir ve fiyatlandırılabilir olmalıdır.

#### Scenario: Ürün tek fiyatlı ise
- **WHEN** Ürün verisinde sadece `price` anahtarı varsa
- **THEN** "Standart" adında tek bir porsiyon kaydı oluşturulur

#### Scenario: Ürün çoklu fiyatlı ise
- **WHEN** Ürün verisinde `portions` dizisi (isim ve fiyat) varsa
- **THEN** Dizideki her bir kalem için ayrı `store_product_portions` kaydı oluşturulur

### Requirement: Store Restriction
Seeder sadece belirlenen şube (Görükle) için döküman üretmelidir.

#### Scenario: Şube seçimi
- **WHEN** Seeder çalıştırıldığında
- **THEN** Sadece `slug: gorukle` olan şubeye ürünler bağlanır ve porsiyonları atanır

### Requirement: No Campaigns
Yeni menü yapılandırmasında kampanya verileri bulunmamalıdır.

#### Scenario: Kampanya filtresi
- **WHEN** Seeder verileri okunurken
- **THEN** Kampanya kategorileri ve porsiyonları işlenmez
