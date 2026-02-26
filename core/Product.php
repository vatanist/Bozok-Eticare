<?php

/**
 * Product Master Engine
 * V-Commerce Enterprise Product Management
 */
class Product
{
    /**
     * Kategorileri hiyerarşik (Tree) olarak getirir.
     */
    public static function categories($parentId = 0)
    {
        return Database::fetchAll("SELECT * FROM categories WHERE parent_id = ? ORDER BY sort_order ASC", [$parentId]);
    }

    /**
     * Tüm kategorileri düz liste ama hiyerarşik sırada getirir.
     */
    public static function categoriesFlat($parentId = 0, $spacing = '')
    {
        $cats = self::categories($parentId);
        $result = [];
        foreach ($cats as $cat) {
            $cat['display_name'] = $spacing . $cat['name'];
            $result[] = $cat;
            $children = self::categoriesFlat($cat['id'], $spacing . '— ');
            $result = array_merge($result, $children);
        }
        return $result;
    }

    /**
     * Kategori yolunu (Materialized Path) günceller.
     */
    public static function syncCategoryPaths($parentId = 0, $parentPath = '', $level = 0)
    {
        $cats = self::categories($parentId);
        foreach ($cats as $cat) {
            $currentPath = $parentPath ? $parentPath . '/' . $cat['id'] : (string) $cat['id'];
            Database::query("UPDATE categories SET path = ?, level = ? WHERE id = ?", [$currentPath, $level, $cat['id']]);
            self::syncCategoryPaths($cat['id'], $currentPath, $level + 1);
        }
    }

    /**
     * Ürün varyasyonlarını getirir.
     */
    public static function getVariations($productId)
    {
        return Database::fetchAll("SELECT * FROM product_variations WHERE product_id = ?", [$productId]);
    }

    /**
     * Stok hareketi kaydeder.
     */
    public static function logInventory($productId, $variationId, $amount, $reason = 'Manuel Güncelleme')
    {
        Database::query(
            "INSERT INTO inventory_log (product_id, variation_id, change_amount, reason) VALUES (?, ?, ?, ?)",
            [$productId, $variationId, $amount, $reason]
        );

        // Ana tabloyu güncelle (Varyasyon varsa varyasyonu, yoksa ürünü)
        if ($variationId) {
            Database::query("UPDATE product_variations SET stock = stock + ? WHERE id = ?", [$amount, $variationId]);
        } else {
            Database::query("UPDATE products SET stock = stock + ? WHERE id = ?", [$amount, $productId]);
        }

        return true;
    }

    /**
     * Ürünü tüm detaylarıyla getirir.
     */
    public static function get($id)
    {
        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product)
            return null;

        $product['variations'] = self::getVariations($id);
        $product['has_variations'] = count($product['variations']) > 0;

        return $product;
    }
}
