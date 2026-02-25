<?php
/**
 * Bozkurt Core - Müşteri Paneli (Sipariş Detayı)
 */
require_once __DIR__ . '/../config/config.php';

// Giriş zorunlu
requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    git('/client/orders.php');
}

$user = aktif_kullanici();
$userId = $_SESSION['user_id'];

// Siparişi Çek (Güvenlik: Sadece kendi siparişi)
$siparis = Database::fetch(
    "SELECT * FROM orders WHERE id = ? AND user_id = ?",
    [$id, $userId]
);

if (!$siparis) {
    mesaj('orders', 'Aradığınız sipariş bulunamadı veya bu siparişe erişim yetkiniz yok.', 'error');
    git('/client/orders.php');
}

// Sipariş Ürünlerini Çek
$urunler = Database::fetchAll(
    "SELECT oi.*, p.slug as product_slug 
     FROM order_items oi 
     LEFT JOIN products p ON p.id = oi.product_id 
     WHERE oi.order_id = ?",
    [$id]
);

// Banka Bilgileri (Eğer havale ise)
$banka = null;
if ($siparis['payment_method'] === 'havale') {
    $banka = [
        'bank_name' => ayar_getir('banka_adi', 'Bozok E-Ticaret Banka'),
        'bank_iban' => ayar_getir('banka_iban', 'TR00 0000 0000 0000 0000 0000 00'),
        'bank_account_holder' => ayar_getir('banka_alici', 'Bozok E-Ticaret LTD.')
    ];
}

// Görünüme gönder
$veriler = [
    'sayfa_basligi' => 'Sipariş Detayı #' . $siparis['order_number'],
    'kullanici' => $user,
    'siparis' => $siparis,
    'urunler' => $urunler,
    'banka' => $banka
];

gorunum('hesap-siparis-detay', $veriler);
