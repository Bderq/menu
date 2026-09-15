# PLAN: Analytics Hardening (Gürültü Temizliği, İndeks, Prune, Tarih Filtresi)

## Context
15 Eylül 2026 incelemesinde analitik sisteminin şu sorunları tespit edildi:
- Bot ve tarayıcı istekleri (`/.env`, `/phpinfo.php`, curl, HealthCheck) `TrackVisitor`
  middleware'inden geçip ziyaretçi + ziyaret kaydı açıyor. Son 7 günde 6.113 ziyaretin
  1.890'ı şubesiz, 1.850'sinde hiç etkileşim yok. Panel kartları 4–5 kat şişik.
- `analytics:prune` komutu var ama hiç zamanlanmamış; `visitors` tablosunu temizlemiyor.
  94.894 ziyaretçinin 75.981'i hiçbir ziyarete bağlı değil.
- `visits` tablosunda yalnızca PK indeksi var; `interactions.visit_id` indekssiz.
  Middleware her heartbeat'te (10 sn) `visits` üzerinde sıralı tarama yapıyor.
- Panel sabit "son 24 saat" bakıyor; tarih aralığı ve trend yok. Ortalama süre kartı
  bot ziyaretlerine bölündüğü için yanıltıcı.

Bu plan `PLAN-analytics-refactor.md`'nin devamıdır; oradaki Phase 1–3 tamamlanmış kabul edilir.

Kapsam dışı: ürün detayı için `view`/süre olayı (ayrı plan), parmak izi algoritması.

---

## Phase 1: Gürültü Temizliği (Middleware + Validation)

### Görev 1.1 — Bilinmeyen slug için kayıt açma
Dosya: `app/Http/Middleware/TrackVisitor.php`
- `store_slug` route parametresi varsa ve `Store::where('slug', …)->value('id')` **null**
  dönerse middleware hiçbir şey yazmadan `return $next($request)` yapar.
  (`MenuController` zaten `firstOrFail` ile 404 veriyor; kayıt öncesinde kesmek yeterli.)
- `store_slug` parametresi **olmayan** rotalar (`/tracking/*`) için mevcut davranış korunur:
  son 30 dk içindeki ziyarete bağlanır. Ziyaret bulunamazsa **yeni ziyaret açılmaz**,
  `tracking_visit_id` null olarak merge edilir; `TrackingController@hit` zaten 400 döner.
  Böylece "şubesiz ziyaret" üretimi tamamen durur.
- Store lookup'ı istek başına tekrarlanmasın diye `Cache::remember("store_id_by_slug:$slug", 300, …)`
  ile 5 dk önbelleklenir. (Slug değişimi nadir; `StoreResource` kaydında cache forget eklenebilir,
  şart değil.)

### Görev 1.2 — Bot listesini genişlet
Dosya: `app/Http/Middleware/TrackVisitor.php`
- Listeye ekle: `curl`, `wget`, `python-requests`, `python`, `Go-http-client`, `HealthCheck`,
  `Java/`, `libwww`, `okhttp`, `axios/`, `node-fetch`, `Scrapy`, `bot`, `spider`, `crawler`.
- Boş / null User-Agent → bot say.
- Liste `config/analytics.php` → `bot_user_agents` anahtarına taşınır; middleware config'den okur.
  Karşılaştırma `stripos` ile devam eder.

### Görev 1.3 — `TrackingController@hit` doğrulaması
Dosya: `app/Http/Controllers/TrackingController.php`
- `$request->validate([...])`:
  - `type` → `in:view,click,heartbeat`
  - `model` → `in:Product,Category,Campaign`
  - `id` → `integer|min:1`
  - `duration` → `integer|min:0|max:60` (heartbeat 10 sn gönderir; üst sınır 60)
- `model → class` eşlemesi `match` ifadesine çevrilir; eşleme `config/analytics.php` →
  `interactable_models` altına alınır.
- `tracking_visit_id` null ise 400 (mevcut davranış korunur).
- `interactable_id`'nin gerçekten var olup olmadığı **kontrol edilmez** (3 tablo için ekstra
  sorgu; orphan kayıt sayısı şu an sıfır, risk düşük).

### Görev 1.4 — Fingerprint logunu kaldır
Dosya: `app/Http/Controllers/TrackingController.php`
- `\Log::info('Fingerprint received', …)` satırı silinir (58.720 satır, 20 MB log).

### Görev 1.5 — Testler
Dosya: `tests/Feature/TrackVisitorMiddlewareTest.php` (yeni)
- Bilinmeyen slug → `visitors` ve `visits` tablosuna satır eklenmez, 404 döner.
- `curl/8.7.1` UA ile geçerli slug → kayıt eklenmez.
- Geçerli slug + normal UA → 1 visitor, 1 visit, `store_id` dolu, cookie set edilmiş.
- Aynı cookie ile 30 dk içinde ikinci istek → yeni visit açılmaz.
- `/tracking/hit` çerezsiz → 400, `interactions` boş.
Dosya: `tests/Feature/TrackingHitValidationTest.php` (yeni)
- `type=foo`, `model=Hacker`, `duration=9999` → 422; geçerli istek → 200 ve satır var.

---

## Phase 2: İndeksler

### Görev 2.1 — Migration
Dosya: `database/migrations/2026_09_15_000001_add_analytics_indexes.php` (yeni)
```php
Schema::table('visits', function (Blueprint $table) {
    $table->index(['visitor_id', 'started_at'], 'visits_visitor_started_idx'); // middleware sorgusu
    $table->index('started_at', 'visits_started_at_idx');                      // 24s kartları, prune
    $table->index(['store_id', 'started_at'], 'visits_store_started_idx');     // şube filtresi
    $table->index('table_id', 'visits_table_id_idx');                          // TopTablesTable
});
Schema::table('interactions', function (Blueprint $table) {
    $table->index('visit_id', 'interactions_visit_id_idx');                    // join + cascade delete
});
Schema::table('visitors', function (Blueprint $table) {
    $table->index('last_seen_at', 'visitors_last_seen_at_idx');                // "Tekil Ziyaretçi" kartı
    $table->index('created_at', 'visitors_created_at_idx');                    // prune
});
Schema::table('google_review_interactions', function (Blueprint $table) {
    $table->index(['store_id', 'showed_at'], 'gri_store_showed_idx');
    $table->index('visitor_id', 'gri_visitor_id_idx');
});
```
- `down()` tüm indeksleri isimle düşürür.
- Postgres'te `visits` 25k satır, `interactions` 57k satır: `CREATE INDEX` saniyeler sürer,
  `CONCURRENTLY` gerekmez. Yine de deploy sırasında `php artisan down` gerekmez; tablo kilidi kısa.

### Görev 2.2 — Doğrulama
- `EXPLAIN` ile middleware sorgusu (`visitor_id = ? AND started_at > ?`) ve 24s kart sorgularının
  `Index Scan` kullandığı teyit edilir; sonuç `docs/` altına değil, PR açıklamasına yazılır.

---

## Phase 3: Prune + Zamanlayıcı

### Görev 3.1 — `PruneAnalytics` komutunu genişlet
Dosya: `app/Console/Commands/PruneAnalytics.php`
- İmza: `analytics:prune {--days=90} {--visitors-days=180} {--dry-run}`
- Sıra (FK yüzünden önemli):
  1. `interactions` where `created_at < now-days` → sil (chunk'lı: `->limit(5000)` döngü,
     Postgres'te tek büyük DELETE de olur ama log/WAL için chunk daha güvenli).
  2. `visits` where `started_at < now-days` → sil (cascade ile kalan interactions da gider).
  3. `visitors` where `last_seen_at < now-visitors_days` **ve** `NOT EXISTS visits`
     **ve** `NOT EXISTS votes` **ve** `NOT EXISTS google_review_interactions` → sil.
     Oy ve review kaydı olan ziyaretçi korunur (unique kısıtlar ve huni raporu bozulmasın).
  4. `google_review_interactions` where `showed_at < now-days` → sil.
- `--dry-run`: sadece sayıları yazar.
- Her adımda silinen satır sayısı `$this->info` + `Log::info('analytics:prune', [...])`.

### Görev 3.2 — Schedule
Dosya: `routes/console.php`
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('analytics:prune')->dailyAt('04:30')->withoutOverlapping()->onOneServer();
```
- Laravel 12'de `bootstrap/app.php` içinde ek `withSchedule` gerekmez; `routes/console.php`
  otomatik yüklenir.

### Görev 3.3 — Crontab
Sunucu: `www-data` kullanıcısı (dosya sahipliği `www-data`).
```
* * * * * cd /var/www/qr-menu && php artisan schedule:run >> /dev/null 2>&1
```
- Ekleme `crontab -u www-data -e` ile yapılır; bu adım **kod değil, ops** — PR'a not düşülür,
  `docs/DEPLOY.md` yoksa README'ye tek satır eklenir.
- Doğrulama: `php artisan schedule:list` çıktısında komut görünür; ertesi gün
  `storage/logs/laravel.log` içinde `analytics:prune` satırı vardır.

### Görev 3.4 — İlk temizlik
- İndeksler deploy edildikten **sonra** (Phase 2 önce), tek sefer:
  `php artisan analytics:prune --dry-run` → sayılar makulse `php artisan analytics:prune`.
- Beklenen: ~76k ölü ziyaretçi, Ağustos 17 öncesi kayıtlar silinir. `storage/backups`
  dizinine önce `pg_dump -t visitors -t visits -t interactions` alınır.

### Görev 3.5 — Test
Dosya: `tests/Feature/PruneAnalyticsTest.php` (yeni)
- Eski/yeni interaction, visit, visitor + oy veren eski visitor seed'lenir; komut çalışır;
  sadece beklenenler silinir, oy veren visitor kalır. `--dry-run` hiçbir şey silmez.

---

## Phase 4: Panel — Tarih Aralığı + Trend + Düzeltilmiş Kartlar

### Görev 4.1 — Sayfa filtre formu
Dosya: `app/Filament/Pages/AnalyticsDashboard.php`
- `use Filament\Pages\Dashboard\Concerns\HasFiltersForm;` (trait `Page` ile de çalışır;
  `vendor/filament/filament/src/Pages/Dashboard/Concerns/HasFiltersForm.php`).
- `filtersForm(Schema $schema)`:
  - `Select::make('range')` → `24h` (varsayılan), `7d`, `30d`, `custom`
  - `DatePicker::make('from')`, `DatePicker::make('to')` → sadece `custom` seçiliyken görünür
  - `Select::make('store_id')` → tüm şubeler + "Hepsi"
- Filtre değerleri `$this->filters` üzerinden widget'lara `InteractsWithPageFilters` ile geçer.
- Tarih çözümleme tek yerde: `app/Support/AnalyticsRange.php` (yeni, küçük value object):
  `AnalyticsRange::fromFilters(array $filters): self` → `$from`, `$to`, `$storeId`,
  `previousPeriod()` (karşılaştırma için).

### Görev 4.2 — Widget'ları filtreye bağla
Tüm widget'lara `use Filament\Widgets\Concerns\InteractsWithPageFilters;` eklenir ve
`now()->subDay()` sabitleri `AnalyticsRange` ile değiştirilir:
- `AnalyticsStats` — `$from/$to/$storeId` uygular.
- `TopInteractionsTable` — `whereBetween('interactions.created_at', …)`, `store_id` varsa `visits.store_id = ?`.
  `stores` join'i **left join** olur (şubesiz eski ziyaretler düşmesin; şube sütunu "—" gösterir).
  Başlık dinamik: "En Çok Etkileşim (Son 7 Gün)".
- `TopLikesTable` — `votes_24h_count` → `votes_range_count`; `store_id` filtresi `whereHas('stores')`.
- `VisitorSourcesTable` — aralık + şube.
- `TopTablesTable` — `visits_7d_count` → `visits_range_count`.

### Görev 4.3 — Kartları düzelt
Dosya: `app/Filament/Widgets/AnalyticsStats.php`
- **Tekil Ziyaretçi**: `visitors.last_seen_at` yerine
  `Visit::whereBetween('started_at')->distinct('visitor_id')->count('visitor_id')` — botlar
  Phase 1 sonrası zaten girmez, ama tanım "aralıkta ziyaret eden benzersiz ziyaretçi" olur.
- **Toplam Oturum**: aralıktaki `visits` sayısı (aynı).
- **Etkileşimli Oturum** (yeni kart): en az 1 interaction'ı olan visit sayısı + yüzde.
- **Ort. Etkileşim Süresi**: payda = etkileşimli oturum sayısı (heartbeat'i olan visit),
  pay = o visit'lerin heartbeat toplamı. Açıklama metni: "etkileşimli ziyaret başına".
- Her kartta önceki dönemle karşılaştırma: `->description('+12% önceki döneme göre')`,
  `->descriptionIcon(heroicon-m-arrow-trending-up/down)`, `->color(success/danger)`.
- Kartlar tek sorguda: `Visit::selectRaw('count(*) …, count(distinct visitor_id) …')` +
  heartbeat toplamı ayrı bir sorgu. Ağır aggregate'ler `Cache::remember(key(range,store), 60s)`.

### Görev 4.4 — Trend grafiği
Dosya: `app/Filament/Widgets/VisitsTrendChart.php` (yeni, `ChartWidget`)
- Tip: `line`. Seriler: Oturum, Etkileşimli Oturum.
- Kırılım: aralık ≤ 48 saat → saatlik (`date_trunc('hour')`), aksi halde günlük.
- Tek sorgu: `SELECT date_trunc(...) AS bucket, count(*) , count(*) FILTER (WHERE EXISTS interaction)`.
  Postgres'e özel `FILTER`; SQLite testte çalışmayacağı için widget testi sadece
  "render oluyor" seviyesinde tutulur veya `DB::getDriverName()` ile CASE fallback yazılır.
- `AnalyticsDashboard::getHeaderWidgets()` sırası: Stats → Trend → TopInteractions →
  TopLikes → VisitorSources.
- `columnSpan = 'full'`, `maxHeight = '300px'`.

### Görev 4.5 — Dashboard blade
Dosya: `resources/views/filament/pages/analytics-dashboard.blade.php`
- `HasFiltersForm` header'ı otomatik render eder; blade'e ek gerekmez. Boş yorum satırı kalabilir.

### Görev 4.6 — Test
Dosya: `tests/Feature/AnalyticsDashboardTest.php` (yeni)
- Admin ile `/admin/analytics` 200 döner.
- Livewire ile `range=7d` set edildiğinde `AnalyticsStats` 7 günlük değeri verir
  (seed: 2 gün önce 1 visit, 10 gün önce 1 visit → 24h=0, 7d=1, 30d=2).
- Ortalama süre kartı: 1 visit heartbeat'li (30 sn), 1 visit heartbeat'siz → 30 sn (15 değil).

---

## Uygulama Sırası ve Commit Planı
1. `feat(analytics): bilinmeyen slug ve botlar için kayıt açma` — Phase 1 (1.1, 1.2, 1.4, 1.5 kısmı)
2. `feat(analytics): tracking/hit doğrulaması` — Phase 1 (1.3, testi)
3. `perf(analytics): visits/interactions/visitors indeksleri` — Phase 2
4. `feat(analytics): prune komutu ziyaretçileri de kapsar, günlük schedule` — Phase 3 (3.1, 3.2, 3.5)
5. **Ops:** crontab + ilk prune (3.3, 3.4) — deploy sonrası elle, PR'a not
6. `feat(analytics): panelde tarih aralığı, şube filtresi ve trend grafiği` — Phase 4
7. Memento: `3_Sessions/decisions.md` → "Bilinmeyen slug/bot ziyaret açmaz", "Prune 90/180 gün";
   `current_task.md` → sonraki adım: ürün detay `view`/süre olayı.

Her commit `php artisan test` ve `vendor/bin/pint --dirty` ile yeşil olmalı.

## Riskler / Kararlar
- **1.1'de tracking rotaları için yeni visit açmamak** davranış değişikliğidir: 30 dk hareketsiz
  kalıp sonra kategori değiştiren kullanıcının heartbeat'i düşer. Kabul edilebilir; alternatif
  (JS'in `store_slug` göndermesi) frontend değişikliği gerektirir ve kapsam dışı.
- Prune için `visitors-days=180`: parmak izi birleştirme geçmişi 6 ay korunur. Daha kısa
  istenirse `--visitors-days` ile ayarlanır.
- Phase 4'te `left join stores` ile şubesiz eski ziyaretler tabloya girer; Phase 3 ilk prune'dan
  sonra bu satırlar zaten azalır.
- SQLite test ortamında `date_trunc` / `FILTER` yok → 4.4 için driver kontrolü veya test kapsamı daraltılır.

## Verification Checklist
- [ ] `/.env`, `/wp-login.php` isteği `visitors`/`visits` satırı üretmiyor.
- [ ] `curl` UA ile `/gorukle` isteği satır üretmiyor; Safari UA üretiyor.
- [ ] `POST /tracking/hit` `type=foo` → 422.
- [ ] `EXPLAIN` middleware sorgusu `visits_visitor_started_idx` kullanıyor.
- [ ] `php artisan schedule:list` içinde `analytics:prune` 04:30 görünüyor; crontab aktif.
- [ ] `analytics:prune --dry-run` sayıları mantıklı; gerçek çalıştırma sonrası oy veren visitor duruyor.
- [ ] Panelde 24h / 7d / 30d / özel aralık ve şube seçimi tüm widget'ları değiştiriyor.
- [ ] Trend grafiği 24h'de saatlik, 7d'de günlük çiziyor.
- [ ] Ort. süre kartı sadece heartbeat'li oturumlara bölüyor; önceki dönem yüzdesi görünüyor.
- [ ] `php artisan test` yeşil.
