## Why

`Ses Ver` formunda kullanıcı 10 karakterden az mesaj gönderdiğinde backend 422 döner, ancak frontend yalnızca genel bir "HATA OLUŞTU" mesajı gösterir. Kullanıcı neyin yanlış gittiğini anlamaz, form deneyimi kötüleşir. Validasyon geri bildirimi client-side'da, açık ve neobrutalist tarza uygun şekilde verilmelidir.

## What Changes

- `MenuInteractionDrawer.jsx` içine `isShaking` state eklenir
- `handleFeedbackSubmit` handler'ına client-side `< 10 karakter` kontrolü eklenir — backend'e istek gönderilmez
- Textarea wrapper'ı Framer Motion `motion.div` ile sarılır; kısa mesaj denemesinde x-ekseni titreşim (shake) animasyonu oynar
- Buton `short` state'i için kırmızı arka plan + `"EN AZ 10 HARF YAZ!"` metni gösterir (2 saniye, ardından `idle`'a döner)
- Backend `min:10` validasyonu yerinde kalır (savunmacı katman olarak)

## Capabilities

### New Capabilities
- `feedback-short-message-validation`: Kullanıcı 10 karakterden az mesaj göndermeye çalıştığında client-side uyarı: textarea shake animasyonu + buton uyarı metni

### Modified Capabilities
- (yok)

## Impact

- **Dosya:** `resources/js/Components/MenuInteractionDrawer.jsx`
- **Bağımlılık:** Framer Motion (`motion`) zaten import edilmiş; `AlertTriangle` ikonu zaten mevcut
- **Backend:** Değişiklik yok
- **Build:** `npm run build` gerektirir
