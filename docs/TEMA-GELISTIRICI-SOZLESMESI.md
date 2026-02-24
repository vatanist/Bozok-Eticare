# Tema Geliştirici Sözleşmesi (P1-PR1)

Bu doküman, çekirdekte `core/TemaSozlesmesi.php` ile birebir uyumludur.

## 1) Zorunlu Tema Dosyaları

- `ust.php`
- `alt.php`
- `anasayfa.php`
- `kategori.php`
- `urun-detay.php`
- `sepet.php`
- `odeme.php`

## 2) Metadata Zorunluluğu

Aşağıdakilerden en az biri **kök tema klasöründe** bulunmalıdır:

- `theme.json`
- `style.css` (Theme Name/Version/Author/Description başlıkları)

## 3) Standart Kanca Listesi

- `head_basi`
- `head_sonu`
- `body_basi`
- `body_sonu`
- `footer_basi`
- `footer_sonu`

## 4) Alias Tablosu (Geriye Uyumluluk)

| Eski Kanca | Yeni Kanca |
|---|---|
| `ust_basi` | `head_basi` |
| `ust_sonu` | `head_sonu` |
| `alt_basi` | `footer_basi` |
| `alt_sonu` | `footer_sonu` |

## 5) Admin Tema Doğrulama Ekranı

`admin/themes.php` ekranı artık:

- metadata kaynağını gösterir (`theme.json` / `style.css` / `yok`)
- tema sözleşmesi hatalarını listeler (eksik dosya/metadata)

## 6) DirectAdmin Uyum Notu

- Ek bağımlılık yoktur.
- Composer/SSH zorunlu değildir.
- Tema dosyaları `public_html/temalar/<tema>` yapısında çalışır.
