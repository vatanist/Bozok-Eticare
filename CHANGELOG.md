# Changelog

Bu dosya sürüm bazında önemli değişiklikleri özetler.

## [2.1.0] - 2026-02-26
### Eklendi
- Kurumsal CMS minimum sürüm (`cms_pages`, `cms_page_revisions`, admin CRUD, önizleme).
- Modül sözleşmesi (`module.json`) ve Options API (`core_options`, `option_*`).
- Admin için tema-bağımsız görünüm akışı ve modül merkezi ekranı.

### Değiştirildi
- Tema/kanca sözleşmesi tek kaynağa alındı, alias/fallback davranışları netleştirildi.
- Sitemap çıktısı yayındaki CMS sayfalarını içerecek şekilde güncellendi.
- Kurulum akışı (`install.php` / `setup.php`) yeni tabloları oluşturacak şekilde genişletildi.

### Güvenlik
- CSRF doğrulamasında `hash_equals` kullanımı yaygınlaştırıldı.
- Modül metadata ve görünüm yol doğrulamalarında güvenlik kontrolleri sıkılaştırıldı.

## [2.0.x] - Önceki sürümler
- P0/P1 stabilizasyonları, tema geçişleri, ödeme modülü iyileştirmeleri ve admin panel düzenlemeleri.
- Detaylar için `docs/` altındaki doğrulama notlarına bakınız.
