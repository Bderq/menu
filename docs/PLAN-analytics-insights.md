# PLAN: Analytics Insights — Müşteri Alışkanlıkları, Review Hunisi Düzeltmesi, Temiz Başlangıç

> **Type:** WEB (Laravel 12 + React/Inertia + Filament v5)
> **Öncül:** `PLAN-analytics-hardening.md` (Phase 1–4 kodlandı; canlı deploy adımları 1–5 bekliyor)
> **Amaç:** QR menüden müşteri alışkanlıklarını beş başlıkta okunur hale getirmek,
> Google review hunisini düzeltmek, ardından ziyaretçi verisini sıfırlayıp temiz başlamak.

---

## 0. İlkeler

- **Analiz birimi "oturum" ve "masa"dır, "kişi" değil.** Çerez/parmak izi kusurlu; aynı masadaki
  üç telefon üç ziyaretçidir. Kişi bazlı metrikler yalnızca "geri dönüş" için ve trend olarak kullanılır.
- **Tek olay ucu:** tüm yeni olaylar mevcut `POST /tracking/hit` ile `interactions` tablosuna yazılır.
  Yeni tablo yok; yalnızca `interactions` tablosuna `meta` (json, nullable) sütunu eklenir.
- **Her olay için tanım tablosu** `config/analytics.php` → `interaction_types` içinde; tanımsız tip 422.
- **Sunucu saati / iş günü:** rapor kırılımları `Europe/Istanbul`; "iş günü" saat 06:00'da başlar
  (mevcut `visitDaysCount` mantığıyla aynı; `business_logic.md`'deki açık soru böylece kapanır).
- **Gizlilik:** IP ve user-agent 90 gün sonra prune ile gider; `meta` içinde serbest metin tutulmaz.

---

## 1. Olay Sözlüğü (interactions.type)

| type | model / id | meta | Tetik (frontend) | Var mı? |
|---|---|---|---|---|
| `view` | Category | — | Kategori 2.5 sn görünür kalınca (`useTracking`) | ✅ |
| `heartbeat` | Category | — | 10 sn'de bir, sekme görünürken | ✅ |
| `click` | Product / Campaign | — | Ürün kartı / kampanya kartı tıklaması | ✅ (kampanya çift sayım kontrolü: §7) |
| `product_view` | Product | `{ "seconds": n, "from": "list"\|"next" }` | Ürün detay modalı **kapanınca** (açık kalma süresi ile) | ➕ |
| `filter` | Category (aktif ana kategori) / null | `{ "vegan": bool, "vegetarian": bool, "glutenFree": bool }` | Filtre state değişince (debounce 500 ms) | ➕ |
| `drawer_open` | null | `{ "tab": "filters"\|"feedback"\|"survey", "source": "button"\|"review_popup" }` | Drawer açılınca ve sekme değişince | ➕ |
| `campaign_impression` | Campaign | — | Kampanya kartı %50 görünür (IntersectionObserver), oturumda kampanya başına 1 | ➕ |
| `tab` | null | `{ "tab": "food"\|"drink"\|"campaign" }` | Ana sekme değişince | ➕ |
| `search` | null | — | (Menüde arama yok; ileride eklenirse) | ⏸ |

Backend değişiklikleri:
- Migration: `interactions.meta json nullable`; `interactable` morph zaten nullable.
- `TrackingController@hit`: `meta` → `array|nullable`, izinli anahtarlar tip bazında whitelist
  (`config('analytics.interaction_meta')`). `model` artık `nullable` (drawer/tab/filter için).
- `useTracking.js`: `trackEvent(type, model = null, id = null, meta = null)` genel fonksiyonu;
  `trackClick` bunun üstünde kalır. `navigator.sendBeacon` ile modal kapanışında kayıp önlenir.

---

## 2. Beş Başlık → Widget Haritası

Her widget `pageFilters` (aralık + şube) alır; hepsi `AnalyticsService` üzerinden hesaplanır ve 60 sn cache'lenir.
Panel iki sayfaya bölünür: **Analitik** (genel bakış) ve **Davranış** (derin analiz). Aşağıdaki tablo sayfa → widget.

### 2.1 Zaman ve Yoğunluk (Analitik sayfası)
| Widget | Kaynak | Hesap |
|---|---|---|
| `VisitsTrendChart` (var) | visits | saatlik/günlük oturum + etkileşimli oturum |
| `HeatmapWeekHour` (yeni, ChartWidget bar/matrix) | visits | gün × saat oturum matrisi; iş günü 06:00 kayması |
| `TableOccupancyTable` (yeni) | visits.table_id | masa başına: oturum, ortalama oturum süresi (ilk–son heartbeat), tekrar açılış sayısı |
| `ReturnWithinDayStat` (kart) | visits | aynı ziyaretçi + masa, aynı iş günü, 30 dk sonra yeni oturum → "ikinci sipariş niyeti" oranı |

### 2.2 Keşif ve Okuma (Davranış sayfası)
| Widget | Kaynak | Hesap |
|---|---|---|
| `EntryCategoryTable` | interactions `view` (oturumun ilk view'ı) | ilk açılan kategori dağılımı |
| `CategoryFlowSankey` | ardışık `view` çiftleri | en sık geçişler (top 15 çift; Sankey yerine tablo, Chart.js Sankey eklentisi gerekmez) |
| `CategoryEngagementTable` | `view` + `heartbeat` + `click` | kategori başına: görüntüleme, ort. süre, tıklama; **"okunuyor ama tıklanmıyor"** işareti (süre üst çeyrek, tıklama alt çeyrek) |
| `SessionDepthStat` | interactions per visit | oturum başına kategori sayısı ve `product_view` sayısı; sığ/derin dağılımı |
| `ProductDwellTable` | `product_view.meta.seconds` | ürün başına açılma, ort. süre, beğeni; **"uzun okunup beğenilmeyen"** işareti |

### 2.3 Tercih ve Kısıt (Davranış sayfası)
| Widget | Kaynak | Hesap |
|---|---|---|
| `DietFilterStat` | `filter` | oturumların %'si filtre kullanmış; vegan/vejetaryen/glütensiz kırılımı |
| `FoodDrinkByHourChart` | `heartbeat` + categories.type | saat bazında yiyecek/içecek ilgi payı; "Alkolsüz" kategorisi ayrı seri |
| `TopLikesTable` (var) | votes | aralıkta beğeni |
| `PollResultsWidget` (var) | poll_votes | aralık filtresine bağlanır |

### 2.4 Kampanya ve Mesaj (Analitik sayfası)
| Widget | Kaynak | Hesap |
|---|---|---|
| `CampaignFunnelTable` | `campaign_impression` + `click` | kampanya başına gösterim, tıklama, CTR; `is_live` süresiyle normalize |
| `SongRequestStat` | song_requests + guest_messages | aralıkta istek sayısı, kabul (`queued_at`) oranı, saat dağılımı |
| `MusicEffectStat` | visits × `now-playing` durumu | ⏸ **ertelendi**: menü "müzik çalıyor mu" bilgisini oturuma yazmıyor; gerekirse `visits.music_playing` eklenir (karar bekliyor) |

### 2.5 Sadakat ve Memnuniyet (Analitik sayfası)
| Widget | Kaynak | Hesap |
|---|---|---|
| `ReturningVisitorStat` | visits | aralıkta ≥2 farklı iş gününde gelen ziyaretçi %'si; medyan dönüş aralığı |
| `VisitorSourcesTable` (var) | referer/utm | + "yeni vs dönen" sütunu |
| `GoogleReviewFunnel` (yeniden yazılır) | google_review_interactions | §3'teki düzeltmelerle: kişi bazlı gösterim, evet/hayır/cevapsız, Google'a gidiş, Ses Ver dönüşümü; aralık + şube |
| `GuestMessagesByHourTable` | guest_messages | saat ve masa kırılımı (masa için §4) |

---

## 3. Google Review Hunisi Düzeltmeleri

1. **Evet → Google akışında "dismissed" gönderilmesin.** `GoogleReviewPopup.handleGoogleRedirect`
   `handleDismiss()` çağırmayı bırakır; yalnızca `google-clicked` POST eder, popup'ı kapatır,
   `localStorage` işaretini koyar. Backend `googleClicked` ayrıca `status='accepted'` olarak sabitler
   (ne gelirse gelsin Google'a giden accepted'dır).
2. **Kişi başına tek gösterim.** `showed` POST başarılı olduğunda `localStorage` işareti hemen konur
   (cevap beklenmez). Sunucu tarafı emniyet: aynı `visitor_id + store_id` için son 30 günde kayıt varsa
   yeni `showed` açılmaz, mevcut id döner (`firstOrCreate` mantığı).
3. **Cevapsız durumu ayrı sayılır.** `status='showed'` ve `responded_at null` → "Cevapsız";
   widget'ta "Kapatıldı" ile karıştırılmaz.
4. **Widget yeniden yazılır** (`GoogleReviewFunnel`): aralık + şube; kişi bazlı gösterim (distinct visitor);
   oranlar: evet/gösterim, Google/evet, Ses Ver/hayır, cevapsız/gösterim. Floyd için URL boşsa "pasif" uyarısı.
5. **Ses Ver bağlantısı korunur** (`guest_messages.review_interaction_id` akışı mevcut).

---

## 4. Şema Değişiklikleri (tek migration)

| Tablo | Değişiklik | Neden |
|---|---|---|
| `interactions` | `meta json null` | §1 olay meta'sı |
| `guest_messages` | `visit_id fk null`, `table_id fk null` | mesajın masa/saat analizi (2.5) |
| `google_review_interactions` | `unique(visitor_id, store_id, showed_at::date)` yerine uygulama kontrolü; `index(visitor_id, store_id, showed_at)` | §3.2 |
| `visits` | `ended_at` doldurulmaya başlar: son heartbeat/olay zamanı (`hit` her çağrıda `visits.ended_at = now()` günceller, 60 sn'de en fazla bir yazma) | oturum süresi için heartbeat toplamak yerine doğrudan süre |

---

## 5. Backend Yapısı

- `app/Services/AnalyticsService.php` büyür; bölünür:
  `Analytics/TrafficQueries` (2.1, 2.5), `Analytics/BehaviorQueries` (2.2, 2.3),
  `Analytics/CampaignQueries` (2.4), `Analytics/ReviewFunnelQueries` (§3).
  Hepsi `AnalyticsRange` alır, `array` döner, 60 sn cache.
- Ağır sorgular (Sankey çiftleri, gün×saat matrisi) `LATERAL`/window function ile tek sorgu; Postgres'e
  özgü olması kabul (testler Postgres'te).
- Filament: `AnalyticsDashboard` (Analitik) + yeni `BehaviorDashboard` (Davranış), ikisi de
  `Dashboard + HasFiltersForm`; filtre formu ortak trait `HasAnalyticsFilters`.
- Ana panel (`/admin`) analitik widget'larını **göstermez**: widget'larda
  `public static function canView(): bool` → yalnızca analitik sayfalarında (`Livewire` parent kontrolü)
  veya `discoverWidgets` yerine açık liste.

---

## 6. Frontend Yapısı

- `useTracking.js`: `trackEvent`, `trackProductView(open/close)`, `trackFilter`, `trackDrawer`,
  `useCampaignImpressions(ref)` (IntersectionObserver, oturum içi `Set` ile tekilleştirme).
- `Index.jsx`: ürün modalı aç/kapa noktaları (`setSelectedItem`), `activeFilters` effect,
  drawer `onOpen`/`setDrawerTab`, `mainTab` effect, kampanya kartı ref'i.
- `GoogleReviewPopup.jsx`: §3.1–3.2.
- `sendBeacon` fallback: `pagehide` olayında açık ürün modalı ve son heartbeat gönderilir.

---

## 7. Doğrulanacak Şüpheler (uygulamadan önce)

- **Kampanya çift sayım:** `Index.jsx:396` ve `:445` aynı kampanya için iki farklı liste mi
  (aktif / yaklaşan), yoksa aynı kart iki kez mi render ediliyor? Tek kartta tek `trackClick` olmalı.
  Veri: 6.000 kampanya tıklaması vs 4.135 ürün tıklaması.
- **`activeSubCategory` id çözümü:** `split('-').pop()` her zaman DB id mi veriyor?
  `heartbeat`'in doğru kategoriye yazıldığı örnekle doğrulanır.
- **Heartbeat sekme arka planda:** `visibilityState` kontrolü var; iOS Safari'de arka plana atılınca
  interval durur mu? `pagehide` beacon ile son süre yazılır.

---

## 8. Temiz Başlangıç (sıfırlama) — en son adım

Sıra önemlidir; kod tamamen deploy edilip 2–3 gün doğru veri aktığı görüldükten sonra:

1. Tam yedek: `pg_dump -Fc qr_menu_db > storage/backups/qr_menu_db_pre_reset_<ts>.dump`
2. Tek transaction içinde:
   ```sql
   TRUNCATE interactions, visits, google_review_interactions, poll_impressions, poll_votes, votes, visitors
     RESTART IDENTITY CASCADE;
   ```
   `guest_messages` ve `song_requests` **korunur** (visitor'a bağlı değil); `visit_id/table_id` null kalır.
3. `php artisan cache:clear` (analitik cache'leri).
4. Panelde kartların sıfırdan başladığı ve ilk gerçek ziyaretin doğru şube/masa ile düştüğü doğrulanır.
5. Karar: sıfırlama tarihi `decisions.md`'ye yazılır; "tüm zamanlar" yerine "sıfırlamadan beri" ifadesi kullanılır.

Alternatif (daha yumuşak): yalnızca `google_review_interactions` + ölü ziyaretçiler silinir, ziyaret geçmişi kalır.
Kullanıcı tam sıfırlamayı tercih etti; alternatif yalnızca fikir değişirse.

---

## 9. Uygulama Sırası ve Commit Planı

| # | Commit | Kapsam | Bağımlılık |
|---|---|---|---|
| 1 | `feat(analytics): interactions.meta + genel trackEvent` | §1 migration + controller + useTracking | hardening deploy adımları tamam |
| 2 | `feat(analytics): ürün detay süresi, filtre, drawer, sekme olayları` | §1 ➕ satırları (kampanya gösterimi hariç) | 1 |
| 3 | `feat(analytics): kampanya gösterimi + CTR` | IntersectionObserver + `CampaignFunnelTable` | 1, §7 çift sayım kararı |
| 4 | `fix(review): evet→Google akışı, tek gösterim, cevapsız durumu` | §3 | — (bağımsız, önce de çıkabilir) |
| 5 | `feat(analytics): guest_messages visit/table bağı, visits.ended_at` | §4 | 1 |
| 6 | `feat(analytics): Davranış sayfası (keşif, tercih widget'ları)` | 2.2, 2.3 | 2 |
| 7 | `feat(analytics): yoğunluk, masa, sadakat, mesaj widget'ları` | 2.1, 2.4, 2.5 | 3, 5 |
| 8 | `refactor(analytics): ana panelden analitik widget'larını kaldır` | §5 son madde | 6, 7 |
| 9 | Ops: deploy + 2–3 gün gözlem + §8 sıfırlama | — | hepsi |

Her commit: `APP_CONFIG_CACHE=/nonexistent/c.php APP_ROUTES_CACHE=/nonexistent/r.php APP_EVENTS_CACHE=/nonexistent/e.php php artisan test` yeşil, `pint --dirty`.
Frontend: `npm run build` sonrası `public/build` commit edilmiyorsa deploy adımına eklenir (doğrulanacak).

---

## 10. Açık Kararlar (uygulamadan önce cevap gerekli)

1. **Müzik etkisi (2.4 `MusicEffectStat`)** için `visits.music_playing` sütunu eklensin mi, yoksa bu başlık atlansın mı?
2. **Davranış sayfası ayrı mı, tek uzun sayfa mı?** Öneri: ayrı (Analitik = günlük bakış, Davranış = haftalık/aylık okuma).
3. **Ürün detay süresi için üst sınır:** 10 dk üstü "açık unutuldu" sayılıp 600 sn'de kırpılsın mı? Öneri: evet.
4. **Sıfırlama kapsamı:** §8'deki tam sıfırlama mı (öneri), alternatif yumuşak sıfırlama mı?
5. **Floyd'da Google review URL'i** tanımlanacak mı? Tanımlanmazsa huni yalnızca Görükle için anlamlı.

## Verification Checklist
- [ ] Ürün modalı 12 sn açık kalıp kapanınca `product_view` meta.seconds≈12 düşüyor.
- [ ] Vegan filtresi açılınca tek `filter` olayı (debounce) düşüyor.
- [ ] Drawer'ı review popup'ından açmak `source=review_popup` yazıyor.
- [ ] Kampanya kartı ekrana girince oturumda 1 kez `campaign_impression`; tıklama 1 kez `click`.
- [ ] Evet → Google: kayıt `accepted` + `google_redirected=true`, asla `dismissed` değil.
- [ ] Aynı ziyaretçi aynı gün ikinci kez menü açınca yeni `showed` açılmıyor.
- [ ] Ana panelde analitik widget'ları görünmüyor; Analitik ve Davranış sayfaları filtreye tepki veriyor.
- [ ] Sıfırlama sonrası `guest_messages` ve `song_requests` sayıları değişmedi.
