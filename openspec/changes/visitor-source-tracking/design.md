## Context
Kullanıcının QR kodları basılı ve değiştirilemez durumdadır. Ancak şube bazlı girişler farklı domainlerden veya eski site alt klasörlerinden yönlendirilmektedir. Bu yapı, `Referer` başlığı üzerinden kaynak tespiti yapmamıza olanak tanır.

## Goals / Non-Goals

**Goals:**
- Her oturumun (Visit) hangi domainden geldiğini tespit etmek.
- UTM parametrelerini (özellikle `utm_source`) yakalamak.
- Filament üzerinde kaynak dağılımını raporlamak.

**Non-Goals:**
- Fiziksel QR kodlarının URL yapısını değiştirmek.
- Kullanıcı bazlı detaylı web tracking (sayfa bazlı tam yol takibi bu tasarımın odağı değildir).

## Decisions

- **Decision 1: Referer Host Temizliği**: Tam Referer URL'i yerine sadece host (domain) bilgisi tutulacak (Örn: `https://l.instagram.com/p/xxx` -> `instagram.com`). Bu, raporlamada gruplamayı kolaylaştırır.
- **Decision 2: NULL Handling**: Referer yoksa "Direct / QR Scan" olarak kabul edilecek ve DB'de null bırakılıp raporlama katmanında isimlendirilecek.
- **Decision 3: UTM Önceliği**: Eğer hem Referer hem de UTM varsa; Referer gerçek teknik kaynağı, UTM ise manual etiketlemeyi temsil eder. İkisi de ayrı kolonlarda tutulacaktır.

## Risks / Trade-offs

- **Risk: HTTP Referer Kaybı**: Redirect (yönlendirme) sırasında veya gizlilik ayarlarına bağlı olarak Referer başlığı kaybolabilir.
  - *Mitigation*: Bu durumlar "Direct" olarak sayılacaktır.
- **Risk: Veri Boyutu**: Çok fazla farklı referrer olması durumunda tablo şişebilir.
  - *Mitigation*: Sadece host bilgisi tutularak veri boyutu minimize edilecektir.
