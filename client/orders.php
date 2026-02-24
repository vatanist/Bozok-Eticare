<?php
/**
 * Bozkurt Core - Müşteri Paneli (Siparişlerim)
 */
require_once __DIR__ . '/../config/config.php';

// Giriş zorunlu
requireLogin();

$user = aktif_kullanici();
$userId = $_SESSION['user_id'];

// Siparişleri Çek
$siparisler = Database::fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

// Görünüme gönder
$veriler = [
    'sayfa_basligi' => 'Siparişlerim',
    'kullanici' => $user,
    'siparisler' => $siparisler
];

gorunum('hesap-siparisler', $veriler);
