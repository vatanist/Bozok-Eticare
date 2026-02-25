# P1-PR2 Doğrulama Notu

## Kapsam
- Options API (çekirdek)
- `module.json` okuma/doğrulama (fail-open)
- Tek admin modül ekranı iskeleti (`admin/moduller.php`)

## Değişen Dosyalar
- `core/SecenekServisi.php`
- `core/ModulSozlesmesi.php`
- `includes/functions.php`
- `config/config.php`
- `admin/moduller.php`
- `admin/plugins.php` (uyumluluk yönlendirmesi)
- `install.php`
- `setup.php`
- `moduller/odeme/paytr/module.json`
- `moduller/odeme/havale/module.json`
- `moduller/genel/slider/module.json`

## Options API Fonksiyonları
- `option_get($anahtar, $varsayilan = null, $grup = 'genel')`
- `option_set($anahtar, $deger, $grup = 'genel', $autoload = false)`
- `option_delete($anahtar, $grup = 'genel')`
- `option_has($anahtar, $grup = 'genel')`
- `option_get_group($grup = 'genel')`

## module.json Zorunlu Alanlar
- `code`
- `name`
- `version`
- `type`
- `entry`

`settings_schema` yalnızca dizi formatında desteklenir ve tip beyaz listesi uygulanır: `string`, `bool`, `int`, `float`, `select`, `json`.

## DirectAdmin Gerçekçi Kurulum Smoke Testi
1. `install.php` ile temiz kurulum yap.
2. Kurulumdan sonra veritabanında `core_options` tablosunun oluştuğunu doğrula.
3. Admin giriş yap ve `/admin/moduller.php` aç.
4. `paytr`, `havale`, `slider` modülleri için Ayarlar sekmesinden değer kaydet.
5. Veritabanında `core_options` tablosunda `modul_payment_paytr`, `modul_payment_havale`, `modul_module_slider` grup anahtarları altında kayıtları doğrula.
6. `/admin/plugins.php` açıldığında Modül Merkezi'ne yönlendirildiğini doğrula.

## Manuel Smoke Test Adımları
1. Sekmeleri sırayla gez: Kurulu / Kurulabilir / Eksik / Ayarlar.
2. Kurulabilir sekmesinden bir modülü sisteme ekle; Kurulu sekmesinde göründüğünü doğrula.
3. Kurulu sekmesinden modülü aktif/pasif yap; durum etiketinin değiştiğini doğrula.
4. Ayarlar sekmesinde schema alanlarını kaydet; sayfayı yenileyince değerlerin korunduğunu doğrula.

## DirectAdmin Uyumluluk Notu
- SSH/composer bağımlılığı eklenmedi.
- Ek servis/daemon yok; yalnızca PDO + proje veritabanı kullanıldı.
- `vendor` FTP yaklaşımına aykırı bir kurgu eklenmedi.
- `public_html` kök yapı ve `.htaccess` (Apache/LiteSpeed) uyumu korunur.
- Yollar `open_basedir` kısıtlarını ihlal etmez.

## Not
- `autoload=1` için bootstrap toplu önbellek yüklemesi bu sürümde eklenmedi; sonraki sürümde performans iyileştirmesi olarak ele alınacak.
