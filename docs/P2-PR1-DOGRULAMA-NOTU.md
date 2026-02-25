# P2-PR1 Doğrulama Notu — Çerez Yönetimi

## Kapsam
- Çerez banner (kabul/reddet/tercih kaydet)
- KVKK/GDPR odaklı IP maskeleme ve saklama süresi
- Çerez tercih kayıtları için admin ekranı
- Analitik loglamanın çerez iznine bağlanması

## Değişen Dosyalar
- `core/CerezYonetimi.php`
- `core/Marketing.php`
- `config/config.php`
- `routes.php`
- `cerez-tercih.php`
- `includes/cerez-banner.php`
- `includes/functions.php`
- `admin/cerez-yonetimi.php`
- `admin/includes/header.php`
- `install.php`
- `setup.php`
- `temalar/varsayilan/alt.php`
- `temalar/svs-tema/alt.php`
- `includes/footer.php`
- `docs/P2-PR1-TEKNIK-TASLAK.md`

## Manuel Smoke Test
1. Frontend’de sayfayı aç ve çerez banner göründüğünü doğrula.
2. **Reddet** tıkla:
   - banner kapanmalı,
   - analitik/pazarlama çerezleri temizlenmeli,
   - `bozok_cerez_tercih` içinde `analitik=false`, `pazarlama=false` olmalı.
3. **Kabul** veya **Tercihi Kaydet** ile yeni seçim yap; çerez değerleri doğru güncellenmeli.
4. Adminde `/admin/cerez-yonetimi.php` aç:
   - kabul/reddet/tercih sayıları artıyor mu kontrol et,
   - son kayıt listesinde IP (maskeli), tarayıcı ve tarih görünüyor mu kontrol et.
5. Adminden `Tam IP sakla` açıp yeni tercih gönder; yeni satırda tam IP yazıldığını doğrula.
6. `kayit_saklama_gunu` değerini düşürüp eski kayıtların temizlenmesini doğrula (zamana bağlı).

## Güvenlik Kontrolü
- Çerez tercih formu CSRF doğrulaması ile korunuyor.
- Harici domaine açık redirect engellendi.
- Varsayılan politika: sadece zorunlu çerez açık.
- Analitik izni yoksa ziyaret logu tutulmuyor.

## DirectAdmin Gerçek DB Smoke Test (Adım Adım)
1. DirectAdmin’de `.env` içine `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` bilgilerini kaydet.
2. `/install.php` (veya mevcut kurulumda `/setup.php`) çalıştır.
3. DB’de `cerez_izin_kayitlari` tablosunun oluştuğunu ve indeksleri (`idx_cerez_tarih`, `idx_cerez_anonim`, `idx_cerez_user`) doğrula.
4. Frontend ana sayfaya gir, çerez banner’dan sırayla **Reddet** ve **Kabul** işlemi yap.
5. DB’de `cerez_izin_kayitlari` tablosunda iki satır oluştuğunu, karar alanlarının doğru olduğunu doğrula.
6. `/admin/cerez-yonetimi.php` ekranında istatistik ve son tercihlerin göründüğünü doğrula.
7. `tam_ip_sakla` ayarı kapalıyken IP’nin maskeli, açıkken tam saklandığını doğrula.

## DirectAdmin Uyumluluk Notu
- Ek daemon/servis eklenmedi.
- Sadece PHP dosyaları + PDO sorguları kullanıldı.
- SSH/composer zorunluluğu yok, vendor FTP akışı bozulmadı.
- public_html kök ve `.htaccess` uyumu korunur.
