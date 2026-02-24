# P1-PR1 Doğrulama Notu (Tema/Kanca Sözleşmesi)

## Yapılanlar

- Kanca sözleşmesi ve alias tablosu çekirdekte tek kaynakta toplandı: `core/TemaSozlesmesi.php`
- `includes/functions.php` içindeki kanca çözümleme bu tek kaynağa bağlandı.
- `admin/themes.php` tema metadata + zorunlu dosya doğrulamasını sözleşme sınıfı üzerinden yapar hale getirildi.
- Tema geliştirici dokümanı, uygulama ile birebir eşitlendi: `docs/TEMA-GELISTIRICI-SOZLESMESI.md`

## Manuel Smoke Test Adımları

1. `admin/themes.php` açılır.
2. Her tema kartında metadata kaynağı görünür (`theme.json`/`style.css`/`yok`).
3. Eksik dosyalı test tema oluşturulursa doğrulama uyarısı listelenir.
4. Eski kanca adı kullanan tema/modül (`ust_basi` gibi) çalıştırıldığında içerik yine render olur.

## DirectAdmin Uyumluluk Notu

- Ek paket/bağımlılık yok.
- `public_html` kök yapıyı bozacak dizin ayrımı yok.
- Dosya yolları proje içi göreli path ile çalışır.
