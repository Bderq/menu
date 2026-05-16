## Why

Müşterilerin restorandaki deneyimlerini değerlendirmeleri için Google Business profilinde doğrudan yorum bırakmalarını sağlamak. Bu, işletmelerin Google haritalardaki yıldız puanlarını ve görünürlüklerini (SEO) artırmak için önemli bir adımdır.

## What Changes

Uygulamaya yeni bir Google Review Prompt (Memnuniyet Popup'ı) eklenecek. Bu popup, kullanıcının menüyü 2. kez ziyaret etmesinden 10 saniye sonra görünecek.
Kullanıcı deneyiminden memnunsa Google yorum sayfasına yönlendirilecek, değilse mevcut "Ses Ver" drawer'ına yönlendirilerek şikayet/önerisini iletmesi sağlanacak.
Her mağaza admin panelinden kendi Google Business linkini ve özel memnuniyet sorusunu ayarlayabilecek.

## Capabilities

### New Capabilities
- `google-review-prompt`: Google review prompt display logic, UI components, backend integration for visit counts, and store administration settings.

### Modified Capabilities
- `<existing-name>`: 

## Impact

- `Store` modeline yeni alanlar eklenecek.
- `MenuController` üzerinden frontend'e `visitCount` gönderilecek.
- Frontend'e yeni bir React bileşeni (`GoogleReviewPopup.jsx`) eklenecek ve `Index.jsx` üzerinde kullanılacak.
- Admin panelinde `StoreResource` formuna yeni alanlar eklenecek.
