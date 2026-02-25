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

`settings_schema` desteklenir. Bozuk/eksik meta durumunda sistem fail-open kalır, adminde uyarı görünür.

## Manuel Smoke Test Adımları
1. Admin -> Modül Merkezi (`/admin/moduller.php`) aç.
2. Sekmeleri sırayla gez: Kurulu / Kurulabilir / Eksik / Ayarlar.
3. Kurulabilir sekmesinden bir modülü sisteme ekle; Kurulu sekmesinde göründüğünü doğrula.
4. Kurulu sekmesinden modülü aktif/pasif yap; durum etiketinin değiştiğini doğrula.
5. Ayarlar sekmesinde schema alanlarını kaydet; sayfayı yenileyince değerlerin korunduğunu doğrula.
6. Eski uç uyumluluğu: `/admin/plugins.php` açıldığında Modül Merkezi'ne yönlendirme yapıldığını doğrula.

## DirectAdmin Uyumluluk Notu
- SSH/composer bağımlılığı eklenmedi.
- Ek servis yok; yalnızca PDO + proje veritabanı kullanıldı.
- `vendor` FTP yaklaşımına aykırı bir kurgu eklenmedi.
- Yollar `public_html` kök düzeniyle uyumlu ve `.htaccess`/`open_basedir` kısıtlarını ihlal etmez.
