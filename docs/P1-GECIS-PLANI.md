# P1 Geçiş Planı (P0 Sonrası)

## 1) Tema/Kanca Sözleşmesi (Tek Liste)

### 1.1 Tema zorunlu dosyaları

- `ust.php`
- `alt.php`
- `anasayfa.php`
- `kategori.php`
- `urun-detay.php`
- `sepet.php`
- `odeme.php`
- `sayfa.php`
- `hesabim/` alt görünümleri (profil, adres, sipariş vb.)
- Tema metadata: `theme.json` **veya** kök `style.css` başlığı

### 1.2 Standart kanca listesi

- `head_basi`
- `head_sonu`
- `body_basi`
- `body_sonu`
- `footer_basi`
- `footer_sonu`

### 1.3 Alias tablosu (eski -> yeni)

| Eski Kanca | Yeni Kanca |
|---|---|
| `ust_basi` | `head_basi` |
| `ust_sonu` | `head_sonu` |
| `alt_basi` | `footer_basi` |
| `alt_sonu` | `footer_sonu` |

## 2) Modül Standardı

### 2.1 `module.json` şema taslağı

```json
{
  "name": "Ornek Modul",
  "code": "ornek-modul",
  "version": "1.0.0",
  "author": "Bozok E-Ticaret",
  "type": "module",
  "category": "genel",
  "description": "Örnek modül açıklaması",
  "hooks": [
    "head_sonu",
    "footer_sonu"
  ],
  "routes": [
    {
      "method": "GET",
      "path": "/modul/ornek",
      "handler": "OrnekController@index",
      "middleware": ["AuthMiddleware"]
    }
  ],
  "permissions": [
    "admin.manage_extensions"
  ],
  "settings_schema": {
    "modul_aktif": {
      "tip": "bool",
      "varsayilan": true
    },
    "baslik": {
      "tip": "metin",
      "varsayilan": ""
    }
  }
}
```

### 2.2 Options API fonksiyon listesi

- `option_get(string $anahtar, mixed $varsayilan = null): mixed`
- `option_set(string $anahtar, mixed $deger, string $grup = 'genel'): bool`
- `option_delete(string $anahtar): bool`
- `option_has(string $anahtar): bool`
- `option_get_group(string $grup): array`

### 2.3 Modül yönetim ekranı hedefi

- Tek ekran: `admin/moduller.php`
- Alt sekmeler:
  - Kurulu
  - Kurulabilir
  - Eksik (DB var, dosya yok)
  - Ayarlar
- Mevcut dağınık uçların birleştirilmesi:
  - `admin/plugins.php`
  - `ajax/extension-toggle.php`

## 3) Kurumsal CMS Taslağı

### 3.1 Tablo taslağı

#### `cms_pages`
- `id` (PK)
- `title`
- `slug` (UNIQUE)
- `icerik`
- `meta_title`
- `meta_description`
- `canonical_url`
- `sablon`
- `durum` (`taslak`/`yayinda`)
- `siralama`
- `created_at`
- `updated_at`

#### `cms_page_revisions` (opsiyonel ama önerilen)
- `id` (PK)
- `page_id` (FK -> `cms_pages.id`)
- `icerik`
- `duzenleyen_user_id`
- `created_at`

### 3.2 Admin ekranları

- `admin/cms-sayfalar.php` (liste)
- `admin/cms-sayfa-form.php` (ekle/düzenle)
- `admin/cms-sayfa-sil.php` (sil)
- `admin/cms-sayfa-onizleme.php` (önizleme)

### 3.3 Route listesi (taslak)

- `GET /sayfa/{slug}` -> `PageController@show`
- `GET /admin/cms-sayfalar.php` -> sayfa listesi
- `GET /admin/cms-sayfa-form.php` -> form görüntüle
- `POST /admin/cms-sayfa-form.php` -> kaydet
- `POST /admin/cms-sayfa-sil.php` -> sil
- `GET /sitemap.xml` içine `cms_pages` (yayında olanlar) eklenir

## 4) DirectAdmin + Shared Hosting Uygulama Notu

- Composer/SSH zorunlu adım **olmayacak**.
- Dosya yapısı `public_html` köküne uygun tutulacak.
- `vendor/` dizini FTP ile taşınabilir kalacak.
- `.htaccess` ile Apache/LiteSpeed uyumluluğu korunacak.
- Tüm runtime yazma yolları (`storage`, `logs`, `uploads`) göreli ve hosting dostu kalacak.
