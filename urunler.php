<?php
/**
 * Bozkurt Core - Tüm Ürünler Yöneticisi
 */
require_once 'config/config.php';

$sayfa = intval($_GET['sayfa'] ?? 1);
$limit = 12;
$offset = ($sayfa - 1) * $limit;

$kategori_id = intval($_GET['kategori'] ?? 0);
$siralama = $_GET['sirala'] ?? 'yeni';

// Sorgu Başlangıcı
$sql = "SELECT p.*, c.name as kategori_adi FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1";
$params = [];

// Kategori Filtresi
if ($kategori_id > 0) {
    $sql .= " AND (p.category_id = ? OR c.parent_id = ?)";
    $params[] = $kategori_id;
    $params[] = $kategori_id;
}

// Sıralama
switch ($siralama) {
    case 'fiyat_artan':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'fiyat_azalan':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'isim_az':
        $sql .= " ORDER BY p.name ASC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

// Toplam Sayı
$toplam_urun = Database::fetch(str_replace("p.*, c.name as kategori_adi", "COUNT(p.id) as count", $sql), $params)['count'];
$toplam_sayfa = ceil($toplam_urun / $limit);

// Limit Ekle
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$urunler = Database::fetchAll($sql, $params);
$kategoriler = kategorileri_getir();

$veriler = [
    'sayfa_basligi' => 'Tüm Ürünler',
    'urunler' => $urunler,
    'kategoriler' => $kategoriler,
    'mevcut_kategori' => $kategori_id,
    'mevcut_siralama' => $siralama,
    'sayfalama' => [
        'mevcut' => $sayfa,
        'toplam' => $toplam_sayfa,
        'urun_sayisi' => $toplam_urun
    ]
];

gorunum('kategori', $veriler);
