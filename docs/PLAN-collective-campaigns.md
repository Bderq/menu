# Kolektif (Kademeli) Kampanya Tipi Geliştirme Planı

## Amaç
QR menü sistemine "Kolektif" (Collective / Tiered Pricing) adında yeni bir kampanya türü eklemek. Bu tür, kullanıcıların çoklu alımlarda (örn: 4'lü, 8'li, 12'li) birim başına daha uygun fiyatlar görmesini sağlayacak ve kampanya galerisindeki karmaşayı azaltıp ürünleri gruplandıracaktır.

## Sokratik Kapı (Cevaplanması Gereken Sorular / Tasarım Kararları)
Uygulamaya geçmeden önce şu detayları netleştirmeliyiz:
1.  **Satış Mantığı:** Veritabanında kademeleri tutarken "Birim Fiyat" üzerinden mi (1 Adet 145 TL) yoksa "Toplam Paket Fiyatı" üzerinden mi (4 Adet 580 TL) tutalım? (Adet x Fiyat otomatik hesaplanabilir, admin panelinde hangisini girmek size daha kolay gelir?)
2.  **Önceliklendirme:** "Kolektif" paketler her zaman normal fiyattan daha mı öncelikli olacak? Başka bir kampanya (Örn: Happy Hour %10 İndirim) ile Kolektif paket çakışırsa hangisi geçerli olmalı?
3.  **Görünüm Kararı:** Kampanya galerisinde karta tıklandığında alt taraftan açılan çekmecede (drawer) bu paketleri alt alta listeleyeceğiz. Sepete ekleme (sipariş) modülü olmadığı için, görsel olarak salt bilgilendirici, "fatura" veya "fiyat listesi" tarzında bir yapı kurmamız uygun mudur?

---

## Faz 1: Veritabanı ve Model Güncellemeleri
- [ ] `campaigns` tablosuna `tiers` adında `json` tipinde yeni bir sütun eklemek için migration oluşturulacak.
- [ ] `App\Models\Campaign` modelinde `tiers` sütunu `array` olarak cast edilecek.
- [ ] `App\Enums\CampaignType` enum sınıfına `COLLECTIVE = 'collective'` (veya `TIERED`) eklenecek.

## Faz 2: Filament Admin Paneli Entegrasyonu
- [ ] `CampaignResource` güncellenecek.
- [ ] Kampanya tipi `COLLECTIVE` seçildiğinde `value` veya `buy_qty` gibi alanlar gizlenecek.
- [ ] Bunun yerine `tiers` alanını yönetecek bir **Repeater** (Tekrarlayıcı) bileşeni eklenecek.
  - Bileşen alanları: `Adet (Quantity)` ve `Fiyat (Price)`.
  - Kullanıcı istediği kadar kademe (Örn: 4, 8, 12) ekleyebilecek.

## Faz 3: Backend Mantığı (Servisler)
- [ ] `CampaignService::applyDiscountToProduct` metodunda `COLLECTIVE` tipindeki kampanyalar için yeni bir iş mantığı yazılacak.
- [ ] `campaign_price` yerine, frontend'e iletilmek üzere `collective_tiers` adında yeni bir dizi ürün (product/option) nesnesine eklenecek.
- [ ] Düşük olan fiyat başlangıç fiyatı olarak "135₺'den başlayan fiyatlarla" mantığı için eklenecek.

## Faz 4: Frontend (Arayüz) Güncellemeleri
- [ ] `DefaultCampaignCard.jsx` güncellenerek Kolektif türündeki kampanyalar için özel bir görsel kart oluşturulacak. (Büyük "KOLEKTİF" etiketi)
- [ ] `DefaultCampaignCard` içerisindeki açılır pencerede (drawer) kademeleri liste tarzında (4'lü: 580TL vb.) listeleyen yeni bir bölüm (bilet/fiyat tablosu) yazılacak.
- [ ] `Index.jsx` içerisindeki ürün detay penceresinde, ürünün "Kolektif" kampanyası varsa fiyat blokları yerine adet/fiyat matrisini gösteren şık bir UI eklenecek.

## Doğrulama / Test Adımları (Checklist)
- [ ] Yeni kampanya türü Admin panelinden sorunsuz eklenebiliyor mu?
- [ ] Sınırsız sayıda kademe (tier) eklenebiliyor ve JSON olarak kaydedilebiliyor mu?
- [ ] Backend servisi, ürünlere bu kademeleri (tiers) doğru şekilde iliştiriyor mu?
- [ ] Arayüzde (Galeride ve Detayda) kademe listesi tasarım diline (grung/brutalist) uygun görünüyor mu?
- [ ] Birden fazla ürün (Hem Efes, hem Beck's) tek bir Kolektif paketin içine aktarıldığında fiyatlar karışıyor mu?
