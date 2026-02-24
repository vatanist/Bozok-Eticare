<?php
/**
 * Havale Modülü - Ödeme Kontrolcüsü
 */

requireLogin();

// Modüle özel sınıfları yükle
require_once __DIR__ . '/includes/Banka_Manager.php';

$orderId = intval($_GET['id'] ?? 0);
$banka_manager = new Banka_Manager();
$bankalar = $banka_manager->get_banka_listesi();

// Şablonu render et
$veriler = [
    'sayfa_basligi' => 'Havale İle Ödeme Bilgileri',
    'bankalar' => $bankalar
];

extract($veriler);
include __DIR__ . '/templates/havale-detay.php';
