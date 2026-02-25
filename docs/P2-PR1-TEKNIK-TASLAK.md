# P2-PR1 Teknik Taslak — Çerez Yönetimi (KVKK/GDPR)

## Hedef
- Reddet seçeneğinin gerçek anlamda reddet olması.
- Analitik/pazarlama çerezlerinin izin olmadan çalışmaması.
- KVKK/GDPR için maskeli IP, saklama süresi ve güvenli anonim kimlik.
- DirectAdmin + MultiPHP shared hosting ile ek servis gerektirmeden çalışma.

## Veritabanı Şeması
### `cerez_izin_kayitlari`
- `id` (PK, bigint)
- `anonim_id` (varchar 64)
- `user_id` (int, nullable)
- `ip_adresi` (varchar 64) — varsayılan maskeli
- `user_agent` (varchar 255)
- `karar` (`kabul|reddet|tercih`)
- `analitik_izin` (tinyint)
- `pazarlama_izin` (tinyint)
- `tercih_izin` (tinyint)
- `kaynak` (varchar 50)
- `created_at` (datetime)

### İndeksler
- `idx_cerez_tarih` (`created_at`)
- `idx_cerez_anonim` (`anonim_id`)
- `idx_cerez_user` (`user_id`)
- `idx_cerez_karar` (`karar`)

## Uygulama Akışı
1. Kullanıcıya banner gösterilir (zorunlu hariç tüm kategoriler kapalı).
2. Kullanıcı:
   - **Kabul**: analitik/pazarlama/tercih açık
   - **Reddet**: analitik/pazarlama kapalı, ilgili cookie temizliği
   - **Tercihi Kaydet**: checkbox durumuna göre kayıt
3. Tercih çerezi (`bozok_cerez_tercih`) ve anonim kimlik (`bozok_anon_id`) yazılır.
4. `cerez_izin_kayitlari` tablosuna log düşülür.
5. Analitik izni yoksa `Marketing::logVisitor()` çalışmaz.

## KVKK/GDPR Ayarları
- `gizlilik/tam_ip_sakla` (bool, varsayılan: `false`)
- `gizlilik/kayit_saklama_gunu` (int, varsayılan: `180`)
- Kayıt temizliği periyodik (hafif, istek bazlı olasılıksal)

## Admin Ekranı Checklist
- [x] Kabul / Reddet / Tercih istatistik kartları
- [x] Son tercih listesi (maskeli IP + tarayıcı + tarih)
- [x] Tam IP saklama ayarı
- [x] Kayıt saklama süresi ayarı
- [ ] CSV export (opsiyonel, sonraki PR)

## DirectAdmin Uyumluluk Notu
- SSH/composer zorunluluğu yok.
- Sadece PHP + PDO + mevcut MySQL.
- public_html ve `.htaccess` akışını bozmaz.
- open_basedir uyumlu dosya erişimi.
