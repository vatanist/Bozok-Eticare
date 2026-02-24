<?php

/**
 * Variation Engine
 * Handles complex product variations and matrix combinations.
 */
class Variation
{
    /**
     * Verilen özellik dizilerinden tüm olası kombinasyonları üretir.
     * Örn: [['Kırmızı', 'Mavi'], ['S', 'M']] => [['Kırmızı', 'S'], ['Kırmızı', 'M'], ...]
     */
    public static function generateCombinations($arrays)
    {
        $result = [[]];
        foreach ($arrays as $property => $values) {
            $tmp = [];
            foreach ($result as $resultItem) {
                foreach ($values as $value) {
                    $tmp[] = array_merge($resultItem, [$property => $value]);
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    /**
     * Ürünün aktif özelliklerini (Attributes) getirir.
     */
    public static function getProductAttributes($productId)
    {
        // Mevcut sisteme (options/product_options) uyumlu veya yeni sisteme göre
        return Database::fetchAll("
            SELECT a.name as attribute_name, av.value as value_name, av.id as value_id, a.id as attribute_id
            FROM product_options po
            JOIN options a ON po.option_id = a.id
            JOIN option_values av ON po.option_value_id = av.id
            WHERE po.product_id = ?
        ", [$productId]);
    }

    /**
     * Varyasyonları (Matrix) veritabanına kaydeder.
     */
    public static function saveVariations($productId, $variations)
    {
        // Önce eskileri temizle veya güncelle (Enterprise düzeyinde 'soft-delete' veya 'sku matching' önerilir)
        Database::query("DELETE FROM product_variations WHERE product_id = ?", [$productId]);

        foreach ($variations as $v) {
            Database::query(
                "INSERT INTO product_variations (product_id, sku, barcode, price_modifier, stock, specs) VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $productId,
                    $v['sku'] ?? null,
                    $v['barcode'] ?? null,
                    $v['price_modifier'] ?? 0,
                    $v['stock'] ?? 0,
                    json_encode($v['specs'] ?? [])
                ]
            );
        }
    }
}
