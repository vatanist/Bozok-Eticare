<?php

/**
 * Çerez tercih yönetimi ve KVKK/GDPR uyum yardımcıları.
 */
class CerezYonetimi
{
    public const TERCIH_CEREZI = 'bozok_cerez_tercih';
    public const ANONIM_CEREZ = 'bozok_anon_id';

    /**
     * Varsayılan tercih: sadece zorunlu çerezler açık.
     */
    public static function varsayilanTercih(): array
    {
        return [
            'zorunlu' => true,
            'analitik' => false,
            'pazarlama' => false,
            'tercih' => false,
            'karar' => 'bekleniyor',
            'zaman' => date('c'),
        ];
    }

    /**
     * Tarayıcıdaki tercih çerezini çözümler.
     */
    public static function tercihleriOku(): array
    {
        $ham = $_COOKIE[self::TERCIH_CEREZI] ?? '';
        if (!is_string($ham) || $ham === '') {
            return self::varsayilanTercih();
        }

        $veri = json_decode($ham, true);
        if (!is_array($veri)) {
            return self::varsayilanTercih();
        }

        return [
            'zorunlu' => true,
            'analitik' => !empty($veri['analitik']),
            'pazarlama' => !empty($veri['pazarlama']),
            'tercih' => !empty($veri['tercih']),
            'karar' => in_array(($veri['karar'] ?? 'bekleniyor'), ['kabul', 'reddet', 'tercih', 'bekleniyor'], true)
                ? $veri['karar']
                : 'bekleniyor',
            'zaman' => is_string($veri['zaman'] ?? null) ? $veri['zaman'] : date('c'),
        ];
    }

    /**
     * Kullanıcı analitik izni verdi mi?
     */
    public static function analitikIzniVarMi(): bool
    {
        $tercih = self::tercihleriOku();
        return !empty($tercih['analitik']);
    }

    /**
     * Banner tekrar gösterilmeli mi?
     */
    public static function bannerGosterilsinMi(): bool
    {
        $tercih = self::tercihleriOku();
        return ($tercih['karar'] ?? 'bekleniyor') === 'bekleniyor';
    }

    /**
     * Tercih kaydı, çerez yazma ve admin rapor tablosuna loglama.
     */
    public static function tercihKaydet(array $girdi): bool
    {
        $aksiyon = (string) ($girdi['aksiyon'] ?? '');
        $tercih = self::varsayilanTercih();

        if ($aksiyon === 'kabul') {
            $tercih['analitik'] = true;
            $tercih['pazarlama'] = true;
            $tercih['tercih'] = true;
            $tercih['karar'] = 'kabul';
        } elseif ($aksiyon === 'reddet') {
            $tercih['karar'] = 'reddet';
            self::analitikPazarlamaCerezleriniSil();
        } elseif ($aksiyon === 'tercih_kaydet') {
            $tercih['analitik'] = !empty($girdi['analitik']);
            $tercih['pazarlama'] = !empty($girdi['pazarlama']);
            $tercih['tercih'] = !empty($girdi['tercih']);
            $tercih['karar'] = 'tercih';

            if (!$tercih['analitik'] || !$tercih['pazarlama']) {
                self::analitikPazarlamaCerezleriniSil();
            }
        } else {
            return false;
        }

        $tercih['zaman'] = date('c');
        self::tercihCereziniYaz($tercih);
        self::anonimKimlikGetir();
        self::tercihKaydiniYaz($tercih);
        self::eskiKayitlariTemizle();

        return true;
    }

    /**
     * Yönetim paneli için özet istatistik döner.
     */
    public static function ozetIstatistikGetir(): array
    {
        $bos = [
            'kabul' => 0,
            'reddet' => 0,
            'tercih' => 0,
            'toplam' => 0,
        ];

        try {
            $satirlar = Database::fetchAll("SELECT karar, COUNT(*) AS adet FROM cerez_izin_kayitlari GROUP BY karar");
            foreach ($satirlar as $satir) {
                $karar = (string) ($satir['karar'] ?? '');
                $adet = (int) ($satir['adet'] ?? 0);
                if (isset($bos[$karar])) {
                    $bos[$karar] = $adet;
                }
                $bos['toplam'] += $adet;
            }
        } catch (Throwable $e) {
            error_log('CerezYonetimi::ozetIstatistikGetir hatası: ' . $e->getMessage());
        }

        return $bos;
    }

    /**
     * Son tercih kayıtları (admin listesi).
     */
    public static function sonKayitlariGetir(int $limit = 50): array
    {
        try {
            $limit = max(1, min(200, $limit));
            return Database::fetchAll(
                "SELECT id, anonim_id, user_id, ip_adresi, user_agent, karar, analitik_izin, pazarlama_izin, tercih_izin, created_at
                 FROM cerez_izin_kayitlari
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
        } catch (Throwable $e) {
            error_log('CerezYonetimi::sonKayitlariGetir hatası: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Anonim ziyaretçi kimliği üretir/okur.
     */
    public static function anonimKimlikGetir(): string
    {
        $mevcut = $_COOKIE[self::ANONIM_CEREZ] ?? '';
        if (is_string($mevcut) && preg_match('/^[a-f0-9]{32}$/', $mevcut) === 1) {
            return $mevcut;
        }

        try {
            $anonimId = bin2hex(random_bytes(16));
        } catch (Throwable $e) {
            $anonimId = md5(uniqid((string) mt_rand(), true));
        }

        self::guvenliCerezYaz(self::ANONIM_CEREZ, $anonimId, time() + (86400 * 365));
        return $anonimId;
    }

    /**
     * IP maskeler. 192.168.1.20 -> 192.168.1.xxx
     */
    public static function ipMaskele(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parcalar = explode('.', $ip);
            $parcalar[3] = 'xxx';
            return implode('.', $parcalar);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parcalar = explode(':', $ip);
            $say = count($parcalar);
            if ($say >= 2) {
                $parcalar[$say - 1] = 'xxxx';
                $parcalar[$say - 2] = 'xxxx';
            }
            return implode(':', $parcalar);
        }

        return '0.0.0.xxx';
    }

    // ===================== BAŞLANGIÇ: İÇ YARDIMCILAR =====================
    private static function tercihCereziniYaz(array $tercih): void
    {
        $json = json_encode($tercih, JSON_UNESCAPED_UNICODE);
        if (!is_string($json)) {
            $json = json_encode(self::varsayilanTercih());
        }
        self::guvenliCerezYaz(self::TERCIH_CEREZI, (string) $json, time() + (86400 * 365));
    }

    private static function guvenliCerezYaz(string $ad, string $deger, int $bitis): void
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie($ad, $deger, [
            'expires' => $bitis,
            'path' => '/',
            'secure' => $https,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[$ad] = $deger;
    }

    private static function analitikPazarlamaCerezleriniSil(): void
    {
        $silinecekler = [
            '_ga',
            '_gid',
            '_gat',
            '_fbp',
            '_fbc',
            '_gcl_au',
            '_uetsid',
            '_uetvid',
        ];

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        foreach ($silinecekler as $ad) {
            setcookie($ad, '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => $https,
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
            unset($_COOKIE[$ad]);
        }
    }

    private static function tercihKaydiniYaz(array $tercih): void
    {
        try {
            $hamIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            $tamIpSakla = (bool) option_get('tam_ip_sakla', false, 'gizlilik');
            $saklanacakIp = $tamIpSakla ? $hamIp : self::ipMaskele($hamIp);

            Database::query(
                "INSERT INTO cerez_izin_kayitlari
                (anonim_id, user_id, ip_adresi, user_agent, karar, analitik_izin, pazarlama_izin, tercih_izin, kaynak)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    self::anonimKimlikGetir(),
                    $_SESSION['user_id'] ?? null,
                    $saklanacakIp,
                    substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                    $tercih['karar'] ?? 'tercih',
                    !empty($tercih['analitik']) ? 1 : 0,
                    !empty($tercih['pazarlama']) ? 1 : 0,
                    !empty($tercih['tercih']) ? 1 : 0,
                    'banner'
                ]
            );
        } catch (Throwable $e) {
            error_log('CerezYonetimi::tercihKaydiniYaz hatası: ' . $e->getMessage());
        }
    }

    private static function eskiKayitlariTemizle(): void
    {
        if (mt_rand(1, 100) !== 7) {
            return;
        }

        try {
            $gun = (int) option_get('kayit_saklama_gunu', 180, 'gizlilik');
            if ($gun < 1) {
                $gun = 180;
            }

            Database::query(
                "DELETE FROM cerez_izin_kayitlari
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$gun]
            );
        } catch (Throwable $e) {
            error_log('CerezYonetimi::eskiKayitlariTemizle hatası: ' . $e->getMessage());
        }
    }
    // ===================== BİTİŞ: İÇ YARDIMCILAR =====================
}
