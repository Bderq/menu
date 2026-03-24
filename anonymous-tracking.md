# Plan: Anonymous Visitor & Interaction Tracking System

## Overview
Bu plan, QR Menü üzerindeki ziyaretçileri benzersiz (unique) bir şekilde tanımlamak, ürün ve kategori etkileşimlerini takip etmek ve bu verileri Filament admin panelinde raporlamak için gereken altyapıyı kapsar.

**Hedefler:**
- Cihaz bazlı (UUID + LocalStorage + Fingerprint) sağlam kimlik tanıma.
- Ürün tıklamaları ve kategori görüntüleme verilerini toplama.
- Menüde geçirilen süreyi (Dwell Time) ölçme.
- Filament üzerinde basit bir analiz tablosu sunma.

---

## Project Type
**WEB (Laravel + Filament)**

---

## Success Criteria
- [ ] Yeni gelen her cihaza 1 yıllık `visitor_uuid` çerezi ve LocalStorage kaydı atanmalı.
- [ ] Çerezler silinse bile "Gelişmiş Fingerprint (Canvas/GPU)" ile cihaz %99+ doğrulukla tanınmalı.
- [ ] Her ürün tıklaması ve kategori görüntülemesi `interactions` tablosuna kaydedilmeli.
- [ ] Filament admin panelinde "En Çok Tıklanan Ürünler" ve "Tekil Ziyaretçi Sayısı" raporlanmalı.

---

## Tech Stack
- **Backend:** Laravel 11.x (PHP 8.2+)
- **Database:** MySQL/MariaDB
- **Admin UI:** Filament v3
- **Frontend Tracking:** Vanilla JavaScript (Fingerprint implementation)

---

## File Structure
```plaintext
app/
├── Models/
│   ├── Visitor.php         # Ana cihaz kimlikleri
│   ├── Visit.php           # Her bir ziyaret/oturum (Session)
│   └── Interaction.php     # Tıklama ve görüntüleme logları
├── Http/
│   ├── Middleware/
│   │   └── TrackVisitor.php # Kimlik tanıma ve çerez yönetimi
├── Filament/
│   └── Pages/
│       └── AnalyticsDashboard.php # Analiz rapor sayfası
database/
└── migrations/
    ├── create_visitors_table.php
    ├── create_visits_table.php
    └── create_interactions_table.php
```

---

## Task Breakdown

### Phase 1: Database Foundation (`database-architect`)
- [ ] **Task 1.1:** `visitors` tablosu göçü (Migration). (`uuid`, `fingerprint_hash`, `ip_address`, `user_agent`, `last_seen_at`).
- [ ] **Task 1.2:** `visits` tablosu göçü. (`visitor_id`, `started_at`, `ended_at`).
- [ ] **Task 1.3:** `interactions` tablosu göçü. (`visit_id`, `interactable_type` [Product/Category], `interactable_id`, `type` [view/click]).
- [ ] **Task 1.4:** Eloquent modellerini ve ilişkilerini tanımlama.

### Phase 2: Identifier Logic & Middleware (`backend-specialist`)
- [ ] **Task 2.1:** `TrackVisitor` middleware oluşturma (Cookie + UUID logic).
- [ ] **Task 2.2:** Global middleware listesine ekleme (Sadece frontend rotaları için).
- [ ] **Task 2.3:** Fingerprint verisini backend'e iletmek için küçük bir API endpoint'i veya Form verisi entegrasyonu.

### Phase 3: Frontend Tracking & JS (`frontend-specialist`)
- [ ] **Task 3.1:** LocalStorage kontrolü ve Backend UUID ile senkronizasyon script'i.
- [ ] **Task 3.2:** Canvas/GPU tabanlı Fingerprint üretme script'i.
- [ ] **Task 3.3:** Ürün tıklamalarını ve sayfa değişimlerini (Kategori görüntüleme) sessizce backend'e gönderen AJAX (Fetch) yapısı.

### Phase 4: Admin Dashboard & reporting (`backend-specialist`)
- [ ] **Task 4.1:** Filament üzerinde `AnalyticsDashboard` sayfası oluşturma.
- [ ] **Task 4.2:** "En çok tıklanan 10 ürün" tablosu (Widget/Table).
- [ ] **Task 4.3:** "Son 24 saat tekil ziyaretçi" istatistiği.

---

## Phase X: Verification
- [ ] **Security:** Çerezler `HttpOnly: false` (JS erişimi için) ancak güvenli olmalı.
- [ ] **Performance:** `interactions` tablosu çok büyüyeceği için periyodik temizleme (Cleanup) komutu düşünülmeli.
- [ ] **Device Check:** Farklı tarayıcılarda (Safari, Chrome) ve Gizli Sekmede cihaz tanıma testi.

---

## ✅ PHASE X COMPLETE
- Lint: [ ]
- Security: [ ]
- Build: [ ]
- Date: [2026-03-24]
