<?php
// ===================== BAŞLANGIÇ: PAYTR CALLBACK =====================
/**
 * PayTR Callback (Bildirim) URL
 * PayTR sunucularından buraya POST isteği gelir.
 */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit;
}

$post = $_POST;
$zorunlu_alanlar = ['merchant_oid', 'status', 'total_amount', 'hash'];
foreach ($zorunlu_alanlar as $alan) {
    if (!isset($post[$alan]) || $post[$alan] === '') {
        error_log('PayTR callback eksik alan: ' . $alan);
        die('PAYTR notification failed');
    }
}

$merchant_key = getSetting('paytr_merchant_key');
$merchant_salt = getSetting('paytr_merchant_salt');

$beklenen_hash = base64_encode(hash_hmac(
    'sha256',
    $post['merchant_oid'] . $merchant_salt . $post['status'] . $post['total_amount'],
    $merchant_key,
    true
));

if (!hash_equals($beklenen_hash, (string) $post['hash'])) {
    error_log('PayTR callback hash doğrulaması başarısız');
    die('PAYTR notification failed: bad hash');
}

$oid_parts = explode('_', (string) $post['merchant_oid']);
$order_number = $oid_parts[0] ?? '';
$order = Database::fetch("SELECT * FROM orders WHERE order_number = ?", [$order_number]);

if (!$order) {
    error_log('PayTR callback sipariş bulunamadı: ' . $order_number);
    die('Order not found');
}

if ($post['status'] === 'success') {
    if (($order['payment_status'] ?? '') !== 'paid') {
        Database::query(
            "UPDATE orders SET status = 'pending', payment_status = 'paid', payment_transaction_id = ? WHERE id = ?",
            [$post['merchant_oid'], $order['id']]
        );
    }
} else {
    $hata_kodu = $post['failed_reason_code'] ?? 'BILINMIYOR';
    $hata_mesaji = $post['failed_reason_msg'] ?? 'Bilinmeyen ödeme hatası';

    Database::query(
        "UPDATE orders SET payment_status = 'failed', notes = CONCAT(IFNULL(notes,''), ?) WHERE id = ?",
        ["\n[PayTR Hatası]: " . $hata_kodu . " - " . $hata_mesaji, $order['id']]
    );
}

echo 'OK';
exit;
// ===================== BİTİŞ: PAYTR CALLBACK =====================
