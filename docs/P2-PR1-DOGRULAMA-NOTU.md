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

## 1) Banner Render Testi (Kritik)
1. Admin > Temalar ekranından önce `varsayilan`, sonra `svs-tema` temasını aktif et.
2. Her iki temada da ana sayfayı aç (`/`).
3. Karar çerezi yokken (`bozok_cerez_tercih` silinmişken) banner görünmeli.
4. Banner butonları test et:
   - **Kabul**
   - **Reddet**
   - **Tercihi Kaydet**
5. Karar verildikten sonra sayfa yenile:
   - banner tekrar görünmemeli,
   - kararı sıfırlamak için `bozok_cerez_tercih` çerezini temizleyince tekrar görünmeli.

## 2) `/cerez/tercih` Endpoint Testi (Kritik)
> Test yöntemi: banner formu üzerinden veya doğrudan POST isteği ile.

### 2.a Reddet
- POST: `aksiyon=reddet`
- Beklenen:
  - sadece zorunlu açık,
  - analitik/pazarlama kapalı,
  - `_ga`, `_gid`, `_fbp` vb. mümkün olan analitik/pazarlama çerezleri temizlenir.

### 2.b Kabul
- POST: `aksiyon=kabul`
- Beklenen:
  - analitik/pazarlama/tercih açık kaydedilir.

### 2.c Tercih Kaydet
- POST: `aksiyon=tercih_kaydet` + checkbox değerleri
- Beklenen:
  - seçilen kategoriler açık,
  - seçilmeyenlere göre temizleme tetiklenir.

### DB ve Admin doğrulama
1. `cerez_izin_kayitlari` tablosunda kayıt oluşmalı (`anonim_id`, `karar`, `created_at` dolu).
2. `/admin/cerez-yonetimi.php` ekranındaki kabul/reddet/tercih kartları ve son kayıt listesi güncellenmeli.

## 3) “Doğrudan erişim engellendi.” Konusunun Netleştirilmesi
- Bu metin `moduller/loader.php` içindeki koruma kontrolünden gelir.
- Kök neden: `config/config.php` içinde `includes/functions.php` dosyası yalnızca `e()` fonksiyonuna bakılarak yükleniyordu; helper’dan `e()` önceden geldiyse `$bozkurt` oluşturulmadan loader çalışabiliyordu.
- Düzeltme: `config/config.php` içinde `e()` yerine çekirdek gereksinimler (`$bozkurt`, `gorunum()`, `aktif_tema_ayarla()`) kontrol edilerek `includes/functions.php` garantili yüklendi.
- Sonuç:
  - “doğrudan dosya erişimi engeli” normal güvenlik mekanizması olarak korunur,
  - ancak uygulama ana giriş akışı artık yanlışlıkla bu hataya düşmez,
  - `/cerez/tercih` route’u router üzerinden normal çalışır.

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
