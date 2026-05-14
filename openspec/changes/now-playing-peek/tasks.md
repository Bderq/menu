## 1. NowPlayingPeek Bileşeni — Temel Yapı

- [x] 1.1 `resources/js/Components/NowPlayingPeek.jsx` dosyasını oluştur; `storeSlug` ve `isDrawerOpen` prop'larını kabul etsin
- [x] 1.2 `/api/{storeSlug}/now-playing` endpoint'ine fetch mantığını ekle; bileşen mount olduğunda ilk çağrıyı yap
- [x] 1.3 10 saniyelik polling intervalini kur; bileşen unmount olduğunda temizle
- [x] 1.4 `is_playing: false` veya veri yoksa bileşenin `null` döndürmesini sağla (spec: "Peek animasyonu Spotify verisi olmadan görünmez")

## 2. NowPlayingPeek Bileşeni — Animasyon Mantığı

- [x] 2.1 `isPeeking` state'ini kur; sayfa ilk yüklendiğinde `is_playing: true` gelince `true` yap, 5 saniye sonra otomatik `false` yap
- [x] 2.2 Şarkı değişim tespiti için önceki `track+artist` değerini kaydet; değişince `isPeeking`'i yeniden `true` yap (spec: "Şarkı değişiminde peek yeniden tetiklenir")
- [x] 2.3 `isDrawerOpen` prop'u `true` olduğunda peek'in tetiklenmesini engelle (spec: "Drawer açıkken peek tetiklenmez")
- [x] 2.4 `framer-motion AnimatePresence` + `motion.div` ile sağdan sola giriş (`x: 80 → 0`) ve çıkış (`x: 80`) animasyonu uygula; `spring` transition kullan

## 3. NowPlayingPeek Bileşeni — İçerik ve Stil

- [x] 3.1 Peek şeridine albüm kapağı thumbnail'i ekle; resim yoksa müzik ikonu placeholder göster (spec: "Albüm kapağı mevcut / yok")
- [x] 3.2 "NOW PLAYING" micro label, şarkı adı (büyük, heading font, truncate), sanatçı adı (mono font, küçük, truncate) ekle; mevcut drawer header'ındaki MTV stilini referans al
- [x] 3.3 EQ bar animasyonunu ekle (3 dikey bar, `is_playing: true` olunca `height` loop animasyonu); mevcut `MenuInteractionDrawer.jsx` içindeki EQ barı referans al
- [x] 3.4 Konumu `fixed bottom-24 right-6 z-[90]` olarak ayarla (FAB'ın üzerinde, drawer overlay'inin altında)
- [x] 3.5 Mevcut tema token'larını kullan: `bg-pitch-black`, `text-pub-gold`, `font-heading`, `font-mono`, `border-white`; mevcut drawer header estetiğiyle uyumlu yap

## 4. FAB Pulse Animasyonu — MenuInteractionDrawer Güncellemesi

- [x] 4.1 `MenuInteractionDrawer.jsx`'e `hasMusicPlaying` boolean prop ekle (varsayılan: `false`)
- [x] 4.2 FAB butonunun `motion.button` animasyonunu `hasMusicPlaying` true olunca `animate={{ scale: [1, 1.08, 1] }}` + `transition={{ repeat: Infinity, duration: 2, ease: "easeInOut" }}` ile güncelle; false olunca mevcut statik görünüm korunsun

## 5. Index.jsx Entegrasyonu

- [x] 5.1 `NowPlayingPeek`'i `Index.jsx`'e import et
- [x] 5.2 `isDrawerOpen` state'ini `NowPlayingPeek`'e prop olarak ilet
- [x] 5.3 `NowPlayingPeek`'ten `hasMusicPlaying` callback'ini al (veya bağımsız state); `MenuInteractionDrawer`'a `hasMusicPlaying` prop olarak geçir
- [x] 5.4 `NowPlayingPeek` bileşenini JSX'te FAB ve drawer çağrısının yanına yerleştir; `storeSlug={store?.slug}` ile besle

## 6. Doğrulama

- [x] 6.1 Spotify aktifken sayfayı açarak peek animasyonunun 5 saniye sonra kapandığını doğrula
- [x] 6.2 Drawer açıkken peek'in tetiklenmediğini doğrula
- [x] 6.3 FAB butonunun müzik çalarken pulse yaptığını, çalmayınca durduğunu doğrula
- [x] 6.4 Mobil ekranda peek şeridinin taşmadan düzgün göründüğünü doğrula (truncate kontrolü)
- [x] 6.5 Spotify verisi olmadığında sayfada hiçbir peek elementi görünmediğini doğrula
