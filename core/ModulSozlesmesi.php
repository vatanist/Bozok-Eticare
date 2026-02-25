<?php
/**
 * Bozok E-Ticaret - Modül Sözleşmesi
 *
 * module.json okuma/doğrulama ve modül keşif işlemlerini yönetir.
 */
class ModulSozlesmesi
{
    public static function zorunluAlanlar(): array
    {
        return ['code', 'name', 'version', 'type', 'entry'];
    }

    public static function tipEslemeleri(): array
    {
        return [
            'odeme' => 'payment',
            'payment' => 'payment',
            'genel' => 'module',
            'module' => 'module',
            'kargo' => 'shipping',
            'shipping' => 'shipping',
            'pazarlama' => 'marketing',
            'marketing' => 'marketing',
        ];
    }

    // ===================== BAŞLANGIÇ: MODÜL META OKUMA =====================
    public static function modulMetaOku(string $modul_klasoru, string $kategori, string $kod): array
    {
        $json_dosyasi = rtrim($modul_klasoru, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'module.json';
        $uyarilar = [];

        $meta = [
            'code' => $kod,
            'name' => $kod,
            'version' => '1.0.0',
            'description' => 'Açıklama belirtilmemiş.',
            'author' => 'Bilinmiyor',
            'type' => self::tipEslemeleri()[$kategori] ?? 'module',
            'entry' => 'init.php',
            'settings_schema' => [],
            'metadata_kaynagi' => 'fallback',
        ];

        if (!is_file($json_dosyasi)) {
            $uyarilar[] = 'module.json bulunamadı, init.php başlıkları kullanıldı.';
            return [
                'meta' => self::initBasligindanTamamla($meta, $modul_klasoru),
                'uyarilar' => $uyarilar,
                'gecerli' => false,
            ];
        }

        $icerik = @file_get_contents($json_dosyasi);
        if ($icerik === false) {
            $uyarilar[] = 'module.json okunamadı.';
            return [
                'meta' => self::initBasligindanTamamla($meta, $modul_klasoru),
                'uyarilar' => $uyarilar,
                'gecerli' => false,
            ];
        }

        $cozulmus = json_decode($icerik, true);
        if (!is_array($cozulmus)) {
            $uyarilar[] = 'module.json JSON formatı bozuk.';
            return [
                'meta' => self::initBasligindanTamamla($meta, $modul_klasoru),
                'uyarilar' => $uyarilar,
                'gecerli' => false,
            ];
        }

        $meta = array_merge($meta, $cozulmus);
        $meta['metadata_kaynagi'] = 'module.json';

        $dogrulama = self::metaDogrula($meta, $kategori, $kod);
        $uyarilar = array_merge($uyarilar, $dogrulama['uyarilar']);

        $meta = self::initBasligindanTamamla($meta, $modul_klasoru);

        return [
            'meta' => $meta,
            'uyarilar' => $uyarilar,
            'gecerli' => $dogrulama['gecerli'],
        ];
    }
    // ===================== BİTİŞ: MODÜL META OKUMA =====================

    public static function metaDogrula(array $meta, string $kategori, string $kod): array
    {
        $uyarilar = [];
        $gecerli = true;

        foreach (self::zorunluAlanlar() as $alan) {
            if (!array_key_exists($alan, $meta) || $meta[$alan] === '' || $meta[$alan] === null) {
                $uyarilar[] = $alan . ' zorunlu alanı eksik.';
                $gecerli = false;
            }
        }

        $beklenen_tip = self::tipEslemeleri()[$kategori] ?? 'module';
        if (($meta['type'] ?? '') !== $beklenen_tip) {
            $uyarilar[] = 'type değeri klasör ile uyumlu değil. Beklenen: ' . $beklenen_tip;
            $gecerli = false;
        }

        if (($meta['code'] ?? '') !== $kod) {
            $uyarilar[] = 'code değeri klasör adı ile uyuşmuyor.';
            $gecerli = false;
        }

        if (!empty($meta['settings_schema']) && !is_array($meta['settings_schema'])) {
            $uyarilar[] = 'settings_schema bir dizi olmalı.';
            $gecerli = false;
        }

        if (is_array($meta['settings_schema'] ?? null)) {
            foreach ($meta['settings_schema'] as $indeks => $alan) {
                if (!is_array($alan)) {
                    $uyarilar[] = "settings_schema[$indeks] geçersiz formatta.";
                    $gecerli = false;
                    continue;
                }

                if (empty($alan['key']) || empty($alan['type'])) {
                    $uyarilar[] = "settings_schema[$indeks] için key/type zorunlu.";
                    $gecerli = false;
                }
            }
        }

        return ['gecerli' => $gecerli, 'uyarilar' => $uyarilar];
    }

    private static function initBasligindanTamamla(array $meta, string $modul_klasoru): array
    {
        $init = rtrim($modul_klasoru, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'init.php';
        if (!is_file($init) || !function_exists('dosya_bilgisi_oku')) {
            return $meta;
        }

        $baslik = dosya_bilgisi_oku($init, [
            'name' => 'Module Name',
            'version' => 'Version',
            'description' => 'Description',
            'author' => 'Author',
        ]);

        if (!empty($baslik['name']) && ($meta['name'] === $meta['code'] || empty($meta['name']))) {
            $meta['name'] = $baslik['name'];
        }
        if (!empty($baslik['version']) && ($meta['version'] === '1.0.0' || empty($meta['version']))) {
            $meta['version'] = $baslik['version'];
        }
        if (!empty($baslik['description']) && ($meta['description'] === 'Açıklama belirtilmemiş.' || empty($meta['description']))) {
            $meta['description'] = $baslik['description'];
        }
        if (!empty($baslik['author']) && ($meta['author'] === 'Bilinmiyor' || empty($meta['author']))) {
            $meta['author'] = $baslik['author'];
        }

        return $meta;
    }

    // ===================== BAŞLANGIÇ: MODÜL KEŞFİ =====================
    public static function modulleriKesfet(string $modul_koku): array
    {
        $sonuc = [];
        if (!is_dir($modul_koku)) {
            return $sonuc;
        }

        $kategoriler = array_diff(scandir($modul_koku), ['..', '.', 'loader.php']);
        foreach ($kategoriler as $kategori) {
            $kategori_yolu = $modul_koku . DIRECTORY_SEPARATOR . $kategori;
            if (!is_dir($kategori_yolu)) {
                continue;
            }

            $moduller = array_diff(scandir($kategori_yolu), ['..', '.']);
            foreach ($moduller as $kod) {
                $modul_klasoru = $kategori_yolu . DIRECTORY_SEPARATOR . $kod;
                if (!is_dir($modul_klasoru)) {
                    continue;
                }

                $okuma = self::modulMetaOku($modul_klasoru, $kategori, $kod);
                $meta = $okuma['meta'];
                $meta['kategori'] = $kategori;
                $meta['kod'] = $kod;
                $meta['klasor'] = $modul_klasoru;
                $meta['uyarilar'] = $okuma['uyarilar'];
                $meta['gecerli'] = $okuma['gecerli'];
                $sonuc[] = $meta;
            }
        }

        return $sonuc;
    }
    // ===================== BİTİŞ: MODÜL KEŞFİ =====================
}
