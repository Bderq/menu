# 🎯 Campaign System Specification (Kampanya Motoru v1.0)

## 📋 Genel Bakış
"Street Pub" için tasarlanan bu sistem, sadece statik bir indirim listesi değil, zaman ve kural tabanlı çalışan dinamik bir **Kampanya Motoru**dur. Sistem, ürünlerin standart fiyatlarını anlık olarak manipüle edebilir ve kullanıcıya bir "fırsat" hissi aşılar.

---

## 🛠️ Teknik Şablonlar (Campaign Types)

Sistem aşağıdaki 4 ana kampanya tipini destekler:

1.  **fixed_price (Sabit Fiyat / Happy Hour):** Belirli ürünlerin belirlenen saatler arasında sabit fiyattan satılması.
    *   *Örn:* 19:00'a kadar tüm Efes Fıçı 90 TL.
2.  **percentage (Yüzde İndirim %):** Belirli kapsamdaki ürünlerde uygulanan oranlı indirim.
    *   *Örn:* Kokteyllerde Perşembe günü %25 indirim.
3.  **bundle (Paket Menü):** Bir veya birden fazla ürünün birleşimiyle oluşan sabit fiyatlı menüler.
    *   *Örn:* Burger + Bira + Patates = 450 TL.
4.  **x_get_y (X Al Y Öde):** Belirli bir sayıya ulaşınca tetiklenen promosyonlar.
    *   *Örn:* 6 Shot al, 5 öde. 1 Pizza alana 1 bedava.

---

## 🕒 Kurallar ve Kısıtlamalar (Conditions)

Her kampanya şu kısıtlamalara sahip olabilir:
- **Zaman (Time):** Başlangıç ve bitiş saatleri (örn: 12:00 - 19:00).
- **Günler (Days):** Haftanın hangi günlerinde geçerli (Pazartesi, Salı vb.).
- **Şube (Branch):** Kampanyanın hangi şubelerde (Store) aktif olduğu.
- **Kapsam (Scope):** Tekil ürünler veya ürünlerin belirli porsiyonları (örn: Sadece 50cl olanlar).

---

## 🎨 Gösterim Mantığı (Display Strategy)

### 1. Kampanyalar Sekmesi (Main Tab)
- **Her Zaman Görünür:** Aktif olsun veya olmasın tüm kampanyalar listelenir.
- **Sıralama:** O an saat ve gün kuralına göre **Aktif** olanlar en üstte, parlak renkli ve "ŞU AN AKTİF" rozetiyle görünür.
- **Gelecek Kampanyalar:** Alt sırada, daha soluk renkte ve "Saat 18:00'de Başlıyor" gibi bilgilendirmelerle yer alır.

### 2. Ürün Kartları (İçecek/Yiyecek Sekmeleri)
- Ürün eğer aktif bir kampanyaya dahilse:
    - Standart fiyatın üzeri çizilir.
    - Yanına parlak bir **"Kampanya Fiyatı Sticker'ı"** (Price Stack) eklenir.
    - Sticker üzerinde `display_title` (örn: Günün Kıyağı, Happy Hour) yer alır.

---

## 🗄️ Veritabanı Yapısı (Current Schema)

### `campaigns`
- `id`, `name` (admin), `display_title` (label), `description`, `image_path`
- `type` (fixed_price, percentage, bundle, x_get_y)
- `value` (indirim oranı veya sabit fiyat değeri)
- `buy_qty` / `get_qty` (X al Y öde kurgusu için)
- `priority` (Çakışan kampanyalar için öncelik sırası)
- `is_active` (Genel anahtar)

### `campaign_schedules`
- `campaign_id`, `day_of_week`, `start_time`, `end_time`

### `campaign_items` (Hedefleme ve Kapsam)
- `campaign_id`, `product_id`
- `portion_name` (Opsiyonel - örn: "50cl". Boş bırakılırsa tüm porsiyonlara uygulanır)
- `price_override` (Opsiyonel - kampanya bazlı özel fiyat)
- `is_optional` (Bundle içi seçimler için)

### `campaign_store` (Şube İlişkisi)
- `campaign_id`, `store_id`, `is_active`

---

## 🚀 Durum ve Uygulama
1.  ✅ Veritabanı migrationları tamamlandı.
2.  ✅ Filament Admin Resource (`CampaignResource`) geliştirildi.
3.  ✅ `CampaignService` (Backend logic) fiyat hesaplama motoru hazır.
4.  ✅ Frontend (React) kampanya sticker ve badge tasarımları entegre edildi.

