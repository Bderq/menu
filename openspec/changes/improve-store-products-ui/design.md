## Context

Mevcut mağaza ürün listesi Yönetimi sayfası UI kısmı Filament Admin Panelinde ürün görsellerinin vs. fazla ağırlık kazandığı, kategori/alt kategori filtrelemelerinin zayıf olduğu, listelemenin dağınık olduğu bir görünüme sahiptir. Kullanıcının bir ürünü bulup onun stok ve satıştaki durumunu güncellemesi zaman almaktadır. Daha fazla ürünü tek seferde görüntüleyebilmek, profesyonel bir veri girişi deneyimi sunmak amacıyla kompakt, veri odaklı bir UI tasarımına geçilecektir.

## Goals / Non-Goals

**Goals:**
- Ürün tablosundaki `ImageColumn` vb. gereksiz satır boşluğu yaratan elemanların minimize edilmesi veya daha küçük bir yapıya geçirilmesi.
- Ana kategori -> Alt kategori hiyerarşisine uygun hiyerarşik Filtreleme yapılarının eklenmesi.
- Ürünlerin aktif/pasif (stok) durumlarının sayfadan (inline) tek tıklamayla ve en hızlı biçimde güncellenebilmesi.
- Gerekirse "Toggle" sütunları yanında tablodan porsiyon fiyatlarının da hızlıca görülebildiği çok sütunlu kompakt veri biçimine (Data-Driven Layout) ulaşılması.

**Non-Goals:**
- Sistemin genelindeki Master Ürün, veya Şube mimarisinin Backend logiğinin değişmesi (Sadece mevcut yapının gösterim UI ve UX tarafı değiştirilir).
- Web uygulamasının son kullanıcı (müşteri) Menü görünümünün değiştirilmesi. (Sadece Admin paneli `/admin/products/store/1` sayfası düzenlenir).

## Decisions

- **Table Layout Update:** `ImageColumn` `width(40)->square()` daha da ufaltılarak minimal ikonlara indirgenecek ya da sütun gizlenebilir hale getirilecek.
- **Improved Filtering:** Kategori filtresi ana kategori ve alt kategori ilişkisini dikkate alacak şekilde (Tree/Hierarchical yapısı) geliştirilecek. Kullanıcının bir üst kategoriyi seçmesiyle ona bağlı ürünleri bulması kolaylaştırılacak.
- **Stock Management Priority:** Toggle bileşenleri en sağa sıkıştırılmadan daha erişilebilir bir sütunda tutulacak ve sayfa yenilenmesini beklemeden hızlıca güncellenebilmesi sağlanacak.
- **Simplified UI:** Gruplama yapılarak dikey alanın bölünmesi engellenecek, filtreler üzerinden temiz bir liste sunulacak.
## Risks / Trade-offs

- Görsellerin çok ufalması, eğer ürün adları karışık/benzer ise adminin ürünü tanımakta zorlanmasına sebep olabilir.
- Çok yoğun tablolar mobil admin panel kullanımını zorlaştırabilir. Bu yüzden masaüstü (Data Entry çalışanı) hedeflenerek tasarım yapılacaktır, mobil tarafta scroll edilmesi normal karşılanacaktır.
