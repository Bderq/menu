## Context

Sistemde QR menüyü ziyaret eden kullanıcıları izleyen ve ikinci ziyaretleri veya belli bir süre geçirdikleri zaman anket/poll gösteren bir yapı (PollPopup.jsx) mevcuttur. Şimdi bu sistemin yanına Google Business yorum sayfasını hedefleyen bir memnuniyet sorusu (GoogleReviewPopup) eklenmek istenmektedir.

## Goals / Non-Goals

**Goals:**
- Her mağazaya kendi Google yorum URL'sini (`google_review_url`) ve sorusunu (`google_review_question`) girebilme yetkisi tanımak.
- Menüyü 2. kez (veya daha fazla) ziyaret eden kullanıcıya 10. saniyede bu popup'ı göstermek.
- Kullanıcıya memnuniyet sorusunu sormak:
  - Evet derse: Google URL'sini yeni sekmede açacak bir link göstermek.
  - Hayır derse: Mevcut "Ses Ver" drawer bileşenini açarak şikayetini almak.
- Bir kere bu popup'ı veya prompt'u gören kullanıcının, bu popup'ı tekrar görmemesini sağlamak (LocalStorage ile state tutmak).

**Non-Goals:**
- Karmaşık bir analiz ekranı veya bu yorumların backend tarafından doğrulanması kapsam dışıdır.
- Google Business API ile gerçek yorumların listelenmesi veya entegrasyonu kapsam dışıdır.

## Decisions

- **Veritabanı (Store Tablosu):** `google_review_url` ve `google_review_question` olmak üzere iki yeni alan Store tablosuna eklenecek.
- **Backend (MenuController):** Inertia ile frontend'e kullanıcının ziyaret sayısını (`visitCount`) prop olarak göndereceğiz. `tracking_visitor_id` halihazırda Middleware tarafından bağlanıyor.
- **Frontend (LocalStorage):** Frontend'de Popup gösterilme durumu localStorage'da `google_review_seen_{storeSlug}` şeklinde saklanacak. Böylece veritabanında ekstra log kaydı/tablosu tutmaktan kaçınılacak.
- **Tasarım Dili:** Bileşen, projedeki PollPopup.jsx ile aynı brutalist tasarım dilinde (alt sağ köşe, kalın çerçeveli, z-index'e dikkat edilmiş şekilde) tasarlanacak. Çakışma olmaması için PollPopup'tan biraz daha düşük veya kontrol edilen bir z-index alacak.

## Risks / Trade-offs

- **Risk:** Kullanıcıların tarayıcı çerezlerini temizlemesi veya farklı tarayıcıdan girmesi durumunda prompt tekrar görünecektir. Bu, localStorage kullanımının bir trade-off'udur ancak basitlik ve performans açısından (DB roundtrip'i olmaması) tercih edilmiştir.
- **Çakışma (Collision) Riski:** Aynı anda hem Poll hem de Google Review popup'ının çıkması durumunda üst üste binmemesi için z-index yönetimi yapılacak veya UI'da duruma göre biri gösterilecektir. Ancak bu basit bir koşulla çözülebilir.
