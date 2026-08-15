# Current Task

## Şu An / Now
Memento bellek mimarisi (Bderq/memento) qr-menu projesine kuruldu ve
proje-özel içerikle dolduruldu: `tech_stack.md`, `dev_guidelines.md`,
`brand_voice.md`, `business_logic.md` koddan otomatik dolduruldu ve
doğrulandı; `prompt_patterns.md` kullanıcı tercihleriyle (terse yanıt,
kod değişikliğinden önce plan+onay) dolduruldu. Upstream repo iki kez
kontrol edildi ve yeni eklenen `memento-reindex` ile `memento-close`
skill'leri projeye kuruldu. Şu an 4 skill kurulu: `memento-init`,
`memento-sync`, `memento-reindex`, `memento-close`. Bu oturumda qr-menu
kod tabanında henüz bir özellik/bugfix çalışması yapılmadı — sadece
memento kurulumu ve doğrulaması yapıldı.

## Sırada / Next
- `.memento/2_Knowledge/business_logic.md`'deki açık sorular kullanıcıya
  sorulup netleştirilmeli: (1) analytics/raporlama için özel bir "iş
  günü" sınırı var mı, (2) aynı üründe birden fazla kampanya çakışırsa
  hangisi kazanıyor (ilk eşleşen / en yüksek indirim / stacking)?
- `prompt_patterns.md`'deki "birden fazla kez düzeltilen konular" bölümü
  hâlâ boş — ileride tekrar eden düzeltmeler oldukça buraya eklenmeli.
- `app/Enums/CampaignType.php`'deki `COLLECTIVE` tipinin README'de
  dokümante edilmemiş olması kullanıcıya bildirildi; kasıtlı mı
  (yarım/iç özellik) yoksa README mi eksik, netleştirilmedi.
- Sıradaki gerçek iş: kullanıcının qr-menu üzerinde talep edeceği asıl
  özellik/bugfix görevi — henüz belirtilmedi.

## Açık Sorular / Blockers
- Yukarıdaki iki business-logic sorusu (iş günü sınırı, kampanya çakışma
  kuralı) kullanıcıdan yanıt bekliyor.
- `COLLECTIVE` kampanya tipinin README'de eksik olması netleştirilmeli.
