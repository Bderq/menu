## Why
Sistemdeki Analytics altyapısı (Visitor, Visit, Interaction modelleri ve Filament paneli) şu anda tek şubeli varsayımı üzerinden çalışıyor. `Visit` verilerinde `store_id` (şube/mağaza kimliği) eksik. Además, Filament Dashboard içerisindeki widget'lar (TopInteractionsTable, vs.) 30 günlük global verileri çekerek "24 saat ve mağaza spesifik" tasarım kararlarına uymuyor. Ayrıca N+1 ve index eksiklikleri yüzünden performans ve ölçeklenme sorunları yaratıyor. Canlı yapıyı bozmadan bu düzeltmeleri hayata geçirerek analitik işlevlerini sağlamlaştırmayı amaçlıyoruz.

## What Changes
- `visits` tablosuna nullable `store_id` ForeignKey eklenecek.
- `TrackVisitor` middleware'inde veya MenuController/TrackingController üzerinden `store_id` tespit edilerek visit kayıtlarına eklenecek.
- `TrackingController@hit` metoduna invalid model engeli eklenecek.
- Filament Analitik panelindeki widget'lar 24 saat kuralı ve şube bazlı metrik ayrımına tabi tutulacak.
- `interactions` tablosunda ilgili sütunlara `created_at, interactable_type, interactable_id` tabanlı performans optimizasyonu/index eklenecek.

## Capabilities

### Modified Capabilities
- `analytics-dashboard`: Analitik paneli 24 saat ile sınırlandırılacak. Ürün listelemelerinde "Store" (Şube) görünecek.
- `anonymous-tracking`: Ziyaretler (Visits) hangi store'a ait olduğuna dair `store_id` bilgisiyle kaydedilecek. Hatalı obje tipleri blocklanacak.

## Impact
- `visits` tablosu migration (add store_id).
- `interactions` tablosu migration (add index).
- `TrackingController`, `TrackVisitor` logic iyileştirmesi.
- `App\Filament\Widgets\*\*` içindeki analitik sorguları.
