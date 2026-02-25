<?php
/**
 * Bozok E-Ticaret - Seçenek Servisi
 *
 * Global seçenek (Options API) işlemlerini yönetir.
 */
class SecenekServisi
{
    private static $onbellek = [];
    private static $tablo_durumu_biliniyor = false;
    private static $tablo_var = false;

    // ===================== BAŞLANGIÇ: TABLO DURUM KONTROLÜ =====================
    private static function tabloVarMi(): bool
    {
        if (self::$tablo_durumu_biliniyor) {
            return self::$tablo_var;
        }

        self::$tablo_durumu_biliniyor = true;

        if (!class_exists('Database')) {
            error_log('SecenekServisi tablo kontrolü: Database sınıfı bulunamadı.');
            self::$tablo_var = false;
            return false;
        }

        try {
            $satir = Database::fetch("SHOW TABLES LIKE 'core_options'");
            self::$tablo_var = !empty($satir);
        } catch (Throwable $hata) {
            error_log('SecenekServisi tablo kontrol hatası: ' . $hata->getMessage());
            self::$tablo_var = false;
        }

        if (!self::$tablo_var) {
            error_log('SecenekServisi: core_options tablosu bulunamadı. Kurulum adımını çalıştırın.');
        }

        return self::$tablo_var;
    }
    // ===================== BİTİŞ: TABLO DURUM KONTROLÜ =====================

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

    private static function depola($deger, string $tip)
    {
        if ($tip === 'json') {
            $json = json_encode($deger, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                error_log('SecenekServisi json encode hatası: ' . json_last_error_msg());
                return false;
            }
            return $json;
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
        if (!self::tabloVarMi()) {
            return $varsayilan;
        }

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
        if (!self::tabloVarMi()) {
            return false;
        }

        $tip = self::tipBelirle($deger);
        $db_deger = self::depola($deger, $tip);

        if ($db_deger === false && $tip === 'json') {
            return false;
        }

        try {
            Database::query(
                "INSERT INTO core_options (grup_anahtari, secenek_anahtari, deger, deger_tipi, autoload)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    deger = VALUES(deger),
                    deger_tipi = VALUES(deger_tipi),
                    autoload = VALUES(autoload),
                    updated_at = CURRENT_TIMESTAMP",
                [$grup, $anahtar, (string) $db_deger, $tip, $autoload ? 1 : 0]
            );
        } catch (Throwable $hata) {
            error_log('SecenekServisi yaz hatası: ' . $hata->getMessage());
            return false;
        }

        self::$onbellek[self::onbellekAnahtari($anahtar, $grup)] = $deger;
        return true;
    }

    public static function sil(string $anahtar, string $grup = 'genel'): bool
    {
        if (!self::tabloVarMi()) {
            return false;
        }

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
        if (!self::tabloVarMi()) {
            return false;
        }

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
        if (!self::tabloVarMi()) {
            return [];
        }

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
