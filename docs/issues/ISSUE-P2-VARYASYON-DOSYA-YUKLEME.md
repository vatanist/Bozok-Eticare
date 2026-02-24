# Issue: P2/P3 Backlog - Varyasyon Bazlı Dosya Yükleme

## Kapsam
Ürün özelleştirme (varyasyon/seçenek) adımında dosya yükleme alanı eklenmesi.

## Gereksinimler
- Varyasyon/opsiyon bazlı dosya yükleme alanı
- Kategori bazlı aktif/pasif
- Ürün bazlı aktif/pasif
- Güvenlik:
  - Uzantı whitelist
  - MIME doğrulama
  - Boyut limiti
  - PHP çalıştırma engeli
- Yönetim:
  - Admin panelde yüklenen dosyaların ürün/kullanıcı bazında listelenmesi

## Kabul Kriteri (Backlog Tanımı)
- Desteklenmeyen uzantılar reddedilir.
- Yetkisiz dosya erişimi engellenir.
- Admin'de dosya geçmişi izlenebilir.

## Not
Bu iş maddesi P0/P1 dışıdır; P2/P3 fazında ele alınacaktır.
