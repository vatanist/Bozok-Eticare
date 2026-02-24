<?php

/**
 * Notification & Push Engine
 * V-Commerce Enterprise Omnichannel Messaging
 */
class Notification
{
    /**
     * Cihaz token'ını kaydeder.
     */
    public static function registerDevice($userId, $token, $platform = 'android')
    {
        $exists = Database::fetch("SELECT id FROM device_tokens WHERE token = ?", [$token]);
        if ($exists) {
            Database::query("UPDATE device_tokens SET user_id = ?, platform = ?, last_used = NOW() WHERE id = ?", [$userId, $platform, $exists['id']]);
        } else {
            Database::query("INSERT INTO device_tokens (user_id, token, platform, last_used) VALUES (?, ?, ?, NOW())", [$userId, $token, $platform]);
        }
        return true;
    }

    /**
     * Push bildirimi gönderir (Mock).
     */
    public static function sendPush($userId, $title, $message, $data = [])
    {
        $tokens = Database::fetchAll("SELECT token FROM device_tokens WHERE user_id = ?", [$userId]);
        if (empty($tokens))
            return false;

        foreach ($tokens as $t) {
            // Firebase API call would go here
            // error_log("Push sent to token: " . $t['token']);
        }

        return true;
    }

    /**
     * Sipariş durumu değiştiğinde bildirim gönderir.
     */
    public static function notifyOrderStatus($orderId, $status)
    {
        $order = Database::fetch("SELECT user_id, order_number FROM orders WHERE id = ?", [$orderId]);
        if (!$order)
            return false;

        $title = "Sipariş Güncellemesi";
        $message = "{$order['order_number']} nolu siparişinizin durumu '{$status}' olarak güncellendi.";

        return self::sendPush($order['user_id'], $title, $message);
    }
}
