# Current Task

## Şu An / Now
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
- **Telegram grubundan şarkı isteği alınsın mı?** Kullanıcı gruba "massive
  attack çal" yazdı ve bir şey olmadı; sistem grup mesajlarını okumuyor
  (tasarım gereği, istekler yalnızca menüden gelir). Gruptan da istek
  kabul edilsin mi, karara bağlanmadı.
- **Sunucuda 8 adet eski Claude oturumu** ~1.5 GB RAM tutuyor, swap 1.2 GB.
  Kullanıcı bunları kapatmayı şimdilik istemedi; yoğun saatte yavaşlık
  tekrarlarsa ilk buraya bakılmalı.
- Devreden: iş günü sınırı, kampanya çakışma kuralı, `COLLECTIVE` tipi.
