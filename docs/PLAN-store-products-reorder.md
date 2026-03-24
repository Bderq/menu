# Plan: Store Products Reorder (Drag & Drop)

## Gelişme Amacı
Kullanıcının mağaza ürünlerini, özellikle kategori filtrelemeleri yapıldığında Filament arayüzü üzerinden sürükle-bırak (drag & drop) yöntemiyle kolaylıkla sıralayabilmesi; değişikliğin "canlı" olarak pivot tabloya yansıtılması fakat veritabanını yormayacak şekilde optimize edilmesi.

## Kararlar ve Kapsam

1. **Sınırlandırılmış Reordering:** Kullanıcılar sadece spesifik kategori filtrelemeleri / Tab seçimleri uygulandıktan sonra elde edilen "doğal ve kısa liste" üzerinde sıralama yapmak istiyor.
2. **Sayfalamanın Etkisi:** Bu filtreler uygulanınca liste kısaldığı için sayfalamanın oluşturduğu sıralama engelinden doğal yolla kurtulmuş olunacak. 
3. **Canlı Güncelleme:** Liste elemanı bırakıldığı anda arka planda Filament Reorder yeteneği (Livewire) çalışacak. Bu yapı, binlerce veriyi taramak yerine sadece o listedeki/filtredeki elemanların **pivot tablosunu** (`store_id` eşleşmesini referans alarak) optimize bir sıraya dizecek.

## Aşama 1: Filament Altyapısı (Reorder Özelliğini Aktif Etme)

- [ ] 1.1 `StoreProducts` listesine `reorderable` özelliğini ekle.
- [ ] 1.2 Pivot tablosu üzerinden sıra güncellemeyi (`sort_order`) sağlamak için Filament'in `reorderable()` yeteneğini özelleştir (Eğer varsayılan Eloquent Builder direkt table column'u bulamazsa, pivot'a manuel müdahale fonksiyonu yazılmalı).
- [ ] 1.3 `sort_order` kolonunu default sıralama kriteri yap (`defaultSort('store_product.sort_order')` ya da muadili query ayarı ile).

## Aşama 2: Filtrelenmiş Liste Kontrolü

- [ ] 2.1 Reordering'i, kullanıcı bir kategori filtresi seçtiğinde (veyahut Tab ile alt listeye geçtiğinde) aktif olacak hale getir. Sayfa hiç filtrelenmemişse (Tüm menüyü gösteriyorsa), binlerce kaydın yanlış reorder olmaması için Reorder iconunu gizle veya engelle.

## Aşama 3: Optimizasyon ve Canlı Güncelleme

- [ ] 3.1 Livewire arka plan isteği çalıştığında sadece o an sürüklenen elemanların ve aralarındakilerin `sort_order` indexlerinin pivot tablosunda güncellenmesini sağla (Filament'in built-in mekanizması genelde performanslıdır, profiler ile kontrol edilecek).
- [ ] 3.2 Mevcut durumda `TextInputColumn` olarak duran `sort_order` kutucuğunu tamamen sistemden kaldır (kötü UX) ve yerine sadece listeyi sürükleme imkanı tanı.

## Görev Atamaları
- **`backend-specialist`:** Pivot tablosunda Filament'in sıralama yeteneğini (reorderable callbacks) stabil ve optimize hale getirmek.
- **`frontend-specialist`:** Filament'in reorder handle (sürükle bırak) ikonlarının UI ile uyumunu test edip, önceki sort_order inputlarını kaldırmak.
- **`tester (webapp-testing)`:** Performansı (veritabanı sorgusu adeti) test etmek ve `reorder` işleminin doğru şubede kaydedildiğini doğrulamak.
