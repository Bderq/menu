# PLAN: Visitor Source & Referrer Tracking

## Context
Ziyaretçilerin QR menüye hangi kaynaklardan (Instagram, Google veya eski ana domain yönlendirmeleri) geldiğini tespit etmek ve raporlamak için gereken altyapının kurulması. Kullanıcının QR kodları sabit olduğu için, giriş yapılan URL ve HTTP Referer bilgileri kullanılarak kaynak ayrıştırması yapılacaktır.

## Phase 1: Veritabanı Geliştirmesi
*   **Görev 1:** `visits` tablosuna `referer_host` ve `utm_source` kolonlarını ekleyen migration oluşturulması.
    *   `referer_host`: (string, nullable) - Gelen domain adı (örn: instagram.com).
    *   `referer_url`: (text, nullable) - Gelen tam URL (opsiyonel, detaylı analiz için).
    *   `utm_source`: (string, nullable) - URL parametrelerinden gelen kaynak bilgisi.

## Phase 2: Analitik Kayıt Mantığı (Middleware)
*   **Görev 2:** `TrackVisitor` middleware'inin güncellenmesi.
    *   `$request->headers->get('referer')` bilgisinin yakalanması.
    *   Domain isminin `parse_url` ile temizlenerek (örn: `https://l.instagram.com/` -> `instagram.com`) kaydedilmesi.
    *   Sorgu parametrelerinden (Query params) `utm_source` kontrolü yapılıp varsa kaydedilmesi.
*   **Görev 3:** Eğer yönlendirme (redirect) üzerinden geliniyorsa ve Referer kayboluyorsa, ana sitelerdeki yönlendirme URL'lerine manuel birer parametre (örn: `?ref=old_site_qr`) eklenmesi önerisi (Bu görev yazılımsal değil, kullanıcıya bilgi amaçlıdır).

## Phase 3: Dashboard & Raporlama (Filament)
*   **Görev 4:** "Ziyaretçi Kaynakları" (Visitor Sources) isimli yeni bir Filament Widget (Trend chart veya Table) oluşturulması.
    *   Hangi sitelerden ne kadar trafik geldiğinin yüzdesel veya sayısal gösterilmesi.
    *   "Doğrudan/QR" (Referer yok) ve "Referanslar" ayrımının yapılması.

## Verification Checklist
- [ ] Yeni visit kayıtlarında `referer_host` kolonu doluyor mu?
- [ ] Instagram üzerinden tıklandığında `instagram.com` verisi kaydediliyor mu?
- [ ] Filament panelinde kaynakların dağılımı listeleniyor mu?
- [ ] Bot filtreleme (önceki görev) hala aktif çalışıyor mu ve bot referansları kaydedilmiyor mu?

## Agent Görev Dağılımı
- `@backend-specialist`: Migration ve Middleware logic güncellemesi.
- `@frontend-specialist`: Filament Widget/Table oluşturulması ve UI entegrasyonu.
