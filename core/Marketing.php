<?php

/**
 * Marketing & Analytics Engine
 * Bozok E-Ticaret Enterprise Growth & Insight Management
 */
class Marketing
{
    /**
     * Ziyaretçiyi loglar.
     */
    public static function logVisitor()
    {
        $meta = self::ziyaretciMetaBilgisiGetir();

        try {
            Database::query(
                "INSERT INTO visitor_logs (ip, user_agent, page_url, referrer, session_id, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $meta['ip'],
                    $meta['user_agent'],
                    $meta['page_url'],
                    $meta['referrer'],
                    $meta['session_id'],
                ]
            );
        } catch (Throwable $e) {
            error_log('Marketing::logVisitor visitor_logs hatası: ' . $e->getMessage());
        }

        self::olayKaydet('page_view', [
            'page_url' => $meta['page_url'],
            'referrer' => $meta['referrer'],
            'user_agent' => $meta['user_agent'],
            'ip' => $meta['ip'],
            'anonim_id' => $meta['anonim_id'],
            'session_id' => $meta['session_id'],
            'il' => $meta['il'],
            'ilce' => $meta['ilce'],
            'tarayici' => $meta['tarayici'],
            'cihaz_tipi' => $meta['cihaz_tipi'],
        ]);
    }

    /**
     * Analitik olayı kaydeder.
     */
    public static function olayKaydet(string $olayAdi, array $veri = []): bool
    {
        // ===================== BAŞLANGIÇ: ÇEREZ İZİN KONTROLÜ =====================
        if (!self::analitikYazimiIzinliMi()) {
            return false;
        }
        // ===================== BİTİŞ: ÇEREZ İZİN KONTROLÜ =====================

        $olayAdi = trim($olayAdi);
        if ($olayAdi === '' || preg_match('/^[a-z0-9_\-]+$/i', $olayAdi) !== 1) {
            return false;
        }

        $meta = self::ziyaretciMetaBilgisiGetir($veri);

        try {
            Database::query(
                "INSERT INTO analytics_events
                (event_name, page_url, referrer, product_id, user_id, anonim_id, session_id, ip, il, ilce, tarayici, cihaz_tipi, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $olayAdi,
                    $meta['page_url'],
                    $meta['referrer'],
                    $meta['product_id'],
                    $meta['user_id'],
                    $meta['anonim_id'],
                    $meta['session_id'],
                    $meta['ip'],
                    $meta['il'],
                    $meta['ilce'],
                    $meta['tarayici'],
                    $meta['cihaz_tipi'],
                    $meta['user_agent'],
                ]
            );
            return true;
        } catch (Throwable $e) {
            error_log('Marketing::olayKaydet hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Belirli bir pozisyondaki aktif bannerları getirir.
     */
    public static function getBanners($position = 'homepage_main')
    {
        return Database::fetchAll(
            "SELECT * FROM banners WHERE position = ? AND status = 1 ORDER BY id DESC",
            [$position]
        );
    }

    /**
     * Banner tıklamasını loglar.
     */
    public static function logBannerClick($bannerId)
    {
        Database::query("UPDATE banners SET click_count = click_count + 1 WHERE id = ?", [$bannerId]);
    }

    /**
     * Basit bir dashboard özeti döner.
     */
    public static function getGrowthStats()
    {
        $stats = [];
        $stats['total_visitors'] = Database::fetch("SELECT COUNT(*) as c FROM visitor_logs")['c'];
        $stats['total_affiliates'] = Database::fetch("SELECT COUNT(*) as c FROM affiliates")['c'];
        $stats['total_commissions'] = Database::fetch("SELECT SUM(commission_amount) as s FROM affiliate_referrals WHERE status = 'approved'")['s'] ?? 0;
        return $stats;
    }

    /**
     * Admin analitik paneli için özet veri döner.
     */
    public static function analitikPanelVerisiGetir(): array
    {
        $bos = [
            'toplam_olay' => 0,
            'tekil_anonim' => 0,
            'son12_goruntuleme' => 0,
            'son12_sepete_ekleme' => 0,
            'tarayicilar' => [],
            'iller' => [],
            'saatlik12' => [],
            'son_olaylar' => [],
        ];

        try {
            $ozet = Database::fetch(
                "SELECT
                    COUNT(*) AS toplam_olay,
                    COUNT(DISTINCT anonim_id) AS tekil_anonim,
                    SUM(CASE WHEN event_name = 'page_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 HOUR) THEN 1 ELSE 0 END) AS son12_goruntuleme,
                    SUM(CASE WHEN event_name = 'add_to_cart' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 HOUR) THEN 1 ELSE 0 END) AS son12_sepete_ekleme
                 FROM analytics_events"
            );

            $bos['toplam_olay'] = (int) ($ozet['toplam_olay'] ?? 0);
            $bos['tekil_anonim'] = (int) ($ozet['tekil_anonim'] ?? 0);
            $bos['son12_goruntuleme'] = (int) ($ozet['son12_goruntuleme'] ?? 0);
            $bos['son12_sepete_ekleme'] = (int) ($ozet['son12_sepete_ekleme'] ?? 0);

            $bos['tarayicilar'] = Database::fetchAll(
                "SELECT tarayici, COUNT(*) AS adet
                 FROM analytics_events
                 GROUP BY tarayici
                 ORDER BY adet DESC
                 LIMIT 8"
            );

            $bos['iller'] = Database::fetchAll(
                "SELECT il, COUNT(*) AS adet
                 FROM analytics_events
                 GROUP BY il
                 ORDER BY adet DESC
                 LIMIT 8"
            );

            $saatlik = Database::fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') AS saat,
                        SUM(CASE WHEN event_name = 'page_view' THEN 1 ELSE 0 END) AS goruntuleme,
                        SUM(CASE WHEN event_name = 'add_to_cart' THEN 1 ELSE 0 END) AS sepete_ekleme
                 FROM analytics_events
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 HOUR)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H')
                 ORDER BY saat ASC"
            );
            $bos['saatlik12'] = $saatlik;

            $bos['son_olaylar'] = Database::fetchAll(
                "SELECT event_name, page_url, product_id, anonim_id, ip, il, ilce, tarayici, created_at
                 FROM analytics_events
                 ORDER BY id DESC
                 LIMIT 60"
            );
        } catch (Throwable $e) {
            error_log('Marketing::analitikPanelVerisiGetir hatası: ' . $e->getMessage());
        }

        return $bos;
    }

    // ===================== BAŞLANGIÇ: ANALİTİK YARDIMCILAR =====================
    private static function analitikYazimiIzinliMi(): bool
    {
        if (class_exists('CerezYonetimi')) {
            return CerezYonetimi::analitikIzniVarMi();
        }
        return true;
    }

    private static function ziyaretciMetaBilgisiGetir(array $veri = []): array
    {
        $hamIp = (string) ($veri['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
        $ip = self::uygunIpDonustur($hamIp);
        $ua = (string) ($veri['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));

        $tarayici = self::tarayiciBul($ua);
        $cihaz = self::cihazTuruBul($ua);

        return [
            'ip' => $ip,
            'user_agent' => substr($ua, 0, 255),
            'page_url' => (string) ($veri['page_url'] ?? ($_SERVER['REQUEST_URI'] ?? '')),
            'referrer' => (string) ($veri['referrer'] ?? ($_SERVER['HTTP_REFERER'] ?? '')),
            'session_id' => (string) ($veri['session_id'] ?? session_id()),
            'anonim_id' => (string) ($veri['anonim_id'] ?? (class_exists('CerezYonetimi') ? CerezYonetimi::anonimKimlikGetir() : '')),
            'user_id' => (int) ($veri['user_id'] ?? ($_SESSION['user_id'] ?? 0)) ?: null,
            'product_id' => (int) ($veri['product_id'] ?? 0) ?: null,
            'il' => (string) ($veri['il'] ?? ($_SERVER['HTTP_X_CITY'] ?? $_SERVER['HTTP_CF_IPCITY'] ?? 'Bilinmiyor')),
            'ilce' => (string) ($veri['ilce'] ?? ($_SERVER['HTTP_X_DISTRICT'] ?? 'Bilinmiyor')),
            'tarayici' => $tarayici,
            'cihaz_tipi' => $cihaz,
        ];
    }

    private static function uygunIpDonustur(string $ip): string
    {
        $tamIpSakla = false;
        if (function_exists('option_get')) {
            $tamIpSakla = (bool) option_get('tam_ip_sakla', false, 'gizlilik');
        }

        if (!$tamIpSakla && class_exists('CerezYonetimi')) {
            return CerezYonetimi::ipMaskele($ip);
        }

        return $ip;
    }

    private static function tarayiciBul(string $ua): string
    {
        $ua = strtolower($ua);
        if (str_contains($ua, 'edg/')) {
            return 'Edge';
        }
        if (str_contains($ua, 'chrome/') && !str_contains($ua, 'edg/')) {
            return 'Chrome';
        }
        if (str_contains($ua, 'firefox/')) {
            return 'Firefox';
        }
        if (str_contains($ua, 'safari/') && !str_contains($ua, 'chrome/')) {
            return 'Safari';
        }
        if (str_contains($ua, 'opr/')) {
            return 'Opera';
        }
        return 'Diger';
    }

    private static function cihazTuruBul(string $ua): string
    {
        $ua = strtolower($ua);
        if (str_contains($ua, 'mobile')) {
            return 'Mobil';
        }
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'Tablet';
        }
        return 'Masaustu';
    }
    // ===================== BİTİŞ: ANALİTİK YARDIMCILAR =====================
}
