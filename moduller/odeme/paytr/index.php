<?php
/**
 * PayTR Ödeme Sayfası (Giriş)
 * Bu dosya modul-isleyici.php üzerinden çağrılır.
 */

requireLogin();

$orderId = intval($_GET['id'] ?? 0);
if (!$orderId)
    git('/');

$user = aktif_kullanici();
$order = Database::fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $user['id']]);

if (!$order)
    git('/client/orders.php');

// Sınıfı Başlat ve Token Al (OOP Style & WooCommerce Standartı)
$gateway = new Paytr_Gateway();
$sonuc = $gateway->get_payment_token($order, $user);

if (!$sonuc['success']) {
    die("PAYTR Hata: " . $sonuc['error']);
}

$token = $sonuc['token'];

// Şablonu Modül İçinden Yükle (Isolation)
$veriler = [
    'sayfa_basligi' => 'Ödeme Yap - Sipariş #' . $order['order_number'],
    'order' => $order,
    'token' => $token
];

// MODÜL İÇİ GÖRÜNÜM SİSTEMİ (Templates Dir)
extract($veriler);
include __DIR__ . '/templates/iframe-container.php';

