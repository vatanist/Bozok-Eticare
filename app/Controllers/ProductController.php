<?php
/**
 * V-Commerce — ProductController
 *
 * Ürün detay, kategori listeleme, arama.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

class ProductController extends BaseController
{
    /**
     * Ürün detay sayfası
     *
     * @param string $slug  Ürün slug'ı
     */
    public function show(string $slug): void
    {
        $urun = urun_getir_slug($slug);
        if (!$urun) {
            $this->redirect('/');
            return;
        }

        // Görüntülenme artır
        \Database::query("UPDATE products SET view_count = view_count + 1 WHERE id = ?", [$urun['id']]);

        // Benzer ürünler
        $benzer_urunler = \Database::fetchAll(
            "SELECT p.*, c.name as kategori_adi FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.category_id = ? AND p.id != ? AND p.status = 1 
             ORDER BY RAND() LIMIT 4",
            [$urun['category_id'], $urun['id']]
        );

        // Varyasyonlar (seçenekler)
        $secenekler = [];
        $satirlar = \Database::fetchAll("
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

        $this->view('urun-detay', [
            'sayfa_basligi' => $urun['name'],
            'urun' => $urun,
            'benzerler' => $benzer_urunler,
            'secenekler' => $secenekler,
            'fiyat' => ($urun['discount_price'] ?: $urun['price'])
        ]);
    }

    /**
     * Kategori sayfası — ürün listeleme + filtreleme
     *
     * @param string $slug  Kategori slug'ı
     */
    public function category(string $slug): void
    {
        $kategori = \Database::fetch(
            "SELECT * FROM categories WHERE slug = ? AND status = 1",
            [$slug]
        );
        if (!$kategori) {
            $this->redirect('/');
            return;
        }

        // Alt kategoriler
        $alt_kategoriler = \Database::fetchAll("
            SELECT c.*, (SELECT COUNT(id) FROM products WHERE category_id = c.id) as product_count 
            FROM categories c 
            WHERE c.parent_id = ? AND c.status = 1
        ", [$kategori['id']]);

        // Filtreler
        $min_fiyat = isset($_GET['min']) ? (float) $_GET['min'] : 0;
        $max_fiyat = isset($_GET['max']) ? (float) $_GET['max'] : 0;

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

        $this->view('kategori', [
            'sayfa_basligi' => $kategori['name'],
            'kategori' => $kategori,
            'alt_kategoriler' => $alt_kategoriler,
            'urunler' => \Database::fetchAll($sql, $params)
        ]);
    }

    /**
     * Arama sonuçları
     */
    public function search(): void
    {
        $q = trim($this->input('q', ''));
        $urunler = [];

        if ($q) {
            $searchTerm = '%' . $q . '%';
            $urunler = \Database::fetchAll(
                "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.status = 1 AND (p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ? OR p.sku LIKE ?)
                 ORDER BY p.name ASC LIMIT 50",
                [$searchTerm, $searchTerm, $searchTerm, $searchTerm]
            );
        }

        $this->view('arama', [
            'sayfa_basligi' => ($q ? 'Arama: ' . $q : 'Arama'),
            'sorgu' => $q,
            'urunler' => $urunler
        ]);
    }
}
