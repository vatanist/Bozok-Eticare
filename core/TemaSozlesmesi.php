<?php
/**
 * Bozok E-Ticaret Tema Sözleşmesi Çekirdeği
 */
class TemaSozlesmesi
{
    // ===================== BAŞLANGIÇ: KANCA SÖZLEŞMESİ =====================
    /**
     * Sistemin desteklediği standart kanca listesi.
     */
    public static function standartKancalar(): array
    {
        return [
            'head_basi',
            'head_sonu',
            'body_basi',
            'body_sonu',
            'footer_basi',
            'footer_sonu',
        ];
    }

    /**
     * Eski tema kancaları için geriye uyumluluk haritası.
     */
    public static function kancaTakmaAdlari(): array
    {
        return [
            // Eski isimler
            'ust_basi' => 'head_basi',
            'ust_sonu' => 'head_sonu',
            'alt_basi' => 'footer_basi',
            'alt_sonu' => 'footer_sonu',

            // Geçiş/fallback (standart isimler açıkça korunur)
            'head_basi' => 'head_basi',
            'head_sonu' => 'head_sonu',
            'body_basi' => 'body_basi',
            'body_sonu' => 'body_sonu',
            'footer_basi' => 'footer_basi',
            'footer_sonu' => 'footer_sonu',
        ];
    }

    /**
     * Gelen kanca adını standart ada çözümler.
     */
    public static function kancaAdiCozumle(string $kanca_adi): string
    {
        $takma_adlar = self::kancaTakmaAdlari();
        return $takma_adlar[$kanca_adi] ?? $kanca_adi;
    }
    // ===================== BİTİŞ: KANCA SÖZLEŞMESİ =====================

    // ===================== BAŞLANGIÇ: TEMA DOSYA SÖZLEŞMESİ =====================
    /**
     * Tema için zorunlu temel görünüm dosyaları.
     */
    public static function zorunluTemaDosyalari(): array
    {
        return [
            'ust.php',
            'alt.php',
            'anasayfa.php',
            'kategori.php',
            'urun-detay.php',
            'sepet.php',
            'odeme.php',
        ];
    }

    /**
     * Tema metadata bilgisini theme.json veya style.css üzerinden okur.
     */
    public static function temaMetadataOku(string $tema_klasoru): array
    {
        $varsayilan = [
            'name' => basename($tema_klasoru),
            'version' => '1.0.0',
            'author' => 'Bilinmiyor',
            'description' => 'Açıklama belirtilmemiş.',
            'kaynak' => 'yok',
        ];

        $json_dosyasi = $tema_klasoru . DIRECTORY_SEPARATOR . 'theme.json';
        if (is_file($json_dosyasi)) {
            $icerik = @file_get_contents($json_dosyasi);
            $json = $icerik ? json_decode($icerik, true) : null;
            if (is_array($json)) {
                $varsayilan['name'] = trim((string) ($json['name'] ?? $varsayilan['name']));
                $varsayilan['version'] = trim((string) ($json['version'] ?? $varsayilan['version']));
                $varsayilan['author'] = trim((string) ($json['author'] ?? $varsayilan['author']));
                $varsayilan['description'] = trim((string) ($json['description'] ?? $varsayilan['description']));
                $varsayilan['kaynak'] = 'theme.json';
                return $varsayilan;
            }
        }

        $style_dosyasi = $tema_klasoru . DIRECTORY_SEPARATOR . 'style.css';
        if (is_file($style_dosyasi)) {
            $stiller = self::stilBasliklariniOku($style_dosyasi);
            $varsayilan['name'] = $stiller['Theme Name'] ?: $varsayilan['name'];
            $varsayilan['version'] = $stiller['Version'] ?: $varsayilan['version'];
            $varsayilan['author'] = $stiller['Author'] ?: $varsayilan['author'];
            $varsayilan['description'] = $stiller['Description'] ?: $varsayilan['description'];
            $varsayilan['kaynak'] = 'style.css';
        }

        return $varsayilan;
    }

    /**
     * Tema sözleşmesini doğrular.
     */
    public static function temaSozlesmesiniDogrula(string $tema_klasoru): array
    {
        $hatalar = [];

        $json_var = is_file($tema_klasoru . DIRECTORY_SEPARATOR . 'theme.json');
        $style_var = is_file($tema_klasoru . DIRECTORY_SEPARATOR . 'style.css');
        if (!$json_var && !$style_var) {
            $hatalar[] = 'Metadata eksik: theme.json veya style.css bulunamadı.';
        }

        foreach (self::zorunluTemaDosyalari() as $dosya) {
            if (!is_file($tema_klasoru . DIRECTORY_SEPARATOR . $dosya)) {
                $hatalar[] = 'Eksik dosya: ' . $dosya;
            }
        }

        return [
            'gecerli' => empty($hatalar),
            'hatalar' => $hatalar,
        ];
    }

    /**
     * Kök style.css başlıklarını okur.
     */
    private static function stilBasliklariniOku(string $dosya): array
    {
        $aranan = ['Theme Name', 'Version', 'Author', 'Description'];
        $sonuc = array_fill_keys($aranan, '');
        $icerik = @file_get_contents($dosya, false, null, 0, 4096) ?: '';

        foreach ($aranan as $baslik) {
            if (preg_match('/' . preg_quote($baslik, '/') . ':(.*)$/mi', $icerik, $eslesen)) {
                $sonuc[$baslik] = trim($eslesen[1]);
            }
        }

        return $sonuc;
    }
    // ===================== BİTİŞ: TEMA DOSYA SÖZLEŞMESİ =====================
}
