<?php
/**
 * Bozkurt Core - Müşteri Paneli (Profil Ayarları)
 */
require_once __DIR__ . '/../config/config.php';

// Giriş zorunlu
requireLogin();

$user = aktif_kullanici();
$userId = $_SESSION['user_id'];

// İşlemler (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'profile';

    if ($action === 'profile') {
        $ad = temiz($_POST['first_name']);
        $soyad = temiz($_POST['last_name']);
        $email = temiz($_POST['email']);
        $telefon = temiz($_POST['phone']);

        Database::query(
            "UPDATE users SET first_name=?, last_name=?, phone=?, email=? WHERE id=?",
            [$ad, $soyad, $telefon, $email, $userId]
        );
        mesaj('profile', 'Profil bilgileriniz başarıyla güncellendi.', 'success');
        git('/client/profile.php');
    }

    if ($action === 'password') {
        $mevcut = $_POST['current_password'];
        $yeni = $_POST['new_password'];
        $tekar = $_POST['new_password_confirm'];

        if (!password_verify($mevcut, $user['password'])) {
            mesaj('profile', 'Mevcut şifreniz hatalı.', 'error');
        } elseif ($yeni !== $tekar) {
            mesaj('profile', 'Yeni şifreler birbiriyle eşleşmiyor.', 'error');
        } elseif (strlen($yeni) < 6) {
            mesaj('profile', 'Yeni şifreniz en az 6 karakter olmalıdır.', 'error');
        } else {
            Database::query("UPDATE users SET password=? WHERE id=?", [password_hash($yeni, PASSWORD_DEFAULT), $userId]);
            mesaj('profile', 'Şifreniz başarıyla değiştirildi.', 'success');
        }
        git('/client/profile.php');
    }
}

// Görünüme gönder
$veriler = [
    'sayfa_basligi' => 'Profil Ayarları',
    'kullanici' => $user
];

gorunum('hesap-profil', $veriler);
