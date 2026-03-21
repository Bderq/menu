# Premium QR Menu (Next-Gen Digital Catalog)

Modern, estetik ve ultra hızlı bir QR Menü deneyimi sunmak üzere tasarlanmış, Laravel ve React tabanlı dijital katalog sistemidir.

## 🚀 Teknoloji Yığını (Cutting-Edge Stack)

Bu proje, 2026'nın en güncel teknolojilerini bir araya getirerek "Premium" bir deneyim sunar:

- **Backend:** Laravel 12.x (PHP 8.2+)
- **Admin Panel:** Filament v5.1+ (ERP seviyesinde yönetim)
- **Frontend Framework:** React 19.x & Inertia.js v2.0+
- **Styling:** Tailwind CSS v4.0+ (Modern Utility-First design)
- **Animations:** Framer Motion v12.x (60 FPS mikro-animasyonlar)
- **Build Tool:** Vite v7.0+
- **Icons:** Lucide React

## ✨ Temel Özellikler

- **Multi-Store Architecture:** Tek bir instance üzerinden sınırsız şube yönetimi.
- **Dynamic Campaign Engine:** Fixed Price, Percentage, Bundle ve X-Get-Y kampanya kurguları.
- **Smart Scheduling:** Günlük ve saatlik (Overnight dahil) otomatik kampanya aktivasyonu.
- **Premium UI:** Teknoloji odaklı, minimalist ve performanslı (Vercel-standard) arayüz.
- **Custom Branding:** Şubeye özel tema renkleri, logolar ve ürün varyasyonları.

## 🛠️ Kurulum ve Geliştirme

```bash
# Bağımlılıkları yükle
composer install
npm install

# .env yapılandırmasını yap (Veritabanı bağlantısı vb.)
cp .env.example .env
php artisan key:generate

# Migrasyonları ve seed verilerini çalıştır
php artisan migrate --seed

# Geliştirme sunucusunu başlat (Vite + Laravel)
composer run dev
```

---

<p align="center">
Designed for Excellence. Built with Laravel 12.
</p>
