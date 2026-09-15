# Current Task

## Şu An / Now
2026-09-15 (akşam) oturumu: **analitik sertleştirme** — `docs/PLAN-analytics-hardening.md`
Phase 1–4 kodlandı, test edildi ve commitlendi (`a07b57b`..`5c1b8b2`).
Canlıda `config:cache` yenilendi (yeni `config/analytics.php` yüklenmeden
`/tracking/hit` 18:29–18:41 arası 500 verdi; düzeltildi, doğrulandı).
Yedekler: `storage/backups/qr_menu_db_full_20260915_182821.dump` (+ analytics-only
dump, uploads tar, worktree patch).

**Canlıda henüz yapılmayan deploy adımları** (izin gerektirdi):
1. `php artisan migrate --force` → `2026_09_15_180000_add_analytics_indexes` bekliyor
2. `php artisan optimize && php artisan filament:optimize` (route/filament cache'i
   yeni widget'larla tazele)
3. `www-data` crontab'ına `* * * * * cd /var/www/qr-menu && /usr/bin/php artisan schedule:run`
   (spotify-agent ve erp-crash-test için zaten aynı satır var)
4. `/admin/analytics` sayfasının canlıda gözle doğrulanması (filtre formu + trend grafiği)
   (login gerektiği için Claude açamadı)
5. ~~İlk prune~~ → **iptal**: kullanıcı prune yerine tam sıfırlama tercih etti
   (aşağıdaki insights planı §8). Cron satırı yine de eklenmeli (schedule tanımlı).

**Sonraki iş: `docs/PLAN-analytics-insights.md`** — kullanıcı "hepsini beğendim, hepsi
için plan yap" dedi, plan yazıldı ve commitlendi (`e698cdc`), **uygulama başlamadı**.
Kullanıcının kuralı: "Her şeyi netleştirmeden başlama." Planın §10'daki 5 açık karar
cevaplanmadan ve §7'deki 3 şüphe doğrulanmadan kod yazılmayacak.
İçerik: 5 alışkanlık başlığı (yoğunluk, keşif, tercih, kampanya/mesaj, sadakat),
`interactions.meta` json + yeni olaylar (product_view, filter, drawer_open, tab,
campaign_impression), Google review hunisi düzeltmesi, "Davranış" adlı ikinci panel
sayfası, en sonda tam sıfırlama (guest_messages ve song_requests korunur).

**Google review hunisi neden yanlış** (bu oturumda bulundu, henüz düzeltilmedi):
`GoogleReviewPopup.handleGoogleRedirect` önce `handleDismiss()` çağırıyor → Google'a
giden herkes `accepted` sonra `dismissed` oluyor (6 accepted vs 2 redirected-dismissed).
"Görüldü" işareti yalnızca Hayır/X'te yazıldığı için cevapsız kapatan aynı kişiye
tekrar gösteriliyor (988 kişi, 1.828 gösterim, 584 cevapsız `showed`).

Bilinen: `tests/Feature/ProductCreateTest` önceden de kırık (`data.category_id`
form hatası), bu oturumla ilgisiz.

---
Önceki oturum (2026-09-15 gündüz):
2026-09-15 oturumu: "Ses Verin" misafir mesajları üzerine **şarkı isteği
sistemi** kuruldu ve canlıda uçtan uca doğrulandı (commit `c8d4194`).
Akış: misafir mesajı → kuyruk job'ı → Gemini mesajın şarkı isteği olup
olmadığını belirler ve sanatçı/şarkı çıkarır → mağazanın kendi Spotify
hesabında aranır → Telegram grubuna butonlu bildirim → butona basınca o
mağazanın Spotify çalma sırasına eklenir. Görükle'de gerçek istek iki kez
sıraya eklenerek doğrulandı; Floyd'da tespit + buton akışı çalıştı,
"Yok say" ile kapatıldı.

Aynı oturumda ayrıca:
- Misafir bildirimleri **WhatsApp bot'undan Telegram'a taşındı** (commit
  `f6bd757`); `whatsapp-bot.service` durduruldu ve açılıştan kaldırıldı.
- Bekleyen **StoreTables** (masa/QR yazdırma) çalışması commitlendi
  (`0c0b8bb`).
- **Performans:** site yavaşlamıştı; `artisan optimize` cache'leri
  oluşturuldu, WA bot + Chrome (~950 MB) kapatıldı, `now-playing`
  polling'i `TrackVisitor`'dan çıkarıldı. Yük 8.1 → 1.6, menü sayfası
  5.2 sn → 0.8 sn, `now-playing` 1.3 sn → 0.12 sn.
- Git remote URL'i `bderq/menu` → `Bderq/menu` olarak düzeltildi.

Oturumun sonunda "Ses Verin" bozuldu diye bildirildi; iki ayrı sorun
çıktı ve ikisi de düzeltildi: (1) `route:cache` yüzünden rate limiter
tanımları devre dışı kalmış ve form 500 veriyordu — tanımlar
`AppServiceProvider`'a taşındı (`b5c6a29`); (2) limit IP bazlı olduğu
için dükkân wifisindeki herkes tek kotayı paylaşıyordu — çerez bazlına
çevrildi, IP tavanı eklendi (`5dd605e`). `storage/backups/` de
`.gitignore`'a alındı (`d9e621a`).

## Sırada / Next
- **Yeni chat:** önce hardening deploy adımları 1–4 (kullanıcı komutları çalıştırır
  veya izin verir), sonra `PLAN-analytics-insights.md` §10 kararları alınır, §7
  şüpheleri doğrulanır, ardından §9 commit sırası (1→8) uygulanır, en son §8 sıfırlama.
- Parmak izi 32 bit basit hash; aynı model iPhone'lar çakışabilir, birleştirme
  mantığı gözden geçirilebilir (insights planı kapsamı dışında, ayrı karar).
- **Floyd'da gerçek sıraya ekleme testi** yapılmadı: Floyd'un Spotify'ında
  aktif cihaz çalarken menüden bir şarkı isteği gönderilip butona
  basılmalı. (Görükle'de doğrulandı, Floyd'da değil.)
- **Adminlere yönerge**: Telegram grubundaki diğer adminler için tek
  sayfalık kullanım açıklaması hazırlanması konuşuldu, henüz yazılmadı.
- **Repo dışı kurulum**: `/etc/systemd/system/qr-menu-queue.service` ve
  `qr-menu-telegram.service` ile `.env`'deki `GEMINI_*` / `TELEGRAM_*`
  değerleri git'te değil. Sunucu yeniden kurulursa elle oluşturulmalı.
- Önceki oturumdan devreden sorular hâlâ açık: business_logic.md'deki
  (1) analytics için "iş günü" sınırı, (2) aynı üründe çakışan kampanya
  kuralı; ayrıca `CampaignType::COLLECTIVE`'in README'de eksik olması.
- `prompt_patterns.md`'deki "birden fazla kez düzeltilen konular" bölümü
  hâlâ boş.

## Açık Sorular / Blockers
- **Insights planı §10 kararları** (müzik etkisi sütunu, ayrı Davranış sayfası,
  ürün detay süresi 600 sn kırpma, tam vs yumuşak sıfırlama, Floyd review URL'i).
- **Deploy izni:** `migrate --force`, `optimize`, `filament:optimize`, crontab
  komutları auto-mode sınıflandırıcısı tarafından "Production Deploy" diye
  engellendi; kullanıcı elle çalıştıracak ya da izin verecek.
- **Telegram grubundan şarkı isteği alınsın mı?** Kullanıcı gruba "massive
  attack çal" yazdı ve bir şey olmadı; sistem grup mesajlarını okumuyor
  (tasarım gereği, istekler yalnızca menüden gelir). Gruptan da istek
  kabul edilsin mi, karara bağlanmadı.
- **Sunucuda 8 adet eski Claude oturumu** ~1.5 GB RAM tutuyor, swap 1.2 GB.
  Kullanıcı bunları kapatmayı şimdilik istemedi; yoğun saatte yavaşlık
  tekrarlarsa ilk buraya bakılmalı.
- Devreden: iş günü sınırı, kampanya çakışma kuralı, `COLLECTIVE` tipi.
