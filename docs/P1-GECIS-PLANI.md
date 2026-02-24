# P1 Geçiş Planı (P0 Sonrası)

## 1) Tema/Kanca Sözleşmesi Kesinleştirme

- Tek tema sözleşmesi dokümanı hazırlanacak:
  - Zorunlu görünüm dosyaları
  - Zorunlu kanca noktaları
  - Metadata standardı (`theme.json` / `style.css`)
- Kanca adlarının nihai listesi belirlenecek:
  - `head_basi`, `head_sonu`, `body_basi`, `body_sonu`, `footer_basi`, `footer_sonu`
- Geçiş dönemi alias desteği için sonlandırma takvimi çıkarılacak.

## 2) Modül Sistemi Standardizasyonu

- `module.json` sözleşmesi tanımlanacak:
  - `name`, `version`, `author`, `type`, `hooks`, `routes`, `permissions`, `settings_schema`
- Options API uygulanacak:
  - `option_get`, `option_set`, `option_delete`
- Admin modül ekranları birleştirilecek:
  - `admin/moduller.php`, `admin/plugins.php`, `ajax/extension-toggle.php` dağınıklığı tek merkeze alınacak.

## 3) Kurumsal CMS Taslağı

- Veri modeli taslağı:
  - Sayfa tablosu, slug, meta alanları, yayın durumu, sıralama
- Endpoint taslağı:
  - `/sayfa/{slug}` SEO odaklı sunum
- Admin ekran taslağı:
  - Listeleme, ekleme, düzenleme, silme
  - Hazır kurumsal sayfa şablonları

## Çıktı Beklentisi
- P1 sonunda tema ve modül geliştirici sözleşmeleri net ve dokümante olacak.
- Kurumsal sayfa yönetimi için tablo + endpoint + admin ekranı uygulanabilir hale gelecek.
