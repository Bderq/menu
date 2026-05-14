## Context

QR Menu sistemi şu anda bir `TrackVisitor` middleware aracılığı ile Ziyaretçi (Visitor) ve Oturum (Visit) bilgilerini UUID ve cookie kullanarak kaydediyor. Kullanıcılar (müşteriler) herhangi bir ürüne veya kategoriye tıkladıklarında, bu bir "Interaction" (Etkileşim) olarak kaydediliyor. Ancak, sistemde "Store" (Şube) bazlı takip yapılmadığı için, kaydedilen bir oturumun (Visit) hangi şubede gerçekleştiği bilinmiyor. Bu da analitik panellerinin, Filament widget'larının tek şube ya da global tüm şubeleri karışık şekilde raporlamasına neden oluyor. Üstüne Filament üzerindeki SQL sorguları/Eloquent SubQuery'leri gruplamalar için 30 günlük full table scan tarzı okumalar yapıyor ve eksik index'ler sebebiyle performans riski taşıyor. Hatalı client istekleri de (yanlış `model` payload'ı) DB'ye null/null veri sokabiliyor.

## Goals / Non-Goals

**Goals:**
- `Visit` modelini `Store` modeliyle bağlamak. (Multi-branch analitiğinin temelini atmak)
- TrackingController içerisindeki veri ambarı zafiyetlerini (null data bypass) önlemek.
- Filament Dashboard panellerinin (AnalyticsStats, TopInteractionsTable, TopLikesTable) 24 saat kuralına uymasını sağlamak ve `Store` kolonunu eklemek.
- `interactions` tablosunun aggregation (group by) sorgularında N+1 problemlerini çözmek ve gerekli indexleri (`created_at`, `interactable_type`, `interactable_id`) tabloya kazandırmak.

**Non-Goals:**
- Yepyeni analitik chart tipleri / dashboard sayfaları eklemek (Sadece var olan tablolar düzeltilecek).
- `interactions` tablosundaki eski verilerde yer alan "tekil mağaza tespiti" işlemi yapmak (Geçmiş ziyaret verileri "global" veya null `store_id` olarak kalabilir, backward compatibility bozulmaz).

## Decisions

- **Decision 1: `store_id` Kolonu Nullable**: Migration yazarken nullable bırakılacak. Çünkü geçmiş visit'lerin store'u bilinemiyor. İleriye dönük visit'lerde null olmaması sağlanacak.
- **Decision 2: Store'u Middleware'de Yakalamak**: `MenuController`'da `$store_slug` mevcut. Eğer `TrackVisitor` rotalara bağlı çalışıyorsa, `$request->route('store_slug')` ile alınabilir. Geçerli bir slug varsa, Store ID resolve edilip `Visit` tablosuna eklenecektir.
- **Decision 3: Multi-column Index**: `interactions` tablosunda Filament'in yoğun gruplamaları var. `interactions(created_at, interactable_type, interactable_id)` indexi eklenecek.
- **Decision 4: N+1 Önlemi**: `TopInteractionsTable` içindeki `fromSub` kullanımından sonra model listesinde `TextColumn::make('interactable.name')` çağrılarında performansı kurtarmak için tablonun ana sorgusuna `->with(['interactable.store'])` eager-loading eklenecek.

## Risks / Trade-offs

- **Risk: Eski verilerde Store filtresi çalışmayabilir** 
  - *Mitigation*: AnalyticsDashboard sadece "Son 24 Saat" verisine odaklanacağı için bu transition (geçiş) gün içinde tolere edilecek ve yarına kadar veri tam rayına oturacaktır.
- **Risk: N+1 with SubQueries in Filament** 
  - *Mitigation*: Subquery yapısını bozmadan dışarıdaki query'de eager loading yapmak mümkündür. Ayrıca `group by` sonuç sayısı nispeten (24 saat için en çok popüler 10-25 ürün olacağından) az olacağı için, ilişkili okumalar çok maliyetli olmayacaktır.
