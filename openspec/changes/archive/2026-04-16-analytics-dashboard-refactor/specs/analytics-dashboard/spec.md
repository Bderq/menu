## ADDED Requirements

### Requirement: 24 Saat ve Mağaza Kısıtı
Filament panelleri (AnalyticsStats, TopInteractionsTable, TopLikesTable vb.) zaman filtresi olarak her defasında 30 gün değil, sadece hedef analitiğe uygun olacak şekilde "24 saat" ile kısıtlandırılmalı, veriler taranırken mağaza ayrımı (Store name) gösterge paneline yansıtılmalıdır.

#### Scenario: Most Popular Products Dashboard View
- **WHEN** Admin kullanıcısı Analytics panelini açtığında.
- **THEN** Sadece son 24 saate ait "Top Interactions" gösterilmeli ve tabloda "Store" sütunu bulunmalıdır.

#### Scenario: Top Likes Dashboard View
- **WHEN** Admin kullanıcısı Analytics panelini açtığında.
- **THEN** En çok beğeni alan ürünler tablosu, ürünün bilgisinin yanı sıra bağlı bulunduğu "Store" bilgisiyle birlikte dönmeli veya analiz edilmelidir.
