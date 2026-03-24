# Mevcut Sistem Durumu (Current State)

Bu belge, projenin mevcut teknoloji yığınını, veritabanı şemasını, çalışan ana modüllerini ve kod yapısında tespit edilen eksiklikleri özetlemektedir.

## 0. Teknoloji Yığını (Tech Stack)

Proje, 2026 standartlarında en güncel "Cutting-Edge" teknolojilerle inşa edilmiştir:

- **Backend:** Laravel 12.x (PHP 8.2+)
- **Admin Panel:** Filament v5.1+
- **Frontend Framework:** React 19.x & Inertia.js v2.0+
- **Styling:** Tailwind CSS v4.0+
- **Animations:** Framer Motion v12.x
- **Build Tool:** Vite v7.0+
- **Icons:** Lucide React

## 1. Veritabanı Şeması ve Tablolar Arası İlişkiler

Sistem şu anda temel olarak **Katalog (Menü)** ve **Kampanya** yönetimi üzerine kuruludur.

**Ana Tablolar ve İlişkiler:**

- **`stores` (Şubeler):**
  - Mağaza bilgilerini tutar (logo, slug, tema rengi vb.).
  - `store_products`, `store_product_portions` ve `campaign_store` tablosuna (1:N ve N:M) bağlıdır.

- **`categories` (Kategoriler):**
  - Hiyerarşik yapıdadır (`parent_id` ile kendi kendine referans).
  - **Filament Tree** eklentisi ile sürükle-bırak (drag-and-drop) hiyerarşi yönetimi desteği eklenmiştir.
  - `products` tablosuyla (1:N) ilişkilidir. Türü (örn: *food, drink, campaign*) tutulur.

- **`products` (Ürünler - Master Liste):**
  - Globale ait ürünlerin ana bilgilerini tutar.
  - Bir `Category`'ye (BelongsTo) aittir.
  - Özel durumlar için `store_products` ve `store_product_portions` üzerinden şubelere dağılır.

- **`store_products` (Şube Ürünleri Detayı):**
  - Şube-Ürün bazlı ara tablodur. (Pivot gibi çalışır ancak kendi Model'i vardır).
  - Ürünün o şubedeki aktifliği, öne çıkan olup olmadığı (`is_featured`), özel isim/açıklama/fiyatı buralarda tutulur.

- **`store_product_portions` (Şube Ürün Porsiyonları):**
  - Ürünlerin şube bazlı varyantlarını/odaklarını tutar (Örn: 33cl, 50cl).
  - Hem `store` hem de `product` tablolarına aittir.

- **Kampanya Sistemi:**
  - **`campaigns`:** Kampanya ana bilgileri (tip: bundle, x_get_y, vb., indirim değeri ve tarihleri).
  - **`campaign_schedules`:** Kampanyanın geçerli olduğu günler ve saat aralıkları. `campaigns` (1:N) ile bağlı.
  - **`campaign_items`:** Kampanyaya dahil ürünler. `product_id` FK ile bağlıdır ancak **porsiyon eşleşmesi string (`portion_name`)** olarak tutulur.
  - **`campaign_store`:** Hangi kampanyanın hangi şubede aktif olduğunu belirleyen pivot tablo.

---

## 2. Şu An Çalışan Ana Modüller

Mevcut projede Stok, Sipariş (Satış) veya Gelişmiş Raporlama modülleri **HENÜZ YOKTUR**. Sistem şu an sadece dinamik bir "Dijital Menü" olarak hizmet vermektedir. Çalışan modüller:

1. **Dijital Menü / Katalog Modülü:**
   - Kategorilerin çoklu ağaç yapısıyla (Ana > Grup > Alt) sergilenmesi.
   - Şubeye özel ürünlerin, opsiyonların (portions) ve fiyatların dinamik listelenmesi.
   - "Öne Çıkanlar" (En Çok Satanlar) yapısı.
2. **Kampanya (Promosyon) Modülü:**
   - Belirli şubelerde, günlerde ve saatlerde devreye giren dinamik fiyatlandırma motoru.
   - 4 Farklı Mekanizma: *Fixed Price*, *Percentage*, *Bundle*, *X get Y*.
   - Menü üzerinde kampanyaların indirimli fiyatlarının dinamik hesaplanıp (`MenuController` üzerinden) gösterilmesi.
    - **Medya Operasyon (Visual Audit Center):** 100+ ürünün görsel eksiklerini tek ekranda görme ve PC'den tablo satırına sürükle-bırak (Drag & Drop) yöntemiyle modal açmadan anında fotoğraf yükleme/güncelleme (WebP ve 800px otomatik optimizasyon dahil).
3. **Mağaza (Store) Yönetim Altyapısı (Public):**
   - URL tabanlı (`/menu/{store_slug}`) çoklu şube desteği ve şubeye özel katalog sunumu.

---

## 3. Kod Yapısındaki Eksiklikler ve Geliştirme Alanları

### A. Modelleme ve İlişki İyileştirmeleri
- **[x] Kampanya ve Porsiyon İlişkisi Refaktörü:** `CampaignItem` tablosu string bazlı eşleşmeden ID bazlı ilişkiye (`store_product_portions.id`) geçirildi. Kod içindeki `str_contains` kontrolleri kaldırıldı.
- **Kullanıcı & Sipariş Yapısı Yok:** Sistemde sadece Laravel'in varsayılan `User` Modeli (muhtemelen Adminler için) var. Müşteriler, Siparişler, Sepet veya Ödeme gibi bir `E-Ticaret / Satış` mimarisi henüz tasarlanmamış.

### B. Mimari Tasarım (Controller & N+1 Problemleri)
- **Fat Controller (Şişkin Controller):** `MenuController@index` metodu yaklaşık 150 satır. Veritabanı sorguları, çok katmanlı array dönüşümleri (`$formattedGroups`) ve iş mantığı (Best Sellers tespiti) aynı metodun içinde yer alıyor. Bu durumun `MenuService`, `CategoryService` gibi servis sınıflarına taşınması gerekir.
- **Ciddi N+1 Sorgu Problemi:** `MenuController` içindeki dongülerde çağırılan şu yapı:
  ```php
  $portions = \App\Models\StoreProductPortion::where('product_id', $product->id)
                ->where('store_id', $storeId)
                ->where('is_active', true)->get();
  ```
  Menüdeki her bir ürün için içeride tekrarlı sorgu atılmasına sebep oluyor (N+1 problemi). Ürün porsiyonlarının `with('portions')` şeklinde eager-load ile çekilmesi performansı ciddi oranda artıracaktır.

### C. Validasyon ve Hardcode Kullanımları
- Dinamik işleyiş yerine kod içi *hardcoded* mantık kontrolleri var (Örn: `$mainCat->type === 'campaign'` veya `$key = strtolower($mainCat->type); // 'drink' or 'food'`). Bunların Enum sınıfları üzerinden yönetilmesi genişletilebilirliği artırır.
- Çekilen id ve request'ler için `FormRequest` yapıları eksik. İleride açılacak API veya Admin Controller kısımlarında güçlü bir request validasyon mimarisi oturtulmalıdır.

---

## 4. Sunucu ve Geliştirme Altyapısı (Infrastructure & UI)
- **Dosya Yükleme Kapasitesi (Nginx & PHP):** Medya merkezinde devasa (15MB+) raw fotoğrafların rahatça sürüklenip bırakılabilmesi için Nginx `client_max_body_size`, PHP `upload_max_filesize` ve Livewire limitleri **200MB** seviyesine ayarlandı. Sunucu arkada bu dosyaları alıp anında 70-80 KB'lık `.webp` (800x800) formatına dönüştürerek mükemmel optimizasyon sağlamaktadır.
- **Z-Index ve Tailwind JIT Bypass:** Proje `npm run dev` ile canlı derleme yapılmadan çalıştırıldığı için, yeni oluşturulan arbitrary (özel) Tailwind sınıfları (Örn: `w-[100px]`, `z-[150]`) derlenemiyor ve tarayıcı tarafından yok sayılıyordu. Bu durum, özellikle **Müşteri Menüsü (Frontend)** üzerindeki "Ses Ver / Diyet Paneli"nin üst menü altında ezilmesine ve Media Hub'daki fotoğrafların devasa patlamasına yol açmıştı. Bu problemleri kökünden çözmek için kritik UI elementlerinin derinlikleri ve boyutlandırmaları doğrudan dinamik **Inline Style (`style={{ ... }}`)** metodolojisine geçirildi.
