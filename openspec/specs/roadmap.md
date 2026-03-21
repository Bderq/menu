# QR Menü Geliştirme Yol Haritası (Roadmap)

Bu belge, projenin mevcut yapısını temel alarak üst düzey "Premium, estetik ve hızlı bir QR Menü" sistemine ulaştırmak için takip edilecek yol haritasıdır. Proje bir ERP (Sipariş, Stok, POS) sistemine dönüşmeyecek; yalnızca mükemmel kullanıcı deneyimine odaklanan, tasarım harikası dijital kataloglar sunacaktır.

## 1. Kritik Eksikler (Mimari ve Performans)

Mevcut yapının stabilizasyonunu artırmak ve QR Menü'nün daha modüler hale gelmesini sağlamak için öncelikli adımlar.

- **[x] Kampanya ve Porsiyon İlişkisi Refaktörü:** `CampaignItem` tablosu string bazlı eşleşmeden ID bazlı ilişkiye (`store_product_portions.id`) geçirildi. Kod içindeki `str_contains` kontrolleri kaldırıldı. (Tamamlandı).
- **[ ] Controller Optimizasyonu (Eager Loading):** QR Menü açılış hızının (Time to First Byte - TTFB) yüksek olması zorunludur. `MenuController@index` içindeki çok sayıda DB sorgusunun ve N+1 problemlerinin `with()` kurgusuyla "Service Pattern" çatısı altında sadeleştirilmesi gerekmektedir.
- **[ ] Type ve Enum Geçişleri:** Hardcoded string ("food", "drink", "bundle") kontrollerinin PHP 8 Enum sınıflarına aktarılarak arka uç güvenliğinin ve geliştirilebilirliğin artırılması.


## 2. UI/UX İyileştirmeleri (Premium ve Brutalist Tasarım)

Bir QR Menü'nün kalbi estetik tasarımdır. Arayüzün "Minimalist Tech" ve yüksek kontrastlı vizyonunun tamamlanmasına yönelik adımlar.

- **[ ] İleri Seviye Skeleton Loading:** Ürünler ve resimler yüklenirken boş sayfa veya Spinner yerine, uygulamanın Premium hissiyatına yakışır yüksek kaliteli "Skeleton Screen" iskelet yükleyicilerin entegre edilmesi.
- **[ ] Animasyon & Mikro-etkileşim (Framer Motion vb.):** Kullanıcının menüde gezindiği, kategori seçtiği veya kampanya detayına baktığı esnada "60 FPS" pürüzsüz geçiş animasyonları (Page transitions, scroll-snap) kullanılması.
- **[ ] Resim Optimizasyonları (Lazy Loading & WebP):** Ürün fotoğraflarının ve banner'ların telefonları yormadan yüklenmesi için Next-Gen formatların (WebP/AVIF) kullanımı. (Gerekirse "Blurhash" gibi düşük çözünürlüklü placeholder görsellerle birleştirilmesi).
- **[ ] Sürükleyici Kategori Navigasyonu:** Sol taraftaki sidebar (Drawer) mantığının elden geçirilmesi, scroll yönüne göre küçülen Header yapıları ile içeriğe (ürünlere) maksimum alan ayrılması.
