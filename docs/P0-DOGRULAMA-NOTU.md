# P0 Doğrulama Notu

## 1) Değişen Dosyalar (P0)

- `admin/themes.php`
- `app/Controllers/HomeController.php`
- `app/Middleware/AuthMiddleware.php`
- `app/Middleware/CsrfMiddleware.php`
- `app/Middleware/RateLimitMiddleware.php`
- `config/config.php`
- `core/KurServisi.php`
- `includes/functions.php`
- `moduller/loader.php`
- `moduller/odeme/paytr/callback.php`
- `moduller/odeme/paytr/fail.php`
- `moduller/odeme/paytr/index.php`
- `moduller/odeme/paytr/success.php`
- `temalar/varsayilan/admin-temalar.php`
- `temalar/varsayilan/alt.php`
- `temalar/varsayilan/odeme.php`
- `temalar/varsayilan/theme.json`
- `temalar/varsayilan/ust.php`
- `temalar/svs-tema/*` (eski `temalar/shoptimizer/*` taşıma + düzenlemeler)

## 2) Manuel Smoke Test Adımları

### 2.1 Tema seçimi admin'den değişince frontend değişiyor mu?
1. Admin paneline giriş yap.
2. `Temalar` ekranına gir.
3. `Varsayilan Tema` ile `Svs Tema` arasında geçiş yap.
4. Ana sayfayı yenile.
5. Beklenen sonuç: seçilen tema anında frontend'e yansır.

### 2.2 Checkout `/siparis-tamamla` POST çalışıyor mu?
1. Sepete ürün ekle.
2. Ödeme ekranına git.
3. Formun `action` adresinin `/siparis-tamamla` olduğunu doğrula.
4. Siparişi tamamla butonuna bas.
5. Beklenen sonuç: istek router üzerinden `CheckoutController@process` akışına düşer.

### 2.3 PayTR callback/success/fail endpointleri PHP olarak çalışıyor mu?
1. `moduller/odeme/paytr/callback.php` endpointine test POST isteği gönder.
2. `moduller/odeme/paytr/success.php?id=<siparis_id>` çağır.
3. `moduller/odeme/paytr/fail.php?id=<siparis_id>` çağır.
4. Beklenen sonuç: endpointler düz metin gibi değil PHP olarak çalışır; callback hash doğrulaması yapar.

### 2.4 Ödeme modülleri (`payment`) loader ile yükleniyor mu?
1. `extensions` tablosunda `type='payment'` ve `status=1` bir modül aktif olsun.
2. Uygulamayı aç.
3. Ödeme ekranında modülün kanca ile geldiğini doğrula.
4. Beklenen sonuç: `moduller/loader.php` payment tiplerini de yükler.

### 2.5 `xml-import` sayfası fatal vermiyor mu?
1. Admin'de `xml-import` ekranını aç.
2. Sayfanın açıldığını ve TCMB kur verisinin üretildiğini kontrol et.
3. Beklenen sonuç: `getTCMBRates()` tanımsız hatası alınmaz.

## 3) DB Değişikliği / Migration Notu

- Bu P0 kapsamındaki düzenlemeler için **zorunlu yeni migration yoktur**.
- Tema adı alias desteği (`shoptimizer -> svs-tema`) kod seviyesinde sağlanmıştır.
- `active_theme` alanı DB'de eski değer olarak `shoptimizer` kalsa bile çalışma anında `svs-tema`ya çözümlenir.


## 4) DirectAdmin Uyumluluk Kontrolü

- `public_html` kök yapı teyidi: proje kökten çalışır, ayrı `public/` document root zorunluluğu yoktur.
- Composer/SSH varsayımı yok: kurulum adımları shared hosting gerçekliğine uygundur.
- `vendor` FTP notu: bağımlılıklar sunucuya FTP ile taşınacak şekilde tasarlanmıştır.
- `.htaccess` kuralları korunur: Apache/LiteSpeed yönlendirme yapısı bozulmaz.
- Yazma yolları shared hosting uyumlu: `storage/`, `storage/logs/`, `data/uploads/` gibi dizinler proje içinde ve göreli yoldadır.
- `open_basedir` etkisi yok: çalışma anında kullanılan dosya yolları `public_html` altında kalacak şekilde planlanmıştır.
