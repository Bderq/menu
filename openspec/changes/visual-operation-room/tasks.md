# Visual Operation Room Implementation Tasks

Bu görevler, Filament'in standart kısıtlamalarından kurtulup, tamamen "Sürükle-Bırak" odaklı, özel bir medya yönetim odası inşa etmeyi hedefler.

## 1. Alt Yapı Hazırlığı
- [ ] Yeni bir Filament Custom Page oluşturun: `VisualMediaManager`.
- [ ] Sayfayı sol menüde "Görsel Operasyon Odası" adıyla kaydedin.
- [ ] Sayfa için özel bir Blade View (`visual-media-manager.blade.php`) hazırlayın.

## 2. Grid (Izgara) Tasarımı
- [ ] Ürünleri 4'lü veya 5'li kartlar halinde listeleyecek bir Livewire bileşeni kurun.
- [ ] Her kart üzerinde iki şeffaf "Dropzone" (Thumbnail ve Detay) alanı tanımlayın.
- [ ] Ürün adı ve mevcut görsel durumlarını (Eksik/Tam) kart üzerinde gösterin.

## 3. "Drop & Done" Logic (JS / AlpineJS)
- [ ] Tarayıcının dosyayı yeni sekmede açmasını engelleyen global "event preventer" kodunu ekleyin.
- [ ] AlpineJS kullanarak; dosya bir ürün kartının üzerine geldiğinde kartın parlamasını (hover state) sağlayın.
- [ ] Dosya bırakıldığı anda, ilgili Ürün ID'sini ve Görsel Tipini (kapak/galeri) Livewire tarafına gönderen asenkron akışı kurun.

## 4. Backend İşleme
- [ ] Gelen dosyayı `Storage`'a kaydeden ve eski `Smart Binding` kurallarını (`slug.webp`) uygulayan metodu yazın.
- [ ] `Product` modelini güncelleyin ve sayfayı yenilemeden (Livewire Refresh) yeni görseli kartta gösterin.

## 5. İnce Ayarlar (UX)
- [ ] Yükleme sırasında kartın üzerinde bir "Loading" animasyonu gösterin.
- [ ] Hatalı dosya tipinde (örn: PDF atıldığında) kullanıcıya sağ üstten uyarı verin.

## 6. Doğrulama
- [ ] Yeni sayfayı açın.
- [ ] Bilgisayarınızdan bir fotoğrafı tutup, **hiçbir yere tıklamadan** direkt bir ürün kartının üzerine bırakın.
- [ ] Fotoğrafın saniyeler içinde o kartın "Kapak" fotoğrafı olduğunu teyit edin.
