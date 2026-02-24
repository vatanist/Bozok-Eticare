<?php
/**
 * Bozkurt Core - Müşteri Paneli (Dashboard)
 */
require_once __DIR__ . '/../config/config.php';

// Giriş zorunlu
requireLogin();

$user = aktif_kullanici();
$userId = $_SESSION['user_id'];

// İstatistikleri Çek
$siparis_sayisi = Database::fetch("SELECT COUNT(id) as c FROM orders WHERE user_id = ?", [$userId])['c'] ?? 0;
$favori_sayisi = Database::fetch("SELECT COUNT(id) as c FROM wishlist WHERE user_id = ?", [$userId])['c'] ?? 0;
$adres_sayisi = Database::fetch("SELECT COUNT(id) as c FROM addresses WHERE user_id = ?", [$userId])['c'] ?? 0;

// Son Siparişler
$son_siparisler = Database::fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$userId]);

// Görünüme gönder
$veriler = [
    'sayfa_basligi' => 'Hesabım',
    'kullanici' => $user,
    'siparis_sayisi' => $siparis_sayisi,
    'favori_sayisi' => $favori_sayisi,
    'adres_sayisi' => $adres_sayisi,
    'son_siparisler' => $son_siparisler
];

gorunum('hesap-dashboard', $veriler);
