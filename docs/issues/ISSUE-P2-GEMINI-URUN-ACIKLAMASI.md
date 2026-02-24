# Issue: P2/P3 Backlog - Gemini ile Ürün Açıklaması Üretimi

## Kapsam
Admin panelden yönetilebilir Gemini entegrasyonu ile ürün açıklaması üretimi.

## Gereksinimler
- Yapay zekâ ayar ekranı:
  - Gemini API anahtarı (gizli saklama)
  - Mod seçimi: `manuel` / `otomatik`
  - Prompt şablonu yönetimi
- Manuel mod:
  - Ürün formunda `Açıklama Oluştur` butonu
- Otomatik mod:
  - Ürün ekleme/güncelleme sırasında açıklama üretimi
- Loglama:
  - İstek sayısı, hata detayları, zaman damgası
- Güvenlik/perf:
  - Rate limit
  - Timeout
  - Fallback metni

## Kabul Kriteri (Backlog Tanımı)
- API anahtarı düz metin görünmez.
- Manuel/otomatik modlar adminden değiştirilebilir.
- Hata anında ürün kaydı bloklanmadan fallback ile tamamlanır.

## Not
Bu iş maddesi P0/P1 dışıdır; P2/P3 fazında ele alınacaktır.
