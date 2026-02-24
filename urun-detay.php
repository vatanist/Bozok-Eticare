<?php
/**
 * Bozkurt Core - Ürün Detay Yöneticisi
 */
require_once 'config/config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    git('/index.php');
}

// Ürün bilgisini çek
$urun = urun_getir_slug($slug);
if (!$urun) {
    git('/index.php');
}

// Görüntülenme sayısını artır
Database::query("UPDATE products SET view_count = view_count + 1 WHERE id = ?", [$urun['id']]);

// Benzer ürünleri getir
$benzer_urunler = Database::fetchAll(
    "SELECT p.*, c.name as kategori_adi FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.category_id = ? AND p.id != ? AND p.status = 1 
     ORDER BY RAND() LIMIT 4",
    [$urun['category_id'], $urun['id']]
);

// Varyasyonları çek (Seçenekler)
$secenekler = [];
$satirlar = Database::fetchAll("
    SELECT po.*, o.name as secenek_adi, ov.name as deger_adi, o.type as secenek_tipi
    FROM product_options po 
    JOIN options o ON po.option_id = o.id 
    JOIN option_values ov ON po.option_value_id = ov.id
    WHERE po.product_id = ?
    ORDER BY o.sort_order, o.id, ov.sort_order
", [$urun['id']]);

foreach ($satirlar as $s) {
    if (!isset($secenekler[$s['option_id']])) {
        $secenekler[$s['option_id']] = [
            'ad' => $s['secenek_adi'],
            'tip' => $s['secenek_tipi'],
            'degerler' => []
        ];
    }
    $secenekler[$s['option_id']]['degerler'][] = $s;
}

// Verileri şablona gönder
$veriler = [
    'sayfa_basligi' => $urun['name'],
    'urun' => $urun,
    'benzerler' => $benzer_urunler,
    'secenekler' => $secenekler,
    'fiyat' => ($urun['discount_price'] ?: $urun['price'])
];

// Görünümü yükle
gorunum('urun-detay', $veriler);
