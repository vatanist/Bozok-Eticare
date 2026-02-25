<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Hosting-DirectAdmin%20%2B%20MultiPHP-0ea5e9?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Version-2.1.0-blue?style=for-the-badge" />
</p>

<h1 align="center">🛒 Bozok E-Ticaret</h1>
<p align="center">
  <b>CMS uyumlu, modüler ve shared-hosting dostu e-ticaret altyapısı</b><br/>
  <sub>OpenCart / PrestaShop / WooCommerce benzeri tema-modül yaklaşımıyla geliştirilen PHP + MySQL çözümü</sub>
</p>

---

## 🎯 Güncel Durum (P0 – P1 – P3)

### ✅ P0 Stabilizasyonu
- Router sözleşmesiyle uyumlu `handle()/calistir()` ara katman yapısı
- Tema keşfi (`theme.json` / `style.css`) ve aktif tema çözümleme iyileştirmeleri
- Admin görünüm kırılmalarına karşı fail-open/fallback düzenlemeleri
- Modül loader’da `module/payment/shipping/marketing` tip desteği
- PayTR callback/success/fail güvenlik sertleştirmeleri

### ✅ P1 Altyapı
- Tema/kanca sözleşmesi tek merkezde (`TemaSozlesmesi`)
- Admin UI’nin temadan bağımsızlaştırılması (`gorunum_admin`)
- Modül sözleşmesi (`module.json`) ve doğrulama katmanı (`ModulSozlesmesi`)
- Options API (`SecenekServisi` + `option_*` fonksiyonları)
- Tek merkez modül yönetimi (`admin/moduller.php`)

### ✅ P3 Kurumsal CMS (Minimum Çalışır Sürüm)
- `cms_pages` + `cms_page_revisions` veri modeli
- Admin CRUD ekranları (liste, form, sil, önizleme)
- Frontend `GET /sayfa/{slug}` ve yalnız yayındaki sayfaların gösterimi
- Sitemap’e yayındaki CMS sayfalarının otomatik eklenmesi
- Meta description + canonical alanlarının tema üstünde çalışması

---

## 🚀 Kurulum (DirectAdmin + MultiPHP)

### Gereksinimler
- PHP 8.0+
- MySQL 8.0+ / MariaDB 10.5+
- Apache/LiteSpeed (`.htaccess` açık)
- Shared hosting uyumlu dosya izinleri

### Adımlar
1. Projeyi `public_html` altına yükleyin (FTP / Dosya Yöneticisi).
2. `.env.example` dosyasını `.env` olarak kopyalayın ve DB bilgilerini girin.
3. Tarayıcıdan `https://alanadiniz.com/install.php` çalıştırın.
4. Kurulum bitince güvenlik için `install.php` ve `setup.php` dosyalarını kaldırın.

> Not: SSH/composer zorunlu değildir. `vendor` klasörü FTP ile taşınabilir.

---

## 🧩 Modül ve Tema Mantığı
- Tema metadata: `theme.json` veya `style.css`
- Modül metadata: `module.json`
- Admin modül merkezi: `/admin/moduller.php`
- Kurumsal CMS yönetimi: `/admin/cms-sayfalar.php`

---

## 📚 Dokümantasyon
- `docs/P0-DOGRULAMA-NOTU.md`
- `docs/P1-PR1-DOGRULAMA-NOTU.md`
- `docs/P1-PR2-DOGRULAMA-NOTU.md`
- `docs/P1-PR3-DOGRULAMA-NOTU.md`
- `docs/P1-GECIS-PLANI.md`

---

## 📝 Changelog
Detaylı sürüm geçmişi için: **[CHANGELOG.md](CHANGELOG.md)**
