## Context

QR Menü sayfasının sağ alt köşesinde bir FAB (⚡ Zap) butonu bulunuyor. Butona tıklanınca sağdan sola açılan bir drawer ortaya çıkıyor. Bu drawer içinde Spotify'dan çekilen "şimdi çalıyor" verisi (şarkı adı, sanatçı, albüm kapağı, ilerleme barı) zaten gösteriliyor — ama yalnızca drawer açık olduğunda.

Sorun: Kullanıcı drawer'ı hiç açmadan menüde geziniyor. Spotify entegrasyonunu ve müzik özelliğini tamamen kaçırıyor. Bu hem engagement hem de marka deneyimi açısından kayıp.

Çözüm: Sayfa açıldığında, müzik çalıyorsa, FAB butonunun yanından sola doğru otomatik bir "peek" animasyonu tetiklenecek. Birkaç saniye ekranda kalıp geri çekilecek.

Mevcut ilgili bileşenler:
- `NowPlaying.jsx` — sol altta vinyl/LP tarzı müzik widget'ı (bağımsız, bu değişmeyecek)
- `MenuInteractionDrawer.jsx` — sağdaki FAB + drawer + drawer içinde şarkı bilgisi
- `Index.jsx` — Tüm bileşenleri bir araya getiriyor

## Goals / Non-Goals

**Goals:**
- Sayfa açıldığında Spotify `is_playing: true` ise FAB'ın yanından bir peek animasyonu çalışsın
- Şarkı değiştiğinde peek yeniden tetiklensin
- FAB butonu, müzik çalarken görsel olarak "canlı" hissettirsin (pulse/glow)
- Peek bileşeni tamamiyle bağımsız olsun — mevcut `NowPlaying.jsx` ve `MenuInteractionDrawer.jsx` mantığı bozulmadan kalsın
- Mobil ve masaüstünde düzgün çalışsın

**Non-Goals:**
- Drawer içindeki şarkı player'ını değiştirmek
- Sol alttaki `NowPlaying.jsx` vinyl widget'ını kaldırmak veya değiştirmek
- Backend / API değişikliği yapmak
- Kullanıcının peek'i kapatabilmesi (otomatik gizleniyor, müdahale yok)

## Decisions

### 1. Yeni bileşen mi, mevcut bileşen içine mi?

**Karar:** Yeni, bağımsız `NowPlayingPeek.jsx` bileşeni oluşturulacak.

**Gerekçe:** `MenuInteractionDrawer.jsx` zaten 450+ satır. Peek mantığı (fetch, timer, animasyon) buraya eklenmesi bileşeni şişirir. Ayrı tutmak test edilebilirliği artırır ve ileride kaldırılmasını kolaylaştırır.

**Alternatif değerlendirilen:** `MenuInteractionDrawer`'a `showPeek` state eklenmesi — reddedildi, bileşen sorumluluğunu genişletiyor.

### 2. Peek'in Spotify verisini nasıl alacağı

**Karar:** `NowPlayingPeek.jsx` kendi bağımsız `fetch` döngüsünü çalıştıracak (`/api/{slug}/now-playing`). Sayfa açıldığında tek fetch, şarkı değişimini detect etmek için 10 saniyelik interval.

**Gerekçe:** `Index.jsx`'ten prop geçirmek mümkün ama `Index.jsx` zaten karmaşık. Bağımsız fetch ile bileşen tamamen self-contained olur, `Index.jsx`'e minimum dokunuş yeterli.

**Alternatif değerlendirilen:** Lifted state ile `Index.jsx`'te tek fetch yapıp hem `NowPlayingPeek` hem `MenuInteractionDrawer`'a prop geçirmek — ileride temiz refactor için uygun ama bu değişikliğin kapsamını aşıyor.

### 3. Animasyon kütüphanesi

**Karar:** Mevcut `framer-motion` kullanılacak (`AnimatePresence` + `motion.div`).

**Gerekçe:** Proje genelinde standart, ekstra bağımlılık yok.

### 4. Peek konumu ve yönü

**Karar:** `fixed`, `bottom-6 right-6`, FAB'ın hemen üzerinde veya yanında. FAB'ın sol kenarından başlayarak sola doğru yatay kayma (`x: "0" → x: "-100%"` değil, tam tersi: `x: 200px → x: 0`). Peek bileşeni FAB ile aynı `z-index` katmanında ama FAB'ın önünde.

**Gerekçe:** Sağdan geliyor, sola açılıyor hissi. Drawer'ın da sağdan soldan açılmasıyla tutarlı dil.

### 5. FAB pulse animasyonu

**Karar:** `MenuInteractionDrawer.jsx`'e `hasMusicPlaying` boolean prop ekleniyor. Bu prop `true` olunca FAB'a framer-motion `animate={{ scale: [1, 1.05, 1] }}` + `transition={{ repeat: Infinity, duration: 2 }}` ekleniyor.

**Gerekçe:** FAB'ın "canlı" hissettirmesi için minimum değişiklik, tek prop.

## Risks / Trade-offs

- **[Risk] İki bağımsız Spotify fetch]** → `NowPlayingPeek` ve `MenuInteractionDrawer` ikisi de ayrı fetch yapıyor. Hafif ek istek yükü. → Mitigation: Fetch aralıkları zaten 10 saniye, etki ihmal edilebilir. Gelecekte context/store ile birleştirilebilir.
- **[Risk] Pozisyon çakışması]** → Peek şeridi sol alttaki `NowPlaying.jsx` vinyl widget'ı ile z-index veya konum çakışması yaratabilir. → Mitigation: `NowPlaying.jsx` sol altta (`left-6`), peek sağ altta (`right-6`), yeterince ayrı.
- **[Risk] Çok fazla animasyon]** → Sayfa açılışında hem vinyl widget hem peek animasyonu başlayabilir → Mitigation: `NowPlaying.jsx` bağımsız, sol tarafta kalıyor. Peek sadece sağ tarafta, tek seferlik tetikleniyor.
