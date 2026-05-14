## Context

`Ses Ver` formu şu anda client-side validasyon içermiyor. Kullanıcı 10 karakterden az mesaj gönderdiğinde istek backend'e gidip 422 dönüyor, frontend ise yalnızca genel "HATA OLUŞTU" mesajı gösteriyor. Kullanıcı neyin yanlış gittiğini anlayamıyor.

Mevcut form yapısı `MenuInteractionDrawer.jsx` içinde bulunuyor. Framer Motion ve `AlertTriangle` ikonu zaten import edilmiş durumda.

## Goals / Non-Goals

**Goals:**
- Kullanıcıya 10 karakter limitini client-side'da, anında ve görsel olarak iletmek
- Gereksiz API çağrısını engellemek (istek backend'e hiç gönderilmez)
- Neobrutalist tasarım diline uygun, sert ve net bir animasyon+metin uyarısı sunmak

**Non-Goals:**
- Backend validasyonunu kaldırmak (kalır, savunmacı katman olarak)
- Karakter sayacı eklemek (farklı bir opsiyon, bu kapsamda değil)
- 1000 karakter üst limiti için ayrı client-side uyarı (opsiyonel, bu kapsamda değil)

## Decisions

### 1. Client-side kontrolü `handleFeedbackSubmit` içinde yap
`onSubmit` handler'ında, `fetch` çağrısından önce `feedback.trim().length < 10` kontrolü yapılır. Bu yaklaşım React form state'ini değiştirmez, sadece gönderimleri engeller.

**Alternatif:** `disabled` prop ile butonu pasif bırakmak → Reddedildi; kullanıcı neden pasif olduğunu anlamaz.

### 2. Shake animasyonu için `motion.div` wrapper
Textarea'nın mevcut `<div className="relative">` wrapper'ı `<motion.div>`'e dönüştürülür. `isShaking` state true olduğunda Framer Motion `animate` prop'u `{ x: [-8, 8, -8, 8, 0] }` dizisi ile titreşim efekti uygular.

**Alternatif:** CSS `@keyframes shake` — Reddedildi; Framer Motion zaten bundle'da ve daha kolay kontrol edilebilir.

### 3. Yeni `feedbackStatus` değeri: `'short'`
Mevcut `feedbackStatus` state'ine `'short'` değeri eklenir. Bu değer:
- Butonu kırmızı (`bg-red-500`) yapar
- Buton metnini `"EN AZ 10 HARF YAZ!"` olarak değiştirir
- 2 saniye sonra `'idle'`'a döner

**Ayrı state (`isTooShort: boolean`) alternatifi** — Reddedildi; mevcut `feedbackStatus` pattern'ine uymak tutarlılık sağlar.

## Risks / Trade-offs

- [Risk] Framer Motion `animate` array prop animasyonu saniyede 60 frame çalıştırır → Yük hafif, mobil'de sorun yaratmaz
- [Risk] `'short'` state, `disabled={feedbackStatus !== 'idle'}` kontrolüne takılır → Button zaten disable olacak, shake görünmez olmayacak; timeout'tan önce kullanıcı tekrar basamaz ✅
