## Why

Sayfaya giren kullanıcı, mekanda Spotify üzerinden müzik çalındığından haberdar değil. Sağ alttaki drawer FAB butonu (⚡) yalnızca filtre/geri bildirim amaçlı algılanıyor; oysa drawer içinde şarkı bilgisi de mevcut. Bu fırsatı ortaya çıkarmak ve drawer etkileşimini artırmak için sayfa açılışında otomatik bir "peek" animasyonu eklenecek.

## What Changes

- **Yeni bileşen** `NowPlayingPeek.jsx` oluşturulacak — FAB butonunun solundan sola kayan MTV lower-third tarzı şerit
- Bileşen yalnızca Spotify'dan aktif şarkı verisi (`is_playing: true`) geldiğinde görünür hale gelecek
- Sayfa ilk yüklendiğinde şarkı alınırsa otomatik olarak 5 saniye "peek" yapıp geri çekilecek
- Şarkı değiştiğinde de yeniden peek tetiklenecek
- `MenuInteractionDrawer`'daki mevcut FAB butonu, Spotify verisi varken hafifçe pulse animasyonu kazanacak (tek prop değişikliği)
- `Index.jsx`'te `NowPlayingPeek` entegre edilecek; Spotify fetch mantığı yeniden kullanılacak (bağımsız fetch, mevcut `MenuInteractionDrawer` mantığına dokunulmayacak)

## Capabilities

### New Capabilities

- `now-playing-peek`: Sayfa açılışında ve şarkı değişiminde, drawer FAB butonunun yanından MTV stilinde sola kayan şarkı bilgisi animasyonu. Albüm kapağı küçük thumbnail, şarkı adı büyük font, sanatçı mono font, EQ bar animasyonu içerir. Spotify verisi yoksa görünmez.

### Modified Capabilities

- (Yok — mevcut bileşenler bozulmadan çalışmaya devam edecek)

## Impact

- **Yeni dosya**: `resources/js/Components/NowPlayingPeek.jsx`
- **Değişen dosya**: `resources/js/Pages/Menu/Index.jsx` (NowPlayingPeek import + render)
- **Değişen dosya**: `resources/js/Components/MenuInteractionDrawer.jsx` (FAB'a `hasMusicPlaying` prop + pulse efekti)
- **Bağımlılıklar**: `framer-motion` (zaten yüklü), Spotify `/api/{slug}/now-playing` endpoint'i (zaten mevcut)
- **Backend**: Değişiklik yok
- **Veritabanı**: Değişiklik yok
