## Context

QR Menü uygulaması için Maestro / Admin panelinde, mağaza bazlı ürün gösterim ve düzenlemesi (`/admin/products/store/{id}`) sayfasında, kullanıcının menüdeki ürün sıralamalarını elle (manuel input) vermek zor gelmeye başlamıştır. Günümüz UX standartları (sürükle bırak) kullanılması için Filament'in özelliklerinin bu pivot tablo destekli sayfaya yansıtılması gerekmektedir. Fakat veritabanında çok fazla ürün olduğu için hepsini tek seferde reorder etmek hataya açık olacaktır; bu sebeple sıralama sadece filtreli sonuçlar (Kategori vs.) üzerinde aktiftir.

## Goals / Non-Goals

**Goals:**
- Filament üzerindeki tabloya Drag&Drop sıralama (`reorderable()`) yeteneği kazandırılması.
- Kategori filtresi uygulandığında (böylece ürün listesi makul bir boyuta düştüğünde) sıralama işleminin açılması.
- Yapılan drag-drop değişikliklerinin `store_product` pivot tablosuna optimize bir sorguyla güncellenmesi.
- Tablodaki gereksiz manuel "Sıra" (sort_order text input) alanının kaldırılması.

**Non-Goals:**
- Ürünlerin Global (Kütüphane) sıralamasının değiştirilmesi (Sadece belirli bir mağazanın kataloğundaki sıralama etkilenecektir).

## Decisions

- **Reorder Kısıtlaması:** Tablo, default olarak her zaman reordering desteklemeyecek. Liste çok uzun olursa sayfalama (pagination) problemi olur, drag-drop işlemi kopabilir. Bu yüzden `reorderable(fn() => return filtre_secilmismi)` gibi mantık veya Filament'in sağladığı best-practice reorder işlevi kullanılacak.
- **Pivot Query Optimization:** Filament'in build-in `reorderable` yapısı çoğu zaman direkt asıl mode (Product) üzerinden işlem yapar. Burada pivot üzerinden (store_product tablosunda) sort_order'ı güncelleyeceğimiz için table'a manuel bir kaydetme mantığı kurmak (`reorderRecordsUsing(...)`) gerekecektir.

## Risks / Trade-offs

- Filtre kaldırılıp "Tümü" seçildiğinde reorder kapalı olacaktır, bu durum kullanıcının kafasını karıştırabileceği için table header'ına ya da boş durumlara yönlendirici bir uyarı (Örn: "Sıralama için bir kategori seçin") konmalıdır.
- Kullanıcı sürükle-bırak işlemini hızlıca ardı ardına yaparsa veritabanına yoğun (arka arkaya ajax) yük bindirilebilir; bu nedenle Filament'in dahili reorder throttling/event'lerine güveneceğiz.
