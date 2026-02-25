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
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $url = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $sid = session_id();

        // ===================== BAŞLANGIÇ: KVKK UYUMLU IP SAKLAMA =====================
        $tamIpSakla = false;
        if (function_exists('option_get')) {
            $tamIpSakla = (bool) option_get('tam_ip_sakla', false, 'gizlilik');
        }

        if (!$tamIpSakla && class_exists('CerezYonetimi')) {
            $ip = CerezYonetimi::ipMaskele($ip);
        }
        // ===================== BİTİŞ: KVKK UYUMLU IP SAKLAMA =====================

        try {
            Database::query(
                "INSERT INTO visitor_logs (ip, user_agent, page_url, referrer, session_id) VALUES (?, ?, ?, ?, ?)",
                [$ip, substr($ua, 0, 255), $url, $ref, $sid]
            );
        } catch (Throwable $e) {
            error_log('Marketing::logVisitor hatası: ' . $e->getMessage());
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
}
