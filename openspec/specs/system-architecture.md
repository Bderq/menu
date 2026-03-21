# System Architecture

## Genel Bakış (Overview)
Sistem, modern ve estetik bir dijital "QR Menü" altyapısı sunmak üzere tasarlanmış **Laravel 12 (Backend)** ve **React 19 / Inertia.js 2 (Frontend)** yığını üzerinde koşmaktadır. Görselleştirme tarafında **Tailwind CSS v4** ve **Framer Motion v12** kullanılarak "Premium" bir kullanıcı deneyimi hedeflenmiştir. Tek bir uygulama üzerinden çoklu şube (multi-store) yönetilebilmektedir.

## Temel Bileşenler (Core Components)

### 1. Şube Yönetimi (Store Management)
Sistem URL tabanlı (`/menu/{store_slug}`) çalışır. Her şube:
- Kendine ait menü renklerine (theme_color) ve logolara sahiptir.
- Master ürün listesinden kendi sattığı ürünleri ve porsiyonları seçerek fiyat veya isim/açıklama override (üzerine yazma) işlemi yapabilir. (`store_products` ve `store_product_portions` tabloları).

### 2. Katalog ve Ürün Hiyerarşisi
- **Kategoriler:** `parent_id` kullanılarak sonsuz derinlikte alt kategori yapısı oluşturulabilir. Mevcut yapıda "Ana Kategori (Yiyecek) > Odak Kategori (Tatlı) > Ürünler" şeklinde 3 katmanlı dizilim kullanılmaktadır.
- **Ürünler (Products):** Global ve benzersiz ana kayıttır.
- **Porsiyonlar (Portions):** Şube bazlı dinamik fiyatlandırılan ürün seçenekleridir (Örn: "33cl", "50cl", "Büyük Boy"). 

### 3. Dinamik Kampanya Motoru (Campaign Engine)
Uygulamanın en karmaşık sistemlerinden biri olan `CampaignService`, şu özelliklere sahiptir:
- **Tipler:** *Fixed Price*, *Percentage*, *Bundle*, *X get Y* (4 temel kurgu).
- **Zamanlama (Schedules):** Belirli gün ve saat aralıklarında otomatik aktifleşme. Ertesi güne sarkan (Overnight) gece kampanyası hesaplamaları.
- **Uygulama Alanı:** Şube, spesifik ürün veya spesifik bir porsiyon bazlı hedefleme yeteneği.

### 4. Routing
Bütün menü verisi tek bir `MenuController@index` endpoint'i üzerinden işlenip Inertia ile Frontend'e aktarılır. Menü hiyerarşisi, en çok satanlar (Best Sellers) ve aktif dinamik kampanyalar bu endpoint'te hesaplanarak JSON tarzında prop olarak beslenir.

## Backend Geliştirme Standartları (Anayasa)
Bu projede kod bütünlüğünü ve hızı korumak adına uygulanması zorunlu olan ve yapay zekanın (AI) her kodlamada baz alacağı kurallar:

- **Rule 1: No string-based matching for database relations.** Veritabanı ilişkileri için (Örn: `campaign_items`'daki `portion_name` yerine `store_product_portion_id`) ASLA metin (string) eşleşmesi yapılamaz. Daima ID tabanlı Foreign Key kullanılmalıdır.
- **Rule 2: Always use Eager Loading (`with()`) to prevent N+1 issues in Controllers.** Controller ve view tarafında (Örn: Model çağrıları etrafındaki döngülerde) DB call oluşmaması için relations baştan Eager Load (ön-yükleme) edilmelidir.
- **Rule 3: Business logic must reside in `Services/`, not in Controllers.** "Fat Controller" anti-pattern'ı yasaktır. Controller sadece Request alıp metot paslar. Karmaşık data formatlamaları, hesaplamalar ve kampanyalar mutlaka Servis sınıflarında (`MenuService`, `CampaignService`) yer almalıdır.
- **Rule 4: Use PHP Enums for fixed types.** Tip güvenliği için; kod içerisinde dağılmış `'food'`, `'drink'`, `'bundle'`, `'fixed_price'` gibi Magic String'ler yerine mutlaka güncel PHP (`App\Enums\...`) özellikleri kullanılacaktır.
