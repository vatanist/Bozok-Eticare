/**
* PayTR Callback (Bildirim) URL
* PayTR sunucularından buraya POST isteği gelir.
*/
// Config zaten modul-isleyici.php tarafından yüklendi

// Post verileri
$post = $_POST;

if (empty($post)) {
die('PAYTR notification failed');
}

// Ayarlar
$merchant_key = getSetting('paytr_merchant_key');
$merchant_salt = getSetting('paytr_merchant_salt');

// Hash kontrolü
$hash = base64_encode(hash_hmac('sha256', $post['merchant_oid'] . $merchant_salt . $post['status'] .
$post['total_amount'], $merchant_key, true));

if ($hash != $post['hash']) {
die('PAYTR notification failed: bad hash');
}

// Sipariş No (merchant_oid: ORDERNO_TIMESTAMP formatında olabilir veya direkt ORDERNO)
// Biz payment.php'de ORDERNO_TIMESTAMP yapmıştık. Parçalayalım.
$oid_parts = explode('_', $post['merchant_oid']);
$order_number = $oid_parts[0];

// Siparişi bul (order_number üzerinden veya ID üzerinden gitmek daha iyiydi ama oid kullandık)
// order_number benzersiz olduğu için sorun yok.
$order = Database::fetch("SELECT * FROM orders WHERE order_number = ?", [$order_number]);

if (!$order) {
// Sipariş bulunamadı (kritik!)
// Loglanmalı
die('Order not found');
}

if ($post['status'] == 'success') {
// Ödeme Başarılı
// Sipariş durumu: pending (veya processing)
// Ödeme durumu: paid

// Zaten ödenmiş mi kontrol et (mükerrer bildirim)
if ($order['payment_status'] !== 'paid') {
Database::query(
"UPDATE orders SET status = 'pending', payment_status = 'paid', payment_transaction_id = ? WHERE id = ?",
[$post['merchant_oid'], $order['id']] // merchant_oid'yi trans_id olarak sakla
);

// Stok zaten checkout'ta düşülmüştü. Ek işlem gerekmez.

// Admin'e bildirim veya email gönderimi buraya eklenebilir.
}

} else {
// Ödeme Başarısız
// Sipariş durumu: cancelled (veya pending_payment olarak kalır, başarısız diye işaretlenir)
// Ödeme durumu: failed

Database::query(
"UPDATE orders SET payment_status = 'failed', notes = CONCAT(IFNULL(notes,''), ?) WHERE id = ?",
["\n[PayTR Hatası]: " . $post['failed_reason_code'] . " - " . $post['failed_reason_msg'], $order['id']]
);

// Stok iadesi yapılabilir mi?
// Eğer "başarısız" kesinleştiyse ve müşteri tekrar denemeyecekse stok iade edilmeli.
// Ancak müşteri 2 dk sonra farklı kartla deneyebilir.
// Şimdilik stok iadesi yapmıyoruz, "pending_payment" veya "cancelled" statüsünde manuel yönetim.
}

// PayTR'a bildirim alındı onayı
echo "OK";
exit;