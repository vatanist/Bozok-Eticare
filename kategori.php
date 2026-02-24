<?php
/**
 * Bozkurt Core - Kategori Yöneticisi
 */
require_once 'config/config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    git('/index.php');
}

// Kategori bilgisini çek
$kategori = Database::fetch("SELECT * FROM categories WHERE slug = ? AND status = 1", [$slug]);
if (!$kategori) {
    git('/index.php');
}

// Alt kategorileri getir
$alt_kategoriler = Database::fetchAll("
    SELECT c.*, (SELECT COUNT(id) FROM products WHERE category_id = c.id) as product_count 
    FROM categories c 
    WHERE c.parent_id = ? AND c.status = 1
", [$kategori['id']]);

// Filtreler
$min_fiyat = isset($_GET['min']) ? (float) $_GET['min'] : 0;
$max_fiyat = isset($_GET['max']) ? (float) $_GET['max'] : 0;

// Sorguyu oluştur
$sql = "SELECT p.*, c.name as kategori_adi FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE (p.category_id = ? OR c.parent_id = ?) AND p.status = 1";
$params = [$kategori['id'], $kategori['id']];

if ($min_fiyat > 0) {
    $sql .= " AND (p.price >= ? OR p.discount_price >= ?)";
    $params[] = $min_fiyat;
    $params[] = $min_fiyat;
}

if ($max_fiyat > 0) {
    $sql .= " AND (p.price <= ? OR p.discount_price <= ?)";
    $params[] = $max_fiyat;
    $params[] = $max_fiyat;
}

$sql .= " ORDER BY p.created_at DESC";

// Ürünleri çek
$urunler = Database::fetchAll($sql, $params);

// Verileri şablona gönder
$veriler = [
    'sayfa_basligi' => $kategori['name'],
    'kategori' => $kategori,
    'alt_kategoriler' => $alt_kategoriler,
    'urunler' => $urunler
];

// Görünümü yükle
gorunum('kategori', $veriler);
