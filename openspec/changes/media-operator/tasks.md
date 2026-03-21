# Media Operator Implementation Tasks

Bu görevler, 100+ ürünü tek bir ekrandan görsel olarak denetlemenizi ve güncellemenizi sağlayacak "Medya Operasyon" sayfasını oluşturmayı hedefler.

## 1. Alt Yapı ve Sayfa Oluşturma
- [ ] Yeni bir Filament Resource oluşturun: `MediaHubResource`.
    - Tablo: `products` tablosunu hedef alacak.
    - Navigation Label: "Medya Operasyon".
    - Navigation Icon: `heroicon-o-camera`.
- [ ] Bu resource'da "Create" ve "Edit" sayfalarını devre dışı bırakın (Sadece Liste görünümü).

## 2. Tablo Tasarımı (ListTable)
- [ ] **Ürün Sütunu:** Marka/Ürün adını gösteren aratılabilir sütun.
- [ ] **Thumbnail Sütunu:** 
    - `FileUpload` bileşenini tablo içine (`Editable`) yerleştirin.
    - `image_path` alanını hedefleyin.
    - Smart Binding kuralını (`slug.webp`) buraya da uygulayın.
- [ ] **Kart Görseli Sütunu:**
    - `gallery` alanının ilk elemanını veya tekil kontrolünü sağlayın.
    - `FileUpload` olarak tablo içine yerleştirin.
- [ ] **Durum Sütunu (Badge):**
    - `image_path` ve `gallery` null ise "🚨 Eksik"
    - Sadece biri varsa "⚠️ Kısmi"
    - İkisi de doluysa "✅ Hazır" gösteren bir Badge sütunu.

## 3. Akıllı Filtreler ve Sıralama
- [ ] "Görseli Olmayanlar" adında bir tablo filtresi ekleyin.
- [ ] "Kategori" bazlı filtreleme ekleyin.

## 4. Kullanıcı Deneyimi (UX)
- [ ] Tabloyu "Contained" (Sıkışık) moddan "Full Width" moda alarak daha fazla görselin yan yana görünmesini sağlayın.
- [ ] Fotoğraflar yüklendiğinde anında önizleme (preview) gösterilmesini sağlayın.

## 5. Doğrulama
- [ ] "Medya Operasyon" sayfasına gidin.
- [ ] Bir ürünün thumbnail sütununa tıklayıp fotoğraf yükleyin.
- [ ] Sayfa yenilenmeden fotoğrafın oraya geldiğini ve `Storage` içinde doğru isimle (`slug.webp`) oluştuğunu teyit edin.
