## ADDED Requirements

### Requirement: Peek animasyonu Spotify verisi olmadan görünmez

Bileşen, `/api/{storeSlug}/now-playing` endpoint'inden `is_playing: true` içeren bir yanıt almadıkça hiçbir şey render etmemeli (null döndürmeli).

#### Scenario: Spotify verisi yok

- **WHEN** sayfa yüklenir ve Spotify API'dan veri gelmez veya `is_playing` false döner
- **THEN** `NowPlayingPeek` bileşeni DOM'da herhangi bir element render etmez

#### Scenario: Spotify verisi var ama çalmıyor

- **WHEN** API yanıtı başarılı ama `is_playing: false` içeriyor
- **THEN** `NowPlayingPeek` bileşeni DOM'da herhangi bir element render etmez

---

### Requirement: Sayfa açıldığında otomatik peek tetiklenir

Sayfa ilk yüklendiğinde Spotify verisi `is_playing: true` ise peek animasyonu otomatik olarak tetiklenmelidir. Kullanıcı herhangi bir etkileşimde bulunmadan açılıp kapanmalıdır.

#### Scenario: Sayfa yüklendiğinde müzik çalıyor

- **WHEN** sayfa ilk yüklenir ve API `is_playing: true` döner
- **THEN** `NowPlayingPeek` bileşeni sağdan sola kayarak ekrana girer, 5 saniye boyunca görünür kalır, ardından sağa doğru kayarak geri çekilir

#### Scenario: Sayfa yüklendiğinde müzik çalmıyor, sonra başlıyor

- **WHEN** sayfa yüklenir ve başlangıçta `is_playing: false`, daha sonra interval fetch `is_playing: true` döner
- **THEN** peek animasyonu tetiklenir

---

### Requirement: Şarkı değişiminde peek yeniden tetiklenir

Şarkı değiştiğinde (track adı veya artist farklılaştığında) peek animasyonu yeniden başlatılmalıdır.

#### Scenario: Farklı bir şarkı algılandığında

- **WHEN** polling intervalinde dönen `track` veya `artist` değeri önceki değerden farklı
- **THEN** peek animasyonu sıfırlanarak yeniden sağdan sola kayar ve 5 saniye sonra geri çekilir

---

### Requirement: Peek şeridinin içeriği

Peek şeridi şu bilgileri içermelidir: albüm kapağı (küçük thumbnail), "NOW PLAYING" etiketi, şarkı adı (belirgin büyük font), sanatçı adı (mono font, küçük), EQ bar animasyonu (müzik oynuyor hissiyatı için).

#### Scenario: Albüm kapağı mevcut

- **WHEN** API yanıtı `image` alanı içeriyor
- **THEN** peek şeridinde o albüm görseli thumbnail olarak gösterilir

#### Scenario: Albüm kapağı yok

- **WHEN** API yanıtı `image` alanı boş veya yok
- **THEN** peek şeridinde ikon veya placeholder gösterilir (şerit yine de görünür olmalı)

#### Scenario: EQ animasyonu şarkı çalarken aktif

- **WHEN** `is_playing: true` ve peek şeridi açık
- **THEN** EQ bar animasyonu (yükseklik değişen 3 bar) loop halinde çalışır

---

### Requirement: FAB butonu müzik çalarken pulse animasyonu gösterir

`MenuInteractionDrawer`'daki FAB (⚡) butonu, aktif Spotify verisi varken hafif bir pulse/scale animasyonu yapmalıdır.

#### Scenario: Müzik çalarken FAB görünümü

- **WHEN** `hasMusicPlaying` prop'u `true` olarak iletilir
- **THEN** FAB butonu sürekli tekrar eden scale pulse animasyonu yapar (`scale: [1, 1.08, 1]`, ~2 saniyelik döngü)

#### Scenario: Müzik çalmıyorken FAB görünümü

- **WHEN** `hasMusicPlaying` prop'u `false` veya iletilmemiş
- **THEN** FAB butonu normal statik görünümünde kalır, hiçbir ek animasyon yapmaz

---

### Requirement: Peek şeridi mevcut bileşenlerle z-index çakışmasına yol açmaz

`NowPlayingPeek` bileşeni, sol alttaki `NowPlaying.jsx` vinyl widget'ı, açık drawer overlay'i veya ürün detail drawer'ıyla görsel çakışma yaratmamalıdır.

#### Scenario: Drawer açıkken peek tetiklenmez

- **WHEN** drawer açık durumdayken yeni şarkı algılanır
- **THEN** peek animasyonu tetiklenmez (drawer zaten şarkı bilgisini gösteriyor)

#### Scenario: Peek ve vinyl widget aynı anda görünür

- **WHEN** hem sol alttaki vinyl widget hem sağ alttaki peek aktif
- **THEN** ikisi ayrı konumlarda (`left-6` vs `right-6`) birbirini örtmeden görünür
