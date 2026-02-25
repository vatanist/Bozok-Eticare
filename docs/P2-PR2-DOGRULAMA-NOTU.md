# P2-PR2 Doğrulama Notu — Ziyaretçi/Analitik + Grafikler

## Kapsam
- Analitik olay altyapısı (`analytics_events`)
- Son 12 saat metrikleri
- Admin analitik dashboard (grafik + tablolar)
- Çerez izni yoksa analitik yazmama kuralı

## Değişen Dosyalar
- `core/Marketing.php`
- `app/Controllers/CartController.php`
- `admin/istatistikler.php`
- `install.php`
- `setup.php`
- `docs/P2-PR2-TEKNIK-TASLAK.md`

## Fonksiyonel Test Adımları
1. Çerez tercihini **reddet** yap.
2. Frontend’de farklı sayfaları gez ve sepete ürün eklemeyi dene.
3. DB’de `analytics_events` tablosuna yeni kayıt düşmediğini doğrula.
4. Çerez tercihini **kabul** yap.
5. Tekrar sayfa gez, sonra sepete ürün ekle.
6. DB’de `analytics_events` içinde:
   - `event_name=page_view` kayıtları,
   - `event_name=add_to_cart` kayıtları
   oluştuğunu doğrula.
7. `/admin/istatistikler.php` ekranında:
   - son 12 saat görüntüleme/sepete ekleme kartları,
   - grafik,
   - il/tarayıcı dağılımları,
   - son olaylar listesi
   güncellendiğini doğrula.

## Güvenlik ve KVKK Doğrulama
- Analitik izni yoksa olay yazımı kapalı.
- IP varsayılan maskeli saklanır.
- `tam_ip_sakla` açık ise tam IP saklanır.

## DirectAdmin Gerçek DB Smoke Test (Adım Adım)
1. DirectAdmin `.env` içine DB bilgilerini gir (`DB_HOST/DB_NAME/DB_USER/DB_PASS`).
2. `/install.php` (veya güncel kurulumda `/setup.php`) çalıştır.
3. DB’de `analytics_events` tablosunu ve indeksleri doğrula:
   - `idx_event_created_at`
   - `idx_event_adi_tarih`
   - `idx_event_anonim_tarih`
   - `idx_event_user_tarih`
4. Çerez banner’dan **Reddet** seç, birkaç sayfa gez; tabloda kayıt yazılmadığını kontrol et.
5. Çerez banner’dan **Kabul** seç, ana sayfa + ürün detayı + sepete ekle akışını çalıştır.
6. `analytics_events` içinde `page_view` ve `add_to_cart` kayıtlarını kontrol et.
7. `/admin/istatistikler.php` ekranında son 12 saat metrikleri ve grafiklerin dolduğunu doğrula.

## DirectAdmin Uyumluluk Notu
- Ek servis/daemon yok.
- Sadece PHP dosyaları + PDO sorguları kullanıldı.
- SSH/composer zorunluluğu yok, vendor FTP akışı korunur.
- public_html kök ve `.htaccess` uyumu korunur.
