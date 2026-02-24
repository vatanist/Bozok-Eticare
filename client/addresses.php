<?php
/**
 * Bozkurt Core - Müşteri Paneli (Adreslerim)
 */
require_once __DIR__ . '/../config/config.php';

// Giriş zorunlu
requireLogin();

$user = aktif_kullanici();
$userId = $_SESSION['user_id'];

// İşlemler (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $data = [
            temiz($_POST['title']),
            temiz($_POST['first_name']),
            temiz($_POST['last_name']),
            temiz($_POST['phone']),
            temiz($_POST['address_line']),
            temiz($_POST['city']),
            temiz($_POST['district']),
            temiz($_POST['neighborhood'] ?? ''),
            temiz($_POST['zip_code'] ?? '')
        ];

        if ($action === 'add') {
            Database::query(
                "INSERT INTO addresses (user_id, title, first_name, last_name, phone, address_line, city, district, neighborhood, zip_code) VALUES (?,?,?,?,?,?,?,?,?,?)",
                array_merge([$userId], $data)
            );
            mesaj('address', 'Yeni adres başarıyla eklendi.', 'success');
        } else {
            $addressId = intval($_POST['address_id']);
            Database::query(
                "UPDATE addresses SET title=?, first_name=?, last_name=?, phone=?, address_line=?, city=?, district=?, neighborhood=?, zip_code=? WHERE id=? AND user_id=?",
                array_merge($data, [$addressId, $userId])
            );
            mesaj('address', 'Adres bilgileriniz güncellendi.', 'success');
        }
        git('/client/addresses.php');
    }

    if ($action === 'delete') {
        $addressId = intval($_POST['address_id']);
        Database::query("DELETE FROM addresses WHERE id=? AND user_id=?", [$addressId, $userId]);
        mesaj('address', 'Adres başarıyla silindi.', 'success');
        git('/client/addresses.php');
    }
}

// Adresleri Çek
$adresler = Database::fetchAll("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC", [$userId]);

// Görünüme gönder
$veriler = [
    'sayfa_basligi' => 'Adreslerim',
    'kullanici' => $user,
    'adresler' => $adresler
];

gorunum('hesap-adresler', $veriler);
