# P1-PR3 Doğrulama Notu

## Kapsam
- Kurumsal CMS veri modeli (`cms_pages`, `cms_page_revisions`)
- Admin CMS ekranları (liste, form, sil, önizleme)
- Frontend `/sayfa/{slug}` yayın akışı
- Sitemap ve temel SEO alanları (meta/canonical)

## Değişen Dosyalar
- `install.php`
- `setup.php`
- `core/Auth.php`
- `admin/includes/header.php`
- `admin/cms-sayfalar.php`
- `admin/cms-sayfa-form.php`
- `admin/cms-sayfa-sil.php`
- `admin/cms-sayfa-onizleme.php`
- `admin/sayfalar.php` (uyumluluk yönlendirmesi)
- `app/Controllers/PageController.php`
- `page.php`
- `sitemap.php`
- `temalar/varsayilan/sayfa.php`
- `temalar/svs-tema/sayfa.php`
- `temalar/varsayilan/ust.php`
- `temalar/svs-tema/ust.php`

## Manuel Smoke Test Adımları
1. `install.php` veya `setup.php` ile kurulumdan sonra DB'de `cms_pages` ve `cms_page_revisions` tablolarını doğrula.
2. Admin giriş yap, `/admin/cms-sayfalar.php` aç.
3. Yeni sayfa ekle (`durum=taslak`), kaydet; liste ekranında göründüğünü doğrula.
4. Sayfayı düzenleyip `durum=yayinda` yap; önizleme ekranını aç.
5. Frontend'de `/sayfa/{slug}` ile sayfanın açıldığını doğrula.
6. Yayından alın (`taslak`) ve aynı URL'nin 404 verdiğini doğrula.
7. `/sitemap.xml` içinde yayındaki CMS sayfa URL'lerinin yer aldığını doğrula.
8. Sayfanın kaynak kodunda `<meta name="description">` ve `<link rel="canonical">` alanlarını doğrula.

## Güvenlik Kontrol Listesi
- `requireAdmin` + `manage_cms` yetkisi var.
- POST işlemleri CSRF korumalı.
- Slug doğrulaması regex ile yapılıyor.
- İçerik basımı `strip_tags` allow-list ile güvenli hale getiriliyor.

## DirectAdmin Uyumluluk Notu
- Ek servis/daemon/SSH/composer bağımlılığı eklenmedi.
- Yalnızca PHP + PDO + mevcut MySQL kullanıldı.
- `vendor` FTP yaklaşımı bozulmadı.
- Yapı `public_html` kök ve `.htaccess` (Apache/LiteSpeed) uyumunu bozmaz.
- Dosya/çalışma yolları `open_basedir` kısıtlarına uygun tutuldu.
