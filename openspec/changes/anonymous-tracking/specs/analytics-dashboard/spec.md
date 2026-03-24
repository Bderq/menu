## ADDED Requirements

### Requirement: Filament Analytics UI
Sistem, işletme sahiplerinin (adminlerin) toplanan verileri bir özet tablo ve istatistiklerle görmesini sağlamalıdır.

#### Scenario: Most Popular Products Dashboard View
- **WHEN** Admin kullanıcısı Filament panelindeki 'Analytics' sayfasını açtığında.
- **THEN** Son 24 saat içinde en çok tıklanan ilk 10 ürünün (Product name, Click count, Store) listesi görüntülenmelidir.

#### Scenario: Unique Visitor Count Widget
- **WHEN** Admin kullanıcısı Analytics panelini görüntülediğinde.
- **THEN** Son 24 saat içindeki "Tekil Ziyaretçi" (Unique Visitor) sayısı ve toplam "Oturum" (Session) sayısı ayrı bir özet kartında (Widget) yer almalıdır.

#### Scenario: Category Dwell Time Distribution
- **WHEN** Admin Analytics sayfasında kategoriler özetine baktığında.
- **THEN** Her bir kategorinin toplamda kaç saniye/dakika görüntülendiği verisi tabloda yer almalıdır.
