<?php
/**
 * V-Commerce — Global Seçenekler (Options) Migration
 * OpenCart tarzı varyasyon sistemi için gerekli tablolar.
 */
require_once __DIR__ . '/../../config/config.php';
if (!isAdmin()) {
    http_response_code(403);
    die('Yetkisiz.');
}

try {
    // 1. Seçenek Grupları (Örn: Renk, Beden, Malzeme)
    Database::query("CREATE TABLE IF NOT EXISTS `options` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(128) NOT NULL,
        `type` VARCHAR(32) NOT NULL DEFAULT 'select',
        `sort_order` INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 2. Seçenek Değerleri (Örn: Kırmızı, Mavi, 42, 44)
    Database::query("CREATE TABLE IF NOT EXISTS `option_values` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `option_id` INT NOT NULL,
        `name` VARCHAR(128) NOT NULL,
        `sort_order` INT DEFAULT 0,
        FOREIGN KEY (`option_id`) REFERENCES `options`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. Ürün — Seçenek İlişkisi (Stok, Fiyat vb. ile)
    Database::query("CREATE TABLE IF NOT EXISTS `product_options` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `option_id` INT NOT NULL,
        `option_value_id` INT NOT NULL,
        `quantity` INT DEFAULT 0,
        `subtract` TINYINT(1) DEFAULT 1,
        `price` DECIMAL(15,4) DEFAULT 0,
        `price_prefix` CHAR(1) DEFAULT '+',
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`option_id`) REFERENCES `options`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`option_value_id`) REFERENCES `option_values`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "✅ Global Varyasyon (Options) tabloları kuruldu.";
} catch (Exception $e) {
    echo "❌ HATA: " . $e->getMessage();
}
