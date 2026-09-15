# Decisions Log

<!-- Append-only. Never delete an entry — mark it SUPERSEDED instead so the
     "why" behind old choices stays visible. Newest at the bottom or top,
     pick one and stay consistent. -->

## Template

### [YYYY-MM-DD] Decision: <short title>
- **Chose:** <what was decided>
- **Why:** <the reasoning / constraint that drove it>
- **Affects:** <link to current_task.md section it changes, e.g. "Sırada → step X">
- **Supersedes:** <link to a prior entry, or "none">

<!-- Example entry:

### 2026-07-21 Decision: Use queue-based email sending instead of sync
- Chose: dispatch email jobs to a queue worker instead of sending inline
- Why: sync sending was blocking checkout requests under load
- Affects: current_task.md → Sırada: "wire up queue worker"
- Supersedes: none

-->

### 2026-07-26 Decision: Install Memento by direct file copy, not git submodule
- **Chose:** Copy `CLAUDE.md`, `.memento/`, and `.claude/skills/memento-*`
  directly into the qr-menu repo instead of adding Bderq/memento as a git
  submodule.
- **Why:** User explicitly chose this over submodule to avoid submodule
  workflow complexity; project had no existing `CLAUDE.md` or
  `.claude/skills/`, so there was no conflict risk either way.
- **Affects:** current_task.md → Şu An (Memento kurulumu)
- **Supersedes:** none

### 2026-07-26 Decision: Communication style — terse, confirm-before-acting
- **Chose:** Short responses, no trailing summaries; state the plan and
  wait for confirmation before non-trivial code edits (not just risky/
  irreversible ones).
- **Why:** User's explicit preference, captured via AskUserQuestion during
  memento-init population of `prompt_patterns.md`.
- **Affects:** `.memento/1_Rules/prompt_patterns.md` (source of truth for
  this rule); current_task.md going forward should reflect plan-first
  behavior.
- **Supersedes:** none

### 2026-07-26 Fix: business_logic.md had two factual errors, corrected after verification
- **Symptom:** Initial AUTO-DETECTED pass claimed 4 campaign types and
  cited wrong line numbers/scope for the duplicated percentage-discount
  formula.
- **Root cause:** `app/Enums/CampaignType.php` actually defines 5 cases
  (missing `COLLECTIVE`, which the README also omits); the discount
  formula is duplicated across 4 call sites (2 in `CampaignService.php`,
  2 in `MenuService.php`), not 2 as first assumed.
- **Affects:** `.memento/2_Knowledge/business_logic.md` (corrected in
  place with verified file:line references).
- **Supersedes:** none (same session, caught before user relied on it)

### 2026-09-15 Decision: Guest notifications moved from WhatsApp bot to Telegram
- **Chose:** `GuestMessageController` artık `WPBOT_WEBHOOK_URL`
  (localhost:3000'deki ayrı WhatsApp bot servisi) yerine Telegram Bot
  API'ye bildirim gönderiyor. `WPBOT_*` env değişkenleri kaldırıldı;
  `whatsapp-bot.service` durduruldu ve açılıştan çıkarıldı (silinmedi,
  `systemctl enable --now whatsapp-bot` ile geri gelir).
- **Why:** Kullanıcı talebi. Yan fayda: WA bot + puppeteer Chrome ~950 MB
  RAM tutuyordu, kapatılınca sunucu belirgin rahatladı.
- **Affects:** current_task.md → Şu An; `app/Http/Controllers/GuestMessageController.php`
- **Supersedes:** none

### 2026-09-15 Decision: Şarkı isteği Spotify çağrıları qr-menu içinde kalsın (spotify-agent'a gitmesin)
- **Chose:** Spotify arama + sıraya ekleme qr-menu'nün kendi
  `SpotifyService`'inde, mağaza başına `stores` tablosundaki kimlik
  bilgileriyle yapılır.
- **Why:** Aynı sunucuda `/var/www/spotify-agent` (music.crashtheroof.com)
  adlı ayrı bir Laravel uygulaması var; UI'dan OAuth akışı, yazma
  yetkili token'ları ve lokasyon-cihaz eşlemesi hazır. İki seçenek
  sunuldu (A: agent'a iç API ekleyip kapı olarak kullan, B: qr-menu
  doğrudan Spotify'a gitsin) ve **kullanıcı B'yi seçti** — tek projede
  kalmak istedi. Bedeli kabul edildi: her mağaza için ayrı Spotify
  uygulaması + elle token, aynı çalar üzerinde iki bağımsız kontrolcü.
- **Affects:** current_task.md → Şu An; `app/Services/SpotifyService.php`
- **Supersedes:** none

### 2026-09-15 Decision: Şarkı isteği tespiti Gemini ile; model `gemini-3.5-flash-lite`'a sabitlendi
- **Chose:** Google Gemini REST (`generateContent`, JSON şema çıktısı),
  model `.env`'den ayarlanabilir, varsayılan `gemini-3.5-flash-lite`.
  SDK eklenmedi.
- **Why:** Kullanıcı ücretsiz kotası yeterli olduğu için Gemini'yi seçti.
  Model seçimi deneyerek bulundu: `gemini-2.5-flash` yeni anahtarlara
  **404** veriyor ("no longer available to new users"), `gemini-3.6-flash`
  zaman zaman 15 sn'yi aşıp timeout'a düşüyordu; `3.5-flash-lite` ~0.8 sn
  ve test edilen 7/7 Türkçe mesajda doğru sonuç verdi (bozuk yazımlar
  dahil: "masive atack teardrop" → Massive Attack / Teardrop).
- **Affects:** `app/Services/SongRequestDetector.php`, `config/services.php`
- **Supersedes:** none

### 2026-09-15 Fix: Telegram webhook sunucuya ulaşamadı → long-polling'e geçildi
- **Symptom:** Butona basılınca hiçbir şey olmuyordu. `getWebhookInfo`
  sürekli `"Connection timed out"` döndürüyordu; tıklamalar ya hiç
  gelmiyor ya 30+ sn sonra geliyordu.
- **Root cause:** Kesin sebep bulunamadı — ufw'de blok yok, nginx'te
  `deny` yok, fail2ban/crowdsec kapalı, sunucu dışarıdan erişilebilir
  durumda; Telegram'ın (91.108.5.3) istekleri sonunda 204 ile
  ulaşıyordu ama güvenilmez gecikmeyle. Telegram→sunucu yönü güvenilmez
  kabul edildi.
- **Fix:** Webhook silindi; `php artisan telegram:poll` (long-polling,
  `getUpdates`) yazıldı ve `qr-menu-telegram.service` olarak systemd'ye
  alındı. Callback işleme mantığı controller'dan `TelegramCallbackHandler`
  servisine taşındı ki webhook ve polling aynı kodu kullansın —
  webhook controller'ı ve `telegram:set-webhook` komutu duruyor, ileride
  geri dönülebilir.
- **Affects:** current_task.md → Şu An; `app/Console/Commands/TelegramPoll.php`
- **Supersedes:** none

### 2026-09-15 Fix: qr-menu'nün hiç kuyruk worker'ı yokmuş
- **Symptom:** İlk şarkı isteği job'ı `jobs` tablosunda öylece bekledi,
  hiç işlenmedi.
- **Root cause:** Sunucuda çalışan 3 `queue:work` süreci ERP ve
  spotify-agent projelerine aitti; qr-menu için systemd unit'i hiç
  oluşturulmamıştı. Proje bugüne dek senkron çalıştığı için fark
  edilmemiş.
- **Fix:** `/etc/systemd/system/qr-menu-queue.service` (diğer projelerin
  unit'leriyle aynı kalıpta) oluşturuldu ve enable edildi.
- **Affects:** current_task.md → Sırada (repo dışı kurulum notu)
- **Supersedes:** none

### 2026-09-15 Decision: Net eşleşmede tek buton, belirsizde 3 aday
- **Chose:** LLM hem sanatçı hem şarkı adı çıkardıysa **ve** Spotify'ın
  ilk sonucu (normalize edilmiş metin karşılaştırmasıyla) ikisini de
  içeriyorsa Telegram'da tek "▶ Sıraya ekle" butonu gösterilir; aksi
  halde ilk 3 aday listelenir. Her iki durumda "✖ Yok say" bulunur.
- **Why:** İlk sürüm hep 3 aday gösteriyordu; kullanıcı "Tarkan Şımarık
  zaten belli, neden 3 seçenek?" diye sordu. Tek butona indirmek net
  isteklerde gürültüyü kaldırıyor, 3 aday ise şarkı adı yazılmamış
  ("şu duman şarkısını açın") veya cover/remix karışan durumlarda
  yanlış şarkıyı sıraya sokmayı önlüyor. Kullanıcı bu ara yolu seçti.
- **Affects:** `app/Models/SongRequest.php` (`isConfidentMatch()`)
- **Supersedes:** none

### 2026-09-15 Decision: Eşzamanlı buton basımına atomik kilit
- **Chose:** Butona basıldığında tek bir `UPDATE ... WHERE status =
  'pending'` ile istek `processing`'e çekilir; etkilenen satır 0 ise
  ikinci basan kişiye "zaten işleniyor/eklendi" uyarısı döner. İşlem
  sonunda `finally` bloğu `processing` kalmışsa `pending`'e geri alır.
- **Why:** Kullanıcı "iki kişi aynı anda basarsa ne olur?" diye sordu.
  Mevcut güvence yalnızca polling'in tek süreçli olmasından geliyordu —
  koddan gelen bir garanti değildi; webhook'a dönülürse (PHP-FPM
  paralel çalışır) veya ikinci bir poller açılırsa şarkı iki kez
  eklenebilirdi. Paralel iki süreçle test edildi: `claimed=1` / `claimed=0`.
- **Affects:** `app/Services/TelegramCallbackHandler.php`
- **Supersedes:** none

### 2026-09-15 Fix: Spotify yazma yetkisi her mağaza için yeniden izin gerektirdi
- **Symptom:** Sıraya ekleme denemesi Görükle'de `401 "Permissions
  missing"`; Floyd'un token'ı ise aynı gün `invalid_grant` ile tamamen
  geçersizleşti. Görükle'nin izin linki `redirect_uri: Not matching
  configuration` hatası verdi.
- **Root cause:** (1) Spotify token'ı izin anındaki scope'larla sınırlı;
  mevcut token'a sonradan `user-modify-playback-state` eklenemiyor,
  hesap sahibinin yeniden onayı şart. (2) Her mağazanın **ayrı Spotify
  uygulaması** olduğu için redirect URI'nin her uygulamanın Dashboard
  ayarlarına tek tek eklenmesi gerekiyor. (3) Refresh token'lar hesap
  tarafından iptal edilebiliyor.
- **Fix:** Floyd ve Görükle `user-read-currently-playing` +
  `user-read-playback-state` + `user-modify-playback-state` ile yeniden
  yetkilendirildi. Yeni mağaza bağlarken izin linkine yazma scope'u
  baştan konmalı, yoksa aynı iş iki kez yapılır.
- **Affects:** current_task.md → Sırada (Floyd testi); `stores` tablosu
- **Supersedes:** none

### 2026-09-15 Fix: `route:cache` rate limiter tanımlarını devre dışı bıraktı (kendi yol açtığımız regresyon)
- **Symptom:** "Ses Verin" formu bazı kullanıcılarda **500** vermeye
  başladı; log'da `Rate limiter [guest-message] is not defined`. Anket
  oylaması (`poll-vote`) da aynı durumdaydı.
- **Root cause:** Performans için `php artisan route:cache` çalıştırıldı.
  Route cache açıkken `routes/web.php` her istekte yüklenmez; limiter'lar
  o dosyanın tepesinde `RateLimiter::for(...)` ile tanımlı olduğu için
  hiç kaydedilmiyor, `throttle:guest-message` middleware'i de bulamıyor.
- **Fix:** Tanımlar `AppServiceProvider::boot()`'a taşındı. **Kural:**
  bu projede route cache kullanıldığı sürece `RateLimiter::for()`,
  `Route::bind()` gibi çağrılar route dosyasında durmamalı.
- **Affects:** `app/Providers/AppServiceProvider.php`, `routes/web.php`
- **Supersedes:** none

### 2026-09-15 Decision: Sesverin limiti IP yerine ziyaretçi çerezinde
- **Chose:** Günlük 2 mesaj hakkı `qr_menu_visitor_id` çerezi başına
  sayılır (çerez yoksa IP'ye düşer); ayrıca IP başına saatte 20 mesaj
  tavanı eklendi.
- **Why:** Kullanıcı PC'den hakkını doldurup telefondan denediğinde
  "hakkın bitti" aldı — dükkânın wifisindeki herkes tek public IP'den
  çıktığı için **tüm mekân** günde 2 mesajla sınırlıydı; akşam
  yoğunluğunda üçüncü müşteriden sonra kimse istek gönderemezdi.
  Mobil veride de CGNAT yüzünden benzer çakışma olabiliyor. Çerez
  silinerek aşılabilir ama kafe için kabul edilebilir; IP tavanı tek
  kişinin grubu boğmasını engelliyor. Günlük hak kullanıcı tercihiyle
  2'de bırakıldı.
- **Affects:** `.memento/2_Knowledge/business_logic.md` ("Ses Verin"
  bölümü); `app/Providers/AppServiceProvider.php`
- **Supersedes:** none

### 2026-09-15 Fix: now-playing polling'i her 10 sn'de ziyaretçi takibi tetikliyordu
- **Symptom:** Site gözle görülür yavaşladı (yük 8.1, menü sayfası 5.2 sn).
  Nginx log'unda son 200 isteğin 80'i `/api/{store}/now-playing`.
- **Root cause:** Polling isteği de `TrackVisitor` middleware'inden
  geçiyordu; her tık başına mağaza + ziyaretçi + ziyaret sorguları ve
  bir `last_seen_at` yazması üretiyordu. Açık her menü, hiçbir şey
  yapılmasa bile 10 sn'de bir 4-5 sorgu + 1 yazma demekti.
- **Fix:** Route'a `withoutMiddleware(TrackVisitor::class)` eklendi.
  DİKKAT: `TrackVisitor` hem route grubunda hem `bootstrap/app.php`'de
  tüm `web` grubuna ekli — gruptan çıkarmak yetmez, açıkça devre dışı
  bırakmak gerekir. Ziyaret süresi `started_at`'e göre ölçüldüğü için
  analitik bozulmadı; doğrulandı (polling'de `last_seen_at` sabit,
  sayfa isteğinde güncelleniyor).
- **Affects:** `routes/web.php`
- **Supersedes:** none


### 2026-09-15 Decision: Analitik sertleştirme — bot/bilinmeyen slug ziyaret açmaz, prune + indeks + filtreli panel
- **Context:** Analitik incelemesi: son 7 günde 6.113 ziyaretin yalnızca
  1.238'inde etkileşim vardı; 1.890 ziyaret şubesizdi; 94.894 ziyaretçinin
  75.981'i hiçbir ziyarete bağlı değildi. Kaynaklar: (1) `/{store_slug}`
  tek segmentli her yolu (`/.env`, `/phpinfo.php`) yakalayıp kayıt açıyordu,
  (2) `TrackVisitor` hem route grubunda hem `bootstrap/app.php` web grubunda
  ekliydi → `/` yönlendirmesi dahil her web isteği şubesiz kayıt üretiyordu,
  (3) bot listesinde curl/HealthCheck/python yoktu, (4) `analytics:prune`
  hiç zamanlanmamıştı ve `visitors` tablosunu temizlemiyordu, (5) `visits`
  tablosunda PK dışında indeks yoktu.
- **Chose:** Mağaza bulunamazsa hiç kayıt açılmaz; tracking çağrısı 30 dk
  penceresi dışındaysa yeni (şubesiz) ziyaret açmaz; tanınan çerezi ve şubesi
  olmayan tracking çağrısı ziyaretçi de açmaz. `TrackVisitor` yalnızca route
  grubunda (global web grubundan çıkarıldı). Bot listesi `config/analytics.php`.
  `hit` ucu doğrulanır (422). `analytics:prune` günlük 04:30, `--days=90`
  (interactions, visits), `--visitors-days=180` (yalnızca ziyareti/oyu/review
  kaydı olmayan ziyaretçiler), Google review etkileşimlerine dokunmaz.
  Panel: `AnalyticsDashboard` Filament `Dashboard` + `HasFiltersForm`
  (24s/7g/30g/özel + şube); `AnalyticsRange` + `AnalyticsService`; ort. süre
  yalnızca heartbeat'li oturumlara bölünür; trend grafiği.
- **Why:** Panel kartları 4–5 kat şişikti; gerçek müşteri davranışı bot
  gürültüsünde kayboluyordu. Şubesiz ziyaret üretimini kaynağında kesmek,
  raporlarda filtrelemekten daha güvenilir.
- **Affects:** `app/Http/Middleware/TrackVisitor.php`, `bootstrap/app.php`,
  `config/analytics.php`, `app/Console/Commands/PruneAnalytics.php`,
  `routes/console.php`, `app/Filament/Pages/AnalyticsDashboard.php`,
  `app/Filament/Widgets/*`, `app/Services/AnalyticsService.php`,
  `app/Support/AnalyticsRange.php`, `docs/PLAN-analytics-hardening.md`
- **Supersedes:** "2026-09-15 Fix: now-playing polling" kaydındaki
  "gruptan çıkarmak yetmez, global web grubunda da ekli" notu — artık
  yalnızca route grubunda; `withoutMiddleware` hâlâ çalışır ama global
  ekleme yok.

### 2026-09-15 Decision: Test paketi ayrı Postgres veritabanı kullanır (`qr_menu_test`)
- **Context:** `php artisan test` sunucuda **üretim** veritabanına bağlanıyordu:
  `bootstrap/cache/config.php` (config:cache) phpunit.xml'deki sqlite ayarını
  eziyor. `RefreshDatabase` → `migrate:fresh` yalnızca `APP_ENV=production`
  onay istediği için (non-interactive'de vazgeçti) veri silinmedi.
  Ayrıca `MenuController` Postgres'e özgü SQL (`INTERVAL '6 hours'`)
  kullandığı için sqlite'ta zaten çalışmıyor.
- **Chose:** phpunit.xml → `pgsql` + `qr_menu_test` (sahibi `qr_menu_user`,
  `sudo -u postgres createdb -O qr_menu_user qr_menu_test` ile oluşturuldu).
  `tests/TestCase::setUpTraits` yalnızca `:memory:` veya `*_test` adlı
  veritabanına izin verir, aksi halde `RuntimeException`.
  Sunucuda çalıştırma: `APP_CONFIG_CACHE=/nonexistent/c.php
  APP_ROUTES_CACHE=/nonexistent/r.php APP_EVENTS_CACHE=/nonexistent/e.php
  php artisan test` (cache'i devre dışı bırakır, config:clear gerekmez).
- **Why:** Config cache temizlenseydi ilk `php artisan test` üretim
  veritabanını sıfırlayacaktı.
- **Affects:** `phpunit.xml`, `tests/TestCase.php`
- **Supersedes:** none


### 2026-09-15 Decision: Analitik içgörü planı — birim "oturum/masa", prune yerine tam sıfırlama
- **Context:** Kullanıcı analitik sistemini "taş gibi" yapıp neyin ölçülebileceğine
  karar vermek, sonra ziyaretçileri sıfırlayıp temiz başlamak istedi. Google review
  hunisinin hatalı olduğunu bildirdi; kök neden bulundu (aşağıdaki Fix kaydı).
- **Chose:** `docs/PLAN-analytics-insights.md` yazıldı; 5 alışkanlık başlığının
  hepsi kapsamda. Analiz birimi **oturum ve masa**, kişi değil (çerez/parmak izi
  kusurlu). Yeni olaylar tek `interactions` tablosuna `meta` json ile; yeni tablo
  yok. İki panel sayfası: Analitik (günlük) + Davranış (derin). **Prune ile
  kademeli temizlik yerine**, kod deploy edilip 2–3 gün doğru veri aktıktan sonra
  ziyaretçiye bağlı tüm tablolar TRUNCATE CASCADE ile sıfırlanacak;
  `guest_messages` ve `song_requests` korunur. Uygulama, §10 kararları ve §7
  doğrulamaları bitmeden **başlamaz** (kullanıcı kuralı).
- **Why:** `visitors` silmek cascade ile visits/interactions/votes/poll_votes/
  google_review_interactions'ı da siler; review verisi zaten hatalı olduğu için
  korumaya değmez, temiz başlangıç daha dürüst. Prune günlük bakım olarak kalır.
- **Affects:** `docs/PLAN-analytics-insights.md`; ileride `interactions`,
  `guest_messages`, `visits.ended_at`, `GoogleReviewPopup.jsx`, panel sayfaları
- **Supersedes:** "2026-09-15 Decision: Analitik sertleştirme" kaydındaki
  "ilk temizlik `analytics:prune --visitors-days=30`" adımı → iptal, yerine tam sıfırlama.

### 2026-09-15 Fix (tespit, henüz uygulanmadı): Google review hunisi yanlış sayıyor
- **Symptom:** Widget'ta 6 "Evet", 2 "Google'a yönlenme" ama yönlenenler
  `dismissed` durumunda; 1.828 gösterim ama 988 kişi; 584 cevapsız `showed`.
- **Root cause:** (1) `GoogleReviewPopup.handleGoogleRedirect` önce
  `handleDismiss()` çağırıyor, o da PATCH `status=dismissed` gönderiyor → Google'a
  giden herkes accepted→dismissed oluyor. (2) `localStorage` "görüldü" işareti
  yalnızca Hayır/X'te konuyor; cevapsız kapatan kişi ertesi gün yeniden görüyor,
  sunucu tarafında tekilleştirme yok. (3) Widget tüm zamanlara bakıyor, tarih/şube
  filtresi yok; yalnızca Görükle'de URL tanımlı.
- **Fix (planlandı, PLAN-analytics-insights §3):** Google tıklamasında dismissed
  gönderme; `showed` başarılı olunca işareti hemen koy + sunucuda 30 gün
  `visitor_id+store_id` tekilleştirme; "cevapsız" ayrı durum; widget aralık+şube.
- **Affects:** `resources/js/Components/GoogleReviewPopup.jsx`,
  `app/Http/Controllers/GoogleReviewInteractionController.php`,
  `app/Filament/Widgets/GoogleReviewStatsWidget.php`
- **Supersedes:** none
