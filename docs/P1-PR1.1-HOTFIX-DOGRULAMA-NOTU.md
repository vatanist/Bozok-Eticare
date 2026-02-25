# P1-PR1.1 Hotfix Doğrulama Notu (Admin Görünüm Tema Bağımsızlığı)

## Problem
`admin/themes.php` ve `admin/plugins.php` sayfaları `gorunum()` ile aktif temadan admin görünümü yüklediği için,
aktif tema `svs-tema` olduğunda (admin-temalar/admin-eklentiler görünümü yoksa) sayfalar patlayabiliyordu.

## Çözüm
- `includes/functions.php` içine `gorunum_tema($yol, $veriler, $tema_adi)` fonksiyonu eklendi.
- `admin/themes.php` görünümü artık zorunlu olarak `varsayilan` temasından yükleniyor.
- `admin/plugins.php` görünümü artık zorunlu olarak `varsayilan` temasından yükleniyor.

## Manuel Smoke Test
1. Admin giriş yap.
2. Tema `svs-tema` olarak aktif et.
3. `/admin/themes.php` aç: sayfa render olmalı, patlamamalı.
4. `varsayilan <-> svs-tema` geçişi yap: ekran render olmaya devam etmeli.
5. `/admin/plugins.php` aç: sayfa render olmalı, patlamamalı.

## DirectAdmin Uyumluluk Notu
- Ek bağımlılık yok.
- Composer/SSH zorunlu değil.
- Tüm yollar proje içi (`public_html`) göreli path ile çalışır.
