<?php

/**
 * Affiliate Master Engine
 * Bozok E-Ticaret Enterprise Growth & Referral Management
 */
class Affiliate
{
    /**
     * Referans kodunu session'a kaydeder (Çerez bazlı izleme).
     */
    public static function trackReferral()
    {
        if (isset($_GET['ref'])) {
            $refCode = trim($_GET['ref']);
            // Affiliate var mı kontrol et
            $affiliate = Database::fetch("SELECT id FROM affiliates WHERE ref_code = ? AND status = 'active'", [$refCode]);
            if ($affiliate) {
                $_SESSION['affiliate_id'] = $affiliate['id'];
                // 30 günlük çerez (opsiyonel)
                setcookie('affiliate_ref', $refCode, time() + (86400 * 30), '/');
            }
        }
    }

    /**
     * Sipariş üzerine komisyon hesaplar ve kaydeder.
     */
    public static function processCommission($orderId, $totalAmount)
    {
        $affiliateId = $_SESSION['affiliate_id'] ?? null;
        if (!$affiliateId && isset($_COOKIE['affiliate_ref'])) {
            $affiliate = Database::fetch("SELECT id FROM affiliates WHERE ref_code = ? AND status = 'active'", [$_COOKIE['affiliate_ref']]);
            $affiliateId = $affiliate['id'] ?? null;
        }

        if ($affiliateId) {
            $aff = Database::fetch("SELECT commission_rate FROM affiliates WHERE id = ?", [$affiliateId]);
            if ($aff) {
                $commission = $totalAmount * ($aff['commission_rate'] / 100);
                Database::query(
                    "INSERT INTO affiliate_referrals (affiliate_id, order_id, commission_amount, status) VALUES (?, ?, ?, 'pending')",
                    [$affiliateId, $orderId, $commission]
                );
            }
        }
    }

    /**
     * Affiliate kazancını onaylar ve cüzdana yansıtır.
     */
    public static function approveCommission($referralId)
    {
        $ref = Database::fetch("SELECT * FROM affiliate_referrals WHERE id = ? AND status = 'pending'", [$referralId]);
        if ($ref) {
            Database::query("UPDATE affiliate_referrals SET status = 'approved' WHERE id = ?", [$referralId]);
            Database::query("UPDATE affiliates SET balance = balance + ? WHERE id = ?", [$ref['commission_amount'], $ref['affiliate_id']]);
            return true;
        }
        return false;
    }
}
