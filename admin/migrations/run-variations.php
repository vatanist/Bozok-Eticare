<?php
/**
 * V-Commerce — Varyasyon Sistemi Migration
 * Yeni tablolar ekler, mevcut yapıya dokunmaz.
 * 
 * KULLANIM: /admin/migrations/run-variations.php
 * SONRA: Silin!
 */
require_once __DIR__ . '/../../config/config.php';
if (!isAdmin()) {
    http_response_code(403);
    die('Yetkisiz.');
}

$sqls = [];

// 1. Varyasyon tipleri (Baskı Tekniği, Malzeme, Boyut, vb.)
$sqls[] = "CREATE TABLE IF NOT EXISTS `product_variation_types` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`  INT UNSIGNED NOT NULL,
    `name`        VARCHAR(100) NOT NULL COMMENT 'Örn: Boyut, Malzeme, Baskı Tekniği',
    `display_name` VARCHAR(100) NOT NULL COMMENT 'Müşteriye gösterilen isim',
    `sort_order`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// 2. Varyasyon seçenekleri
$sqls[] = "CREATE TABLE IF NOT EXISTS `product_variation_options` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_id`      INT UNSIGNED NOT NULL,
    `value`        VARCHAR(200) NOT NULL COMMENT 'Örn: A4, Kuşe 170gr, Dijital Baskı',
    `price_modifier` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Fiyata eklenecek/çıkarılacak tutar',
    `modifier_type` ENUM('fixed','percent') NOT NULL DEFAULT 'fixed',
    `sku_suffix`   VARCHAR(50) DEFAULT NULL,
    `sort_order`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `is_default`   TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_type` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// 3. Miktara göre fiyatlandırma (Web-to-Print için kritik)
$sqls[] = "CREATE TABLE IF NOT EXISTS `product_quantity_pricing` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`   INT UNSIGNED NOT NULL,
    `min_qty`      INT UNSIGNED NOT NULL DEFAULT 1,
    `max_qty`      INT UNSIGNED DEFAULT NULL COMMENT 'NULL = limitsiz',
    `unit_price`   DECIMAL(10,2) NOT NULL,
    `label`        VARCHAR(100) DEFAULT NULL COMMENT 'Örn: 1-9 adet, 10-49 adet',
    `sort_order`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// 4. order_items — varyasyon ve baskı dosyası için new columns
$sqls[] = "ALTER TABLE `order_items`
    ADD COLUMN IF NOT EXISTS `variation_data` JSON DEFAULT NULL COMMENT 'Seçilen varyasyonlar JSON',
    ADD COLUMN IF NOT EXISTS `unit_price_override` DECIMAL(10,2) DEFAULT NULL COMMENT 'Miktara göre fiyat geçersiz kılarsa',
    ADD COLUMN IF NOT EXISTS `print_file_id` INT UNSIGNED DEFAULT NULL COMMENT 'print_files tablosu FK';";

// 5. Baskı dosyaları tablosu (Aşama 5 için hazırlık)
$sqls[] = "CREATE TABLE IF NOT EXISTS `print_files` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`     INT UNSIGNED DEFAULT NULL,
    `order_item_id` INT UNSIGNED DEFAULT NULL,
    `user_id`      INT UNSIGNED DEFAULT NULL,
    `filename`     VARCHAR(500) NOT NULL COMMENT 'Güvenli dizindeki dosya adı',
    `original_name` VARCHAR(500) NOT NULL,
    `file_size`    INT UNSIGNED NOT NULL DEFAULT 0,
    `mime_type`    VARCHAR(100) NOT NULL,
    `status`       ENUM('pending','approved','rejected','used') NOT NULL DEFAULT 'pending',
    `admin_notes`  TEXT DEFAULT NULL,
    `uploaded_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reviewed_at`  DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// 6. Tasarım talepleri
$sqls[] = "CREATE TABLE IF NOT EXISTS `design_requests` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`     INT UNSIGNED DEFAULT NULL,
    `user_id`      INT UNSIGNED DEFAULT NULL,
    `product_id`   INT UNSIGNED DEFAULT NULL,
    `description`  TEXT NOT NULL,
    `reference_url` VARCHAR(500) DEFAULT NULL,
    `status`       ENUM('new','in_progress','completed','cancelled') NOT NULL DEFAULT 'new',
    `admin_notes`  TEXT DEFAULT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$results = [];
$errors = [];

foreach ($sqls as $i => $sql) {
    try {
        Database::query($sql);
        $results[] = "✅ SQL #{$i} başarıyla çalıştı.";
    } catch (Exception $e) {
        // "Duplicate column" gibi ALTER hatalarını tolere et
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $results[] = "ℹ️ SQL #{$i}: Kolon zaten var, atlandı.";
        } else {
            $errors[] = "❌ SQL #{$i} HATA: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>DB Migration</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px
        }

        .ok {
            background: #f0fdf4;
            border-left: 3px solid #22c55e;
            padding: 8px 12px;
            margin: 4px 0;
            border-radius: 4px
        }

        .err {
            background: #fef2f2;
            border-left: 3px solid #dc2626;
            padding: 8px 12px;
            margin: 4px 0;
            border-radius: 4px
        }

        .warn {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px
        }
    </style>
</head>

<body>
    <h2>🛠️ Varyasyon Sistemi Migration</h2>
    <?php foreach ($results as $r): ?>
        <div class="ok">
            <?= $r ?>
        </div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <div class="err">
            <?= e($e) ?>
        </div>
    <?php endforeach; ?>
    <div class="warn">⚠️ <strong>ÖNEMLİ:</strong> Migration başarılı. Bu dosyayı sunucudan <strong>sil!</strong><br>
        <code>admin/migrations/run-variations.php</code>
    </div>
    <p><a href="../products.php">← Ürünlere Dön</a> | <a href="../variations.php">→ Varyasyon Yönetimi</a></p>
</body>

</html>
