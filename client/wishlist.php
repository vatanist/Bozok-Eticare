<?php
/**
 * Bozkurt Core - Müşteri Paneli (Favorilerim)
 */
require_once __DIR__ . '/../config/config.php';

// Giriş zorunlu
requireLogin();

$user = aktif_kullanici();
$userId = $_SESSION['user_id'];

// Favoriden Çıkar (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $removeId = intval($_POST['remove_id']);
    Database::query("DELETE FROM wishlist WHERE id=? AND user_id=?", [$removeId, $userId]);
    mesaj('wishlist', 'Ürün favori listenizden çıkarıldı.', 'success');
    git('/client/wishlist.php');
}

// Favorileri Çek
$wishlist = Database::fetchAll(
    "SELECT w.*, p.name, p.slug, p.price, p.discount_price, p.image, p.stock 
     FROM wishlist w
     JOIN products p ON w.product_id = p.id 
     WHERE w.user_id = ? 
     ORDER BY w.created_at DESC",
    [$userId]
);

// Görünüme gönder
$veriler = [
    'sayfa_basligi' => 'Favorilerim',
    'kullanici' => $user,
    'wishlist' => $wishlist
];

gorunum('hesap-favoriler', $veriler);
