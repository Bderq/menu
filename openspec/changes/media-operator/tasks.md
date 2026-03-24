# Media Operator Implementation Tasks

Bu görevler, 100+ ürünü tek bir ekrandan görsel olarak denetlemenizi ve güncellemenizi sağlayacak "Medya Operasyon" sayfasını oluşturmayı hedefler.

## 1. Alt Yapı ve Sayfa Oluşturma
- [x] Yeni bir Filament Resource oluşturun: `MediaHubResource`.
    - Tablo: `products` tablosunu hedef alacak.
    - Navigation Label: "Medya Operasyon".
    - Navigation Icon: `heroicon-o-camera`.
- [x] Bu resource'da "Create" ve "Edit" sayfalarını devre dışı bırakın (Sadece Liste görünümü).

## 2. Tablo Tasarımı (ListTable)
- [x] **Ürün Sütunu:** Marka/Ürün adını gösteren aratılabilir sütun.
- [x] **Thumbnail Sütunu:** (Sürükle-Bırak)
    - Doğrudan tablo satırına sürükle-bırak (modal'sız) desteği eklendi.
    - `image_path` alanını hedefleyin.
    - Smart Binding kuralını (`slug.webp`) buraya da uygulayın.
- [x] **Kart Görseli Sütunu:** (Sürükle-Bırak)
    - `gallery` alanının çoklu görsel desteği eklendi.
    - Doğrudan tablo satırına sürükle-bırak desteği eklendi.
- [x] **Durum Sütunu (Badge):**
    - `image_path` ve `gallery` null ise "🚨 Eksik"
    - Sadece biri varsa "⚠️ Kısmi"
    - İkisi de doluysa "✅ Hazır" gösteren bir Badge sütunu.

## 3. Akıllı Filtreler ve Sıralama
- [x] "Görseli Olmayanlar" adında bir tablo filtresi ekleyin.
- [x] "Kategori" bazlı filtreleme ekleyin.

## 4. Kullanıcı Deneyimi (UX)
- [x] Tabloyu "Contained" (Sıkışık) moddan "Full Width" moda alarak daha fazla görselin yan yana görünmesini sağlayın.
- [x] Fotoğraflar yüklendiğinde anında önizleme (preview) gösterilmesini sağlayın.
- [x] **Intervention Image v3** ile arka planda otomatik WebP dönüşümü ve boyutlandırma eklendi.

## 5. Doğrulama
- [x] "Medya Operasyon" sayfasına gidin.
- [x] Bir ürünün thumbnail sütununa tıklayıp veya sürükleyip fotoğraf yükleyin.
- [x] Sayfa yenilenmeden fotoğrafın oraya geldiğini ve `Storage` içinde doğru isimle (`slug.webp`) oluştuğunu teyit edin.
