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

## DirectAdmin Gerçek DB Smoke Test (Adım Adım)
1. DirectAdmin panelinde proje dizinindeki `.env` dosyasını aç ve `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` değerlerini gerçek sunucu bilgileriyle doldur.
2. Tarayıcıdan `/install.php` çalıştır; kurulum tamamlandıktan sonra veritabanında `cms_pages` ve `cms_page_revisions` tablolarının oluştuğunu kontrol et.
3. Admin panelde **Kurumsal CMS** ekranına gir (`/admin/cms-sayfalar.php`):
   - yeni sayfa ekle (`slug: hakkimizda`),
   - `durum: yayinda` seç,
   - `meta_title`, `meta_description`, `canonical_url` alanlarını doldurup kaydet.
4. Frontend kontrolü:
   - `/sayfa/hakkimizda` URL’i açılıyor mu kontrol et,
   - `durum=taslak` olan bir CMS sayfası için URL’in 404/403 verdiğini doğrula.
5. `sitemap.xml` kontrolü:
   - `/sitemap.xml` aç,
   - yalnızca `yayinda` durumundaki CMS sayfalarının listelendiğini doğrula.
6. Güvenlik kontrolü:
   - CMS formunda CSRF token olmadan/bozuk token ile kaydetmeyi dene ve işlemin reddedildiğini doğrula,
   - `manage_cms` yetkisi olmayan admin kullanıcısı ile `/admin/cms-sayfalar.php` erişiminin engellendiğini doğrula.
