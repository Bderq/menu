# Google Review Prompt Sistemi

## Hedef
2. ziyaretin 10. saniyesinde memnuniyet popup'ı göster. Evet → Google yorum sayfasına yönlendir. Hayır → Ses Ver drawer'ını aç.

---

## Kararlar (Tartışılmış)

| Konu | Karar |
|------|-------|
| Google URL | Her dükkan admin'den kendi URL'ini girer |
| Memnuniyet sorusu | Her dükkan admin'den kendi sorusunu yazar |
| Tetikleyici | 2. ziyaret, 10. saniye |
| Ses Ver | Hayır'da Ses Ver drawer'ı açılır |
| "Bir daha gösterme" | `localStorage` ile (`google_review_seen_{storeSlug}`) |
| Visit count kaynağı | Backend'den `MenuController` üzerinden prop olarak geçilir |
| Popup tasarımı | Aynı PollPopup design language (brutalist, bottom-right) |

---

## Görevler

### T1 — DB: Store tablosuna alan ekle
**Agent:** `backend-specialist`

- `google_review_url` (string, nullable) — Google yorum linki
- `google_review_question` (string, nullable) — "Bu akşam iyi geçti mi? 🍺" gibi

```bash
php artisan make:migration add_google_review_fields_to_stores_table
```

**Verify:** Migration çalışır, `stores` tablosunda iki yeni kolon görünür.

---

### T2 — Backend: MenuController'dan visit_count geçir
**Agent:** `backend-specialist`

`MenuController::index()` içinde, middleware zaten `tracking_visitor_id`'yi request'e bağlıyor.
Buna ek olarak bu visitor'ın bu store'a kaç ziyareti olduğunu say ve Inertia'ya geçir.

```php
// MenuController.php içinde eklenecek:
$visitCount = $visitorId
    ? \App\Models\Visit::where('visitor_id', $visitorId)
        ->where('store_id', $store->id)
        ->count()
    : 0;

// Inertia render'a ekle:
'visitCount' => $visitCount,
'store' => $store,  // google_review_url ve google_review_question da burada gelecek
```

**Verify:** Menü sayfasında `$page.props.visitCount` değeri 1 veya 2+ olarak gelir.

---

### T3 — Admin Panel: Store kaynağına alanları ekle
**Agent:** `backend-specialist`

Filament'teki Store Resource düzenleme formuna iki alan ekle.

**Verify:** Admin paneli Store düzenleme sayfasında "Google Business" bölümü görünür ve kaydedilebilir.

---

### T4 — Frontend: GoogleReviewPopup.jsx bileşeni oluştur
**Agent:** `frontend-specialist`

`resources/js/Components/GoogleReviewPopup.jsx` dosyasını oluştur.

**Props:** `storeSlug`, `visitCount`, `googleReviewUrl`, `googleReviewQuestion`, `onOpenSesVer`

**Mantık:**
1. `visitCount >= 2` değilse hiçbir şey render etme
2. `localStorage.getItem('google_review_seen_' + storeSlug)` varsa render etme
3. `useEffect` içinde `setTimeout(10000)` ile `isVisible = true` yap
4. Kapatma veya Google'a yönlendirme → localStorage'a `'1'` kaydet

**UI Akışı (2 aşama):**
- **Aşama 1 (Soru):** Dükkanın sorusu + ❤️ Evet / 😐 Hayır butonu
  - PollPopup ile aynı brutalist tasarım dili
- **Aşama 2 (Evet seçildi):** "Bizi Google'da değerlendirin!" mesajı + ⭐ buton (yeni sekmede açılır)
- **Hayır seçildi:** `onOpenSesVer()` çağırır, popup kapanır

**Verify:** Geliştirme ortamında `visitCount=2` prop'u ile görünür, Evet → Google linki açılır, Hayır → Ses Ver açılır.

---

### T5 — Frontend: Index.jsx'e bileşeni bağla
**Agent:** `frontend-specialist`

`resources/js/Pages/Menu/Index.jsx` içinde:

1. `GoogleReviewPopup` import et
2. `visitCount` ve `store.google_review_url` props'larını bileşene geçir
3. `onOpenSesVer` callback'i `MenuInteractionDrawer`'ın Ses Ver sekmesini açacak şekilde bağla

**Verify:** Menü sayfası hata vermeden açılır. Popup 10 sn sonra görünür (visitCount >= 2 iken).

---

## Dosya Değişiklik Haritası

| Dosya | İşlem |
|-------|-------|
| `database/migrations/xxxx_add_google_review_fields_to_stores_table.php` | YENİ |
| `app/Http/Controllers/MenuController.php` | DÜZENLE — `visitCount` ekle |
| `app/Filament/Resources/StoreResource.php` | DÜZENLE — form alanları |
| `resources/js/Components/GoogleReviewPopup.jsx` | YENİ |
| `resources/js/Pages/Menu/Index.jsx` | DÜZENLE — bileşen bağlama |

---

## Bağımlılık Sırası

```
T1 (Migration) → T2 (Controller) → T5 (Index.jsx bağlama)
                → T3 (Admin Panel) — T1 sonrası paralel
T4 (GoogleReviewPopup) — T2 sonrası paralel → T5
```

---

## Tamamlanma Kriterleri

- [ ] Admin panelinde her dükkan için Google linki ve soru girilebiliyor
- [ ] `visitCount` backend'den doğru geliyor (2+ için tetikleniyor)
- [ ] 10 sn sonra popup çıkıyor
- [ ] Evet → Google linki yeni sekmede açılıyor
- [ ] Hayır → Ses Ver sekmesi açılıyor
- [ ] Popup bir kez gösterildikten sonra bir daha çıkmıyor (localStorage)
- [ ] `google_review_url` veya `google_review_question` yoksa popup çıkmıyor

---

## Edge Cases

| Durum | Davranış |
|-------|----------|
| Google URL girilmemişse | Popup hiç gösterilmez |
| Soru girilmemişse | Popup hiç gösterilmez |
| Poll popup ile çakışma | GoogleReviewPopup `z-[199]` → Poll önce gösterilir |
| Ses Ver drawer zaten açıksa | Hayır → sadece Ses Ver tab'ına focus at |

---

## Phase X — Doğrulama

- [ ] `php artisan migrate` başarılı
- [ ] Admin panelde Store düzenleme çalışıyor
- [ ] `npm run build` başarılı
- [ ] Manuel test: 2. ziyaret simülasyonu → popup görünür
- [ ] Evet akışı → Google sekmesi açılır
- [ ] Hayır akışı → Ses Ver açılır
- [ ] Yenileme sonrası popup tekrar çıkmıyor
