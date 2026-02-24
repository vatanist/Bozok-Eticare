<?php
/**
 * Bozkurt Core - Müşteri Paneli (Fiyat Alarmları)
 */
require_once __DIR__ . '/../config/config.php';

// Giriş zorunlu
requireLogin();

$user = aktif_kullanici();
$userId = $_SESSION['user_id'];

// Alarm Kaldır (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $removeId = intval($_POST['remove_id']);
    Database::query("DELETE FROM price_alerts WHERE id=? AND user_id=?", [$removeId, $userId]);
    mesaj('price_alert', 'Fiyat uyarısı listesinden çıkarıldı.', 'success');
    git('/client/price-alerts.php');
}

// Alarmları Çek
$alarmlar = [];
try {
    $alarmlar = Database::fetchAll(
        "SELECT pa.*, p.name, p.slug, p.price, p.discount_price, p.image, p.stock
         FROM price_alerts pa
         JOIN products p ON pa.product_id = p.id
         WHERE pa.user_id = ?
         ORDER BY pa.created_at DESC",
        [$userId]
    );
} catch (Exception $e) {
    // Tablo yoksa veya hata varsa boş kalabilir
}

// Görünüme gönder
$veriler = [
    'sayfa_basligi' => 'Fiyat Alarmları',
    'kullanici' => $user,
    'alarmlar' => $alarmlar
];

gorunum('hesap-fiyat-alarmlari', $veriler);
