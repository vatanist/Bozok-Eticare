<?php

/**
 * Order Master Engine
 * V-Commerce Enterprise Order Management
 */
class Order
{
    /**
     * Sipariş detayını getirir.
     */
    public static function get($id)
    {
        $order = Database::fetch("SELECT * FROM orders WHERE id = ?", [$id]);
        if (!$order)
            return null;

        $order['items'] = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$id]);
        $order['history'] = self::getHistory($id);

        return $order;
    }

    /**
     * Sipariş statüsünü günceller ve geçmişe kaydeder.
     */
    public static function updateStatus($orderId, $status, $note = '', $notifyUser = false)
    {
        // Önce mevcut siparişi kontrol et
        $order = Database::fetch("SELECT id, status, tracking_number FROM orders WHERE id = ?", [$orderId]);
        if (!$order)
            return false;

        // Sipariş tablosunu güncelle
        Database::query("UPDATE orders SET status = ? WHERE id = ?", [$status, $orderId]);

        // Geçmişe ekle
        Database::query(
            "INSERT INTO order_history (order_id, status, note, notify_user) VALUES (?, ?, ?, ?)",
            [$orderId, $status, $note, $notifyUser ? 1 : 0]
        );

        // Hook: Kargoya verildiğinde tarih güncelle
        if ($status === 'shipped') {
            Database::query("UPDATE orders SET shipped_at = NOW() WHERE id = ?", [$orderId]);
        }

        if ($notifyUser) {
            // Müşteriye bildirim gönder (Email/SMS/Push)
            Notification::notifyOrderStatus($orderId, $status);
        }

        return true;
    }

    /**
     * Kargo bilgilerini günceller.
     */
    public static function updateShipping($orderId, $carrier, $trackingNumber)
    {
        return Database::query(
            "UPDATE orders SET shipping_carrier = ?, tracking_number = ? WHERE id = ?",
            [$carrier, $trackingNumber, $orderId]
        );
    }

    /**
     * Sipariş geçmişini getirir.
     */
    public static function getHistory($orderId)
    {
        return Database::fetchAll("SELECT * FROM order_history WHERE order_id = ? ORDER BY created_at DESC", [$orderId]);
    }

    /**
     * Statü etiketini (HTML) döner.
     */
    public static function getStatusBadge($status)
    {
        $badges = [
            'pending' => '<span class="admin-badge admin-badge-gray">Bekliyor</span>',
            'pending_payment' => '<span class="admin-badge admin-badge-orange">Ödeme Bekliyor</span>',
            'processing' => '<span class="admin-badge admin-badge-blue">Hazırlanıyor</span>',
            'shipped' => '<span class="admin-badge admin-badge-purple">Kargoya Verildi</span>',
            'delivered' => '<span class="admin-badge admin-badge-green">Teslim Edildi</span>',
            'cancelled' => '<span class="admin-badge admin-badge-red">İptal Edildi</span>',
            'refunded' => '<span class="admin-badge admin-badge-red">İade Edildi</span>'
        ];
        return $badges[$status] ?? '<span class="admin-badge">' . $status . '</span>';
    }
}
