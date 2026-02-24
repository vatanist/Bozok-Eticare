<?php
/**
 * Bozkurt Core - Arama Sayfası
 */
require_once 'config/config.php';

$q = trim($_GET['q'] ?? '');

$urunler = [];
if ($q) {
    $searchTerm = '%' . $q . '%';
    $urunler = Database::fetchAll(
        "SELECT p.*, c.name as category_name 
         FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.status = 1 AND (p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ? OR p.sku LIKE ?)
         ORDER BY p.name ASC LIMIT 50",
        [$searchTerm, $searchTerm, $searchTerm, $searchTerm]
    );
}

// Görünüme gönder
$veriler = [
    'sayfa_basligi' => ($q ? 'Arama: ' . $q : 'Arama'),
    'sorgu' => $q,
    'urunler' => $urunler
];

gorunum('arama', $veriler);
