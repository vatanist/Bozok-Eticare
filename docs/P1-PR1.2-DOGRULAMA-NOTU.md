# P1-PR1.2 Doğrulama Notu

## Yapılan Düzeltmeler

- `gorunum_tema()` güvenlik kontrolleri güçlendirildi (yol/tema adı doğrulama, `is_file`, güvenli `extract`).
- `gorunum()` için aktif tema -> `varsayilan` tema fallback akışı eklendi.
- Asset uyumu düzeltildi: `render_tema_adi` ile fallback render sırasında `tema_linki()/tema_yolu()` doğru tema varlıklarını kullanır.
- `TemaSozlesmesi` güncellendi:
  - Minimum zorunlu dosyalar ayrı tutuldu.
  - Opsiyonel/override edilebilir dosyalar ayrı tutuldu.
  - Opsiyonel eksikler hata değil uyarı olarak dönüyor.
- Admin UI temadan ayrıldı:
  - `gorunum_admin()` eklendi.
  - `admin/themes.php` ve `admin/plugins.php` artık `admin/views/` altını kullanıyor.
- Router link hizalama için `url()` yardımcısı eklendi ve temel tema bağlantıları güncellendi.
- `dogrula_csrf()` içinde token karşılaştırması `hash_equals` ile güvenli hale getirildi.

## Manuel Smoke Test Adımları

1. `svs-tema` aktifken üye paneli (hesap-*) ekranlarını aç:
   - Eksik dosyalarda `varsayilan` fallback ile sayfa açılmalı.
2. `/admin/themes.php` ve `/admin/plugins.php` aç:
   - Admin ekranları tema bağımsız render olmalı.
3. `varsayilan <-> svs-tema` arasında tema değiştir:
   - Admin görünümü stabil kalmalı, tema değişiminden etkilenmemeli.
4. Tema üst/alt linklerinden `ara`, `sepet`, `hesabim`, `urunler` rotalarını tıkla:
   - Router URL’leri çalışmalı.
5. DirectAdmin kontrolü:
   - Ek bağımlılık yok, composer/ssh zorunluluğu yok, pathler proje içinde.

## DirectAdmin Uyumluluk Notu

- SSH/composer bağımlılığı eklenmedi.
- `vendor` FTP yaklaşımına aykırı tasarım eklenmedi.
- Yollar proje kök/public_html içinde kaldı; `open_basedir` ile uyumlu.
