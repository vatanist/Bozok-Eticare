<?php
/**
 * Cart tablosuna dimensions (boyut bilgisi) sütunu ekler.
 * Bir kez çalıştırın, ardından silebilirsiniz.
 */
require_once __DIR__ . '/../config/config.php';

if (!isAdmin()) {
    http_response_code(403);
    exit('Erişim reddedildi.');
}

$sqls = [
    "ALTER TABLE cart ADD COLUMN dimensions JSON DEFAULT NULL COMMENT 'm² ürünler için boyut bilgisi {w,h,area_m2,price_per_m2}' AFTER quantity",
    "ALTER TABLE cart ADD COLUMN unit_price_override DECIMAL(10,2) DEFAULT NULL COMMENT 'Boyut hesaplamasından gelen birim fiyat' AFTER dimensions",
];

echo '<pre>';
foreach ($sqls as $sql) {
    try {
        Database::query($sql);
        echo "✅ OK : $sql\n";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'Duplicate column')) {
            echo "⚠️ Zaten mevcut: $sql\n";
        } else {
            echo "❌ HATA: $msg\n  SQL: $sql\n";
        }
    }
}
echo "\n<strong>Tamamlandı. Bu dosyayı silebilirsiniz.</strong>";
echo '</pre>';
