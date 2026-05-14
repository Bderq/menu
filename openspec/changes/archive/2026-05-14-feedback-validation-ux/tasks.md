## 1. State Yönetimi

- [x] 1.1 `MenuInteractionDrawer.jsx`'te `feedbackStatus` state'inin yanına `const [isShaking, setIsShaking] = useState(false);` ekle

## 2. Handler Güncelleme

- [x] 2.1 `handleFeedbackSubmit` içinde `fetch` çağrısından önce `feedback.trim().length < 10` kontrolü ekle
- [x] 2.2 Kontrol başarısız olduğunda: `setFeedbackStatus('short')` ve `setIsShaking(true)` çağır
- [x] 2.3 `setTimeout(600ms)` ile `setIsShaking(false)` sıfırla
- [x] 2.4 `setTimeout(2000ms)` ile `setFeedbackStatus('idle')` sıfırla
- [x] 2.5 `return` ile fonksiyondan çık (fetch gönderilmesin)

## 3. Textarea Shake Animasyonu

- [x] 3.1 Textarea'nın `<div className="relative">` wrapper'ını `<motion.div className="relative">` olarak değiştir
- [x] 3.2 `animate={isShaking ? { x: [-8, 8, -8, 8, -6, 6, 0] } : { x: 0 }}` prop ekle
- [x] 3.3 `transition={{ duration: 0.5 }}` prop ekle

## 4. Buton `short` State'i

- [x] 4.1 Buton `className` koşuluna `feedbackStatus === 'short' ? 'bg-red-500 text-white' :` ekle
- [x] 4.2 Buton içerisine `{feedbackStatus === 'short' && <><AlertTriangle size={24} /> EN AZ 10 HARF YAZ!</>}` satırı ekle

## 5. Build ve Test

- [x] 5.1 `npm run build` çalıştır
- [x] 5.2 Sayfayı yenile ve 5 karakter yazıp gönder → shake + kırmızı buton uyarısı görünmeli
- [x] 5.3 2 saniye bekle → formun `idle` durumuna döndüğünü doğrula
- [x] 5.4 15+ karakter yazıp gönder → normal başarı akışını doğrula
