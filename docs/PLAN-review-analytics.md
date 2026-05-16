# PLAN: Google Review Prompt — Interaction Funnel Analytics

> **Type:** WEB (Full-Stack: Laravel + React/Inertia)
> **Goal:** Her Google Review Prompt etkileşimini veritabanında kayıt altına alarak; gösterim, kabul, ret, Google'a gidiş ve Ses Ver tamamlama oranlarını ölçmek.

---

## Overview

Şu anki sistemde popup davranışı yalnızca `localStorage` üzerinden yönetiliyor — sunucu tarafında hiçbir iz bırakmıyor. Bu plan; her etkileşim adımını (`showed → accepted → rejected → dismissed`) bir veritabanı kaydına dönüştürerek huni (funnel) analizini mümkün kılacak.

---

## Success Criteria

- [ ] Popup her gösterildiğinde veritabanına 1 satır düşer.
- [ ] Kullanıcı "Evet" dediğinde bu satır güncellenir.
- [ ] Google bağlantısına tıklandığında güncellenir.
- [ ] "Hayır" deyip Ses Ver'e yönlendiğinde güncellenir.
- [ ] Ses Ver formu başarıyla gönderildiğinde güncellenir.
- [ ] Admin panelinde dükkana özel özet istatistikler görüntülenir.

---

## Tech Stack

| Katman | Teknoloji | Neden |
|--------|-----------|-------|
| Veritabanı | PostgreSQL (mevcut) | Varolan altyapı |
| Migration | Laravel Migration | Schema yönetimi |
| Backend API | Laravel Controller (yeni endpoint) | Hafif, mevcut pattern ile uyumlu |
| Frontend | React (mevcut GoogleReviewPopup.jsx) | Tek nokta değişiklik |
| Admin Panel | Filament Widget | Mevcut admin altyapısı |

---

## Veritabanı Şeması

### Yeni Tablo: `google_review_interactions`

| Kolon | Tip | Açıklama |
|-------|-----|----------|
| `id` | bigint PK | - |
| `visitor_id` | bigint FK | `visitors` tablosuna |
| `store_id` | bigint FK | `stores` tablosuna |
| `status` | string | `showed`, `accepted`, `rejected`, `dismissed` |
| `google_redirected` | boolean | Google linkine tıklayıp tıklamadı |
| `feedback_submitted` | boolean | Ses Ver formunu gönderip göndermedi |
| `guest_message_id` | bigint FK nullable | İlgili `guest_messages` kaydı |
| `showed_at` | timestamp | Popup'ın açıldığı zaman |
| `responded_at` | timestamp nullable | Evet/Hayır'a basıldığı zaman |
| `created_at / updated_at` | timestamps | - |

---

## File Structure (Yeni / Değişecek Dosyalar)

```
app/
├── Http/Controllers/
│   └── GoogleReviewInteractionController.php  ← YENİ
├── Models/
│   └── GoogleReviewInteraction.php             ← YENİ
├── Filament/Widgets/
│   └── GoogleReviewStatsWidget.php             ← YENİ
database/
└── migrations/
    └── xxxx_create_google_review_interactions_table.php  ← YENİ
resources/js/Components/
└── GoogleReviewPopup.jsx                       ← GÜNCELLEME
routes/
└── web.php                                     ← GÜNCELLEME (yeni route'lar)
```

---

## Task Breakdown

### PHASE 0 — Veritabanı ve Model (P0: Blocker)

#### Task 1.1 — Migration oluştur
- **Agent:** `backend-specialist`
- **Skill:** `database-design`
- **Priority:** P0 — Diğer tüm taskların blocker'ı
- **INPUT:** Yukarıdaki şema tanımı
- **OUTPUT:** `create_google_review_interactions_table` migration dosyası
- **VERIFY:** `php artisan migrate` hatasız çalışır, tablo PostgreSQL'de oluşur.

#### Task 1.2 — Model oluştur
- **Agent:** `backend-specialist`
- **Skill:** `clean-code`
- **Priority:** P0
- **Bağımlılık:** Task 1.1
- **INPUT:** Migration şeması
- **OUTPUT:** `app/Models/GoogleReviewInteraction.php` — `$fillable`, `visitor()`, `store()`, `guestMessage()` ilişkileri
- **VERIFY:** `GoogleReviewInteraction::create([...])` çalışır.

---

### PHASE 1 — Backend API (P1)

#### Task 2.1 — Controller oluştur (3 endpoint)
- **Agent:** `backend-specialist`
- **Skill:** `api-patterns`
- **Priority:** P1
- **Bağımlılık:** Task 1.2
- **OUTPUT:** `GoogleReviewInteractionController.php`
  - `store()` → Popup gösterilince kayıt oluşturur (`status: showed`)
  - `update()` → Kullanıcı cevap verince günceller (`accepted / rejected / dismissed`)
  - `googleClicked()` → Google'a tıklandığında `google_redirected = true` yapar
- **VERIFY:** `php artisan route:list | grep review` 3 route görünür.

#### Task 2.2 — Route'ları kaydet
- **Agent:** `backend-specialist`
- **Skill:** `api-patterns`
- **Priority:** P1
- **Bağımlılık:** Task 2.1
- **OUTPUT:** `routes/web.php`'ye eklenen route'lar:
  ```
  POST   /api/{store_slug}/review-interaction
  PATCH  /api/{store_slug}/review-interaction/{id}
  POST   /api/{store_slug}/review-interaction/{id}/google-clicked
  ```
- **VERIFY:** curl ile 200 döner.

#### Task 2.3 — GuestMessageController entegrasyonu
- **Agent:** `backend-specialist`
- **Skill:** `api-patterns`
- **Priority:** P1
- **Bağımlılık:** Task 2.1
- **Açıklama:** Ses Ver formu gönderildiğinde, request'te `review_interaction_id` varsa ilgili kayıtta `feedback_submitted = true` ve `guest_message_id` doldurulur.
- **OUTPUT:** `GuestMessageController::store()` güncellenir.
- **VERIFY:** Hayır → Ses Ver akışında `feedback_submitted` veritabanında `true` olur.

---

### PHASE 2 — Frontend Entegrasyonu (P2)

#### Task 3.1 — Popup "showed" kaydı
- **Agent:** `frontend-specialist`
- **Skill:** `clean-code`
- **Bağımlılık:** Task 2.2
- **Açıklama:** `isVisible = true` olduğunda API'ye POST atar. Dönen `interaction_id`'yi state'de saklar.
- **OUTPUT:** `GoogleReviewPopup.jsx` — `interactionId` state eklenir.
- **VERIFY:** Popup açıldığında DB'de `status: showed` satır oluşur.

#### Task 3.2 — "Evet" aksiyonu
- **OUTPUT:** `handleYes()` → PATCH ile `status: accepted`
- **VERIFY:** Responded_at ve status güncellenir.

#### Task 3.3 — Google tıklama kaydı
- **OUTPUT:** `handleGoogleRedirect()` → `google_redirected` endpoint'ini çağırır, ardından yeni sekmede açar.
- **VERIFY:** `google_redirected: true` güncellenir.

#### Task 3.4 — "Hayır" ve "Kapat" aksiyonları
- **OUTPUT:**
  - `handleNo()` → PATCH ile `status: rejected`
  - `handleDismiss()` (X butonu) → PATCH ile `status: dismissed`
- **VERIFY:** Her durumda doğru status güncellenir.

#### Task 3.5 — Ses Ver entegrasyonu
- **Açıklama:** `interactionId`, `onOpenSesVer` prop'u aracılığıyla Ses Ver formuna geçirilir. Form gönderildiğinde `review_interaction_id` request'e eklenir.
- **OUTPUT:** `Index.jsx` ve `MenuInteractionDrawer.jsx` hafif güncelleme.
- **VERIFY:** Hayır → Ses Ver → Gönder akışında `feedback_submitted: true` olur.

---

### PHASE 3 — Admin Panel (P3)

#### Task 4.1 — Filament Stats Widget
- **Agent:** `frontend-specialist`
- **Skill:** `frontend-design`
- **Bağımlılık:** Task 1.2
- **OUTPUT:** `GoogleReviewStatsWidget.php` — Aşağıdaki stat kartları:

| Metrik | Hesaplama |
|--------|-----------|
| Toplam Gösterim | `status = showed` COUNT |
| Evet Oranı | `accepted / showed` % |
| Google'a Gidiş | `google_redirected = true / accepted` % |
| Hayır Oranı | `rejected / showed` % |
| Ses Ver Tamamlama | `feedback_submitted = true / rejected` % |
| Cevapsız Kapama | `dismissed / showed` % |

- **VERIFY:** Admin panelinde widget görünür, rakamlar SQL ile doğrulanabilir.

---

### PHASE X — Doğrulama

- [ ] `php artisan migrate` hatasız tamamlanır.
- [ ] `php artisan route:list` 3 yeni route listeler.
- [ ] Test modu (`?test_review=1`) ile tüm akış manuel test edilir:
  - [ ] Popup açılır → DB'de `showed` kaydı oluşur.
  - [ ] Evet → `accepted` güncellenir.
  - [ ] Google'a tıkla → `google_redirected: true` olur.
  - [ ] Hayır → `rejected` güncellenir.
  - [ ] Ses Ver yaz & gönder → `feedback_submitted: true` olur.
  - [ ] X butonu → `dismissed` olur.
- [ ] Admin Widget rakamları SQL ile eşleşir.
- [ ] `npm run build` hatasız tamamlanır.

---

## Risk & Rollback

| Risk | Çözüm |
|------|-------|
| API çağrısı başarısız olursa popup bozulur mu? | Tüm API çağrıları `try/catch` ile sarılır, hata kullanıcıya yansımaz. |
| Eski localStorage mantığıyla çakışma | localStorage korunur; veritabanı sadece ek bir analitik katmanı. |
| Performans | Tüm API çağrıları `async/await` ile arka planda, popup render'ını bloklamaz. |
