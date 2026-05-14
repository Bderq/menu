## Why
Ziyaretçilerin QR Menüye hangi mecralardan (Instagram, Google, Eski Domain Yönlendirmesi vb.) geldiğini takip etmek, pazarlama bütçesi ve şube trafiği analizi için hayati önem taşımaktadır. Halihazırda ziyaretler (visits) kaydedilse de kaynağa ait veri tutulmamaktadır.

## What Changes
- `visits` tablosuna `referer_host` ve `utm_source` kolonları eklenecek.
- `TrackVisitor` middleware'i gelen HTTP Referer ve URL parametrelerini ayrıştırıp kaydedecek şekilde güncellenecek.
- Filament Analitik sayfasına trafik kaynaklarını gösteren bir tablo/widget bileşeni eklenecek.

## Capabilities

### Modified Capabilities
- `anonymous-tracking`: Ziyaret kayıtlarına (Visit) referans kaynağı ve UTM parametreleri eklenerek izleme derinleştirilecek.
- `analytics-dashboard`: Trafik kaynaklarını gösteren yeni bir istatistik görünümü eklenecek.

## Impact
- `visits` tablosu migration.
- `TrackVisitor` middleware logic.
- Filament Dashboard (Analytics) yeni widget.
