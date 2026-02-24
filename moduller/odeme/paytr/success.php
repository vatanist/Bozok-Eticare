<?php
// ===================== BAŞLANGIÇ: PAYTR BAŞARILI SAYFA =====================
requireLogin();

$orderId = intval($_GET['id'] ?? 0);
$veriler = [
    'sayfa_basligi' => 'Ödeme Başarılı',
    'basarili' => true,
    'order_id' => $orderId
];

gorunum('odeme-sonuc', $veriler);
// ===================== BİTİŞ: PAYTR BAŞARILI SAYFA =====================
