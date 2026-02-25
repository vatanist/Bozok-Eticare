# P2-PR2 Teknik Taslak — Ziyaretçi/Analitik + Grafikler + Son 12 Saat

## Hedef
- Çerez analitik izni yoksa **hiçbir analitik olay** yazılmaması.
- Varsayılan maskeli IP, sadece admin ayarı ile tam IP.
- 12 saatlik metriklerin hızlı sorgulanması için hafif event tablosu + indeksler.
- DirectAdmin + MultiPHP shared hosting uyumu (ek servis yok).

## Veritabanı Şeması
### 1) `analytics_events` (yeni)
- `id` (BIGINT PK)
- `event_name` (`page_view`, `add_to_cart`, ...)
- `page_url`, `referrer`
- `product_id`, `user_id`
- `anonim_id`, `session_id`
- `ip` (maskeli/tam)
- `il`, `ilce`, `tarayici`, `cihaz_tipi`, `user_agent`
- `created_at`

### 2) `visitor_logs` (mevcut + indeks)
- `idx_visitor_created_at` (`created_at`)
- `idx_visitor_session` (`session_id`)

## Performans İndeksleri (`analytics_events`)
- `idx_event_created_at` (`created_at`)
- `idx_event_adi_tarih` (`event_name`, `created_at`)
- `idx_event_anonim_tarih` (`anonim_id`, `created_at`)
- `idx_event_user_tarih` (`user_id`, `created_at`)

## Olay Akışı
1. `config/config.php` içinde çerez analitik izni kontrolü geçerse `Marketing::logVisitor()` çalışır.
2. `logVisitor()`:
   - `visitor_logs` kaydı
   - `analytics_events` içine `page_view` olayı
3. `CartController@add()` başarılı olursa `Marketing::olayKaydet('add_to_cart')` çağrılır.
4. `olayKaydet()` içinde tekrar izin kontrolü vardır (ikinci güvenlik katmanı).

## Admin Dashboard Checklist
- [x] Son 12 saat görüntüleme kartı
- [x] Son 12 saat sepete ekleme kartı
- [x] Toplam olay / tekil anonim ziyaretçi kartları
- [x] Son 12 saat grafiği (page_view vs add_to_cart)
- [x] İl bazlı dağılım tablosu
- [x] Tarayıcı dağılım tablosu
- [x] Son olaylar listesi

## DirectAdmin Uyumluluk Notu
- SSH/composer zorunluluğu yok.
- Sadece PHP + PDO + MySQL sorguları.
- public_html + `.htaccess` + open_basedir ile uyumlu.
