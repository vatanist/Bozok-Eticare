<?php
/**
 * Bozok E-Ticaret - Seçenek Servisi
 *
 * Global seçenek (Options API) işlemlerini yönetir.
 */
class SecenekServisi
{
    private static $onbellek = [];
    private static $tablo_hazir = false;

    // ===================== BAŞLANGIÇ: TABLO HAZIRLAMA =====================
    private static function tabloyuHazirla(): void
    {
        if (self::$tablo_hazir) {
            return;
        }

        try {
            Database::query("CREATE TABLE IF NOT EXISTS `core_options` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `grup_anahtari` VARCHAR(120) NOT NULL,
                `secenek_anahtari` VARCHAR(150) NOT NULL,
                `deger` LONGTEXT NULL,
                `deger_tipi` VARCHAR(20) NOT NULL DEFAULT 'string',
                `autoload` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_grup_secenek` (`grup_anahtari`, `secenek_anahtari`),
                KEY `idx_grup` (`grup_anahtari`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");
        } catch (Throwable $hata) {
            error_log('SecenekServisi tablo hazırlama hatası: ' . $hata->getMessage());
        }

        self::$tablo_hazir = true;
    }
    // ===================== BİTİŞ: TABLO HAZIRLAMA =====================

    private static function onbellekAnahtari(string $anahtar, string $grup): string
    {
        return $grup . ':' . $anahtar;
    }

    private static function tipBelirle($deger): string
    {
        if (is_bool($deger)) {
            return 'bool';
        }
        if (is_int($deger)) {
            return 'int';
        }
        if (is_float($deger)) {
            return 'float';
        }
        if (is_array($deger)) {
            return 'json';
        }
        return 'string';
    }

    private static function depola($deger, string $tip): string
    {
        if ($tip === 'json') {
            return (string) json_encode($deger, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        if ($tip === 'bool') {
            return $deger ? '1' : '0';
        }
        return (string) $deger;
    }

    private static function cozumle($deger, string $tip)
    {
        switch ($tip) {
            case 'bool':
                return $deger === '1' || $deger === 1 || $deger === true;
            case 'int':
                return (int) $deger;
            case 'float':
                return (float) $deger;
            case 'json':
                $cozulmus = json_decode((string) $deger, true);
                return is_array($cozulmus) ? $cozulmus : [];
            default:
                return (string) $deger;
        }
    }

    // ===================== BAŞLANGIÇ: OPTIONS API =====================
    public static function getir(string $anahtar, $varsayilan = null, string $grup = 'genel')
    {
        self::tabloyuHazirla();

        $onbellek_anahtari = self::onbellekAnahtari($anahtar, $grup);
        if (array_key_exists($onbellek_anahtari, self::$onbellek)) {
            return self::$onbellek[$onbellek_anahtari];
        }

        try {
            $satir = Database::fetch(
                "SELECT deger, deger_tipi FROM core_options WHERE grup_anahtari = ? AND secenek_anahtari = ? LIMIT 1",
                [$grup, $anahtar]
            );
        } catch (Throwable $hata) {
            error_log('SecenekServisi getir hatası: ' . $hata->getMessage());
            return $varsayilan;
        }

        if (!$satir) {
            return $varsayilan;
        }

        $deger = self::cozumle($satir['deger'], $satir['deger_tipi'] ?? 'string');
        self::$onbellek[$onbellek_anahtari] = $deger;
        return $deger;
    }

    public static function yaz(string $anahtar, $deger, string $grup = 'genel', bool $autoload = false): bool
    {
        self::tabloyuHazirla();

        $tip = self::tipBelirle($deger);
        $db_deger = self::depola($deger, $tip);

        try {
            $mevcut = Database::fetch(
                "SELECT id FROM core_options WHERE grup_anahtari = ? AND secenek_anahtari = ? LIMIT 1",
                [$grup, $anahtar]
            );

            if ($mevcut) {
                Database::query(
                    "UPDATE core_options SET deger = ?, deger_tipi = ?, autoload = ? WHERE id = ?",
                    [$db_deger, $tip, $autoload ? 1 : 0, (int) $mevcut['id']]
                );
            } else {
                Database::query(
                    "INSERT INTO core_options (grup_anahtari, secenek_anahtari, deger, deger_tipi, autoload)
                     VALUES (?, ?, ?, ?, ?)",
                    [$grup, $anahtar, $db_deger, $tip, $autoload ? 1 : 0]
                );
            }
        } catch (Throwable $hata) {
            error_log('SecenekServisi yaz hatası: ' . $hata->getMessage());
            return false;
        }

        self::$onbellek[self::onbellekAnahtari($anahtar, $grup)] = $deger;
        return true;
    }

    public static function sil(string $anahtar, string $grup = 'genel'): bool
    {
        self::tabloyuHazirla();

        try {
            Database::query(
                "DELETE FROM core_options WHERE grup_anahtari = ? AND secenek_anahtari = ?",
                [$grup, $anahtar]
            );
        } catch (Throwable $hata) {
            error_log('SecenekServisi sil hatası: ' . $hata->getMessage());
            return false;
        }

        unset(self::$onbellek[self::onbellekAnahtari($anahtar, $grup)]);
        return true;
    }

    public static function varMi(string $anahtar, string $grup = 'genel'): bool
    {
        self::tabloyuHazirla();

        try {
            $satir = Database::fetch(
                "SELECT id FROM core_options WHERE grup_anahtari = ? AND secenek_anahtari = ? LIMIT 1",
                [$grup, $anahtar]
            );
        } catch (Throwable $hata) {
            error_log('SecenekServisi varMi hatası: ' . $hata->getMessage());
            return false;
        }

        return !empty($satir['id']);
    }

    public static function grupGetir(string $grup = 'genel'): array
    {
        self::tabloyuHazirla();

        try {
            $satirlar = Database::fetchAll(
                "SELECT secenek_anahtari, deger, deger_tipi FROM core_options WHERE grup_anahtari = ? ORDER BY secenek_anahtari ASC",
                [$grup]
            );
        } catch (Throwable $hata) {
            error_log('SecenekServisi grupGetir hatası: ' . $hata->getMessage());
            return [];
        }

        $sonuc = [];
        foreach ($satirlar as $satir) {
            $cozulmus = self::cozumle($satir['deger'], $satir['deger_tipi'] ?? 'string');
            $sonuc[$satir['secenek_anahtari']] = $cozulmus;
            self::$onbellek[self::onbellekAnahtari($satir['secenek_anahtari'], $grup)] = $cozulmus;
        }

        return $sonuc;
    }
    // ===================== BİTİŞ: OPTIONS API =====================
}
