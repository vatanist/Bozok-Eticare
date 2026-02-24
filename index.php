<?php
/**
 * V-Commerce - Enterprise Front Controller (Faz 1)
 */
require_once 'config/config.php';

// === ENTERPRISE MIGRATION SYSTEM ===
try {
    $currentDbVersion = getSetting('db_version', '1.0');
} catch (Exception $e) {
    $currentDbVersion = '0.0'; // Ayar çekilemezse (tablo yoksa) migrasyonu zorla
}

if ($currentDbVersion !== '3.2') {
    try {
        // Phase 3 Migration (Master Engine)
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `barcode` VARCHAR(100) DEFAULT NULL AFTER `sku` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `product_type` ENUM('simple', 'variable') DEFAULT 'simple' AFTER `name` ");

        // 2. Özellikler (Renk, Beden vb)
        Database::query("CREATE TABLE IF NOT EXISTS `attributes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(50) NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 3. Özellik Değerleri (Mavi, XL vb)
        Database::query("CREATE TABLE IF NOT EXISTS `attribute_values` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `attribute_id` INT NOT NULL,
            `value` VARCHAR(50) NOT NULL,
            FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 4. Ürün Varyasyonları
        Database::query("CREATE TABLE IF NOT EXISTS `product_variations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT NOT NULL,
            `sku` VARCHAR(50) DEFAULT NULL,
            `barcode` VARCHAR(100) DEFAULT NULL,
            `price_modifier` DECIMAL(10,2) DEFAULT 0,
            `stock` INT DEFAULT 0,
            `specs` JSON DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 5. Envanter Hareket Günlüğü
        Database::query("CREATE TABLE IF NOT EXISTS `inventory_log` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT NOT NULL,
            `variation_id` INT DEFAULT NULL,
            `change_amount` INT NOT NULL,
            `reason` VARCHAR(255) DEFAULT 'Manuel Güncelleme',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 6. Kategori Hiyerarşisi Geliştirme
        Database::query("ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `path` VARCHAR(255) DEFAULT NULL");
        Database::query("ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `level` INT DEFAULT 0");

        // Bir kerelik senkronizasyon
        Product::syncCategoryPaths();

        // === Phase 4 Migration (Order & Cargo Engine) ===
        // 1. Ürünlere Desi Alanı Ekle
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `desi` DECIMAL(10,2) DEFAULT 0 AFTER `stock` ");

        // 2. Siparişlere Kargo Alanları Ekle
        Database::query("ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `tracking_number` VARCHAR(100) DEFAULT NULL AFTER `status` ");
        Database::query("ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_carrier` VARCHAR(50) DEFAULT NULL AFTER `tracking_number` ");
        Database::query("ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipped_at` DATETIME DEFAULT NULL AFTER `shipping_carrier` ");

        // 3. Sipariş Geçmişi Tablosu
        Database::query("CREATE TABLE IF NOT EXISTS `order_history` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `order_id` INT NOT NULL,
            `status` VARCHAR(50) NOT NULL,
            `note` TEXT,
            `notify_user` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 4. Kargo Fiyatlandırma (Desi Bazlı)
        Database::query("CREATE TABLE IF NOT EXISTS `shipping_rates` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `carrier_name` VARCHAR(50) NOT NULL,
            `min_desi` DECIMAL(10,2) DEFAULT 0,
            `max_desi` DECIMAL(10,2) DEFAULT 0,
            `price` DECIMAL(10,2) NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // === Phase 5 Migration (Marketplace & Integration) ===
        // 1. Ürün Eşleştirme Tablosu
        Database::query("CREATE TABLE IF NOT EXISTS `product_mappings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT NOT NULL,
            `marketplace` VARCHAR(50) NOT NULL,
            `remote_id` VARCHAR(100) NOT NULL,
            `sync_status` VARCHAR(20) DEFAULT 'pending',
            `last_sync` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (`product_id`),
            INDEX (`marketplace`),
            INDEX (`remote_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // === Phase 6 Migration (Marketing & Analytics) ===
        // 1. Affiliate (Satış Ortaklığı) Tablosu
        Database::query("CREATE TABLE IF NOT EXISTS `affiliates` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `ref_code` VARCHAR(50) UNIQUE NOT NULL,
            `commission_rate` DECIMAL(5,2) DEFAULT 10.00,
            `balance` DECIMAL(10,2) DEFAULT 0.00,
            `status` ENUM('active', 'passive') DEFAULT 'active',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (`user_id`),
            INDEX (`ref_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 2. Affiliate Referans Takip Tablosu
        Database::query("CREATE TABLE IF NOT EXISTS `affiliate_referrals` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `affiliate_id` INT NOT NULL,
            `order_id` INT NOT NULL,
            `commission_amount` DECIMAL(10,2) NOT NULL,
            `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (`affiliate_id`),
            INDEX (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 3. Banner & Reklam Yönetimi
        Database::query("CREATE TABLE IF NOT EXISTS `banners` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `image` VARCHAR(255) NOT NULL,
            `link` VARCHAR(255) DEFAULT NULL,
            `position` VARCHAR(50) DEFAULT 'homepage_main',
            `status` TINYINT(1) DEFAULT 1,
            `view_count` INT DEFAULT 0,
            `click_count` INT DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 4. Ziyaretçi Log Tablosu
        Database::query("CREATE TABLE IF NOT EXISTS `visitor_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip` VARCHAR(45),
            `user_agent` TEXT,
            `page_url` TEXT,
            `referrer` TEXT,
            `session_id` VARCHAR(100),
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // === Phase 8 Migration (Enterprise Product Fields) ===
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `model` VARCHAR(64) DEFAULT NULL AFTER `name` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `upc` VARCHAR(12) DEFAULT NULL AFTER `barcode` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `ean` VARCHAR(14) DEFAULT NULL AFTER `upc` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `jan` VARCHAR(13) DEFAULT NULL AFTER `ean` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `isbn` VARCHAR(17) DEFAULT NULL AFTER `jan` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `mpn` VARCHAR(64) DEFAULT NULL AFTER `isbn` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `location` VARCHAR(128) DEFAULT NULL AFTER `mpn` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `minimum_qty` INT DEFAULT 1 AFTER `stock` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `subtract_stock` TINYINT(1) DEFAULT 1 AFTER `minimum_qty` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `out_of_stock_status_id` INT DEFAULT 7 AFTER `subtract_stock` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `requires_shipping` TINYINT(1) DEFAULT 1 AFTER `out_of_stock_status_id` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `date_available` DATE DEFAULT NULL AFTER `requires_shipping` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `length` DECIMAL(15,8) DEFAULT 0.00000000 AFTER `height` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `length_class_id` INT DEFAULT 1 AFTER `length` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `weight` DECIMAL(15,8) DEFAULT 0.00000000 AFTER `length_class_id` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `weight_class_id` INT DEFAULT 1 AFTER `weight` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `sort_order` INT DEFAULT 0 AFTER `status` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `meta_title` VARCHAR(255) DEFAULT NULL AFTER `name` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `meta_description` TEXT AFTER `meta_title` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `meta_keyword` TEXT AFTER `meta_description` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `tags` TEXT AFTER `meta_keyword` ");
        Database::query("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `pricing_extra_options` JSON DEFAULT NULL AFTER `max_height_cm` ");

        // Ürün Özellik Eşleştirme (OpenCart Stil)
        Database::query("CREATE TABLE IF NOT EXISTS `product_attributes` (
            `product_id` INT NOT NULL,
            `attribute_id` INT NOT NULL,
            `text` TEXT NOT NULL,
            PRIMARY KEY (`product_id`, `attribute_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 6. Enterprise Seçenek Motoru
        Database::query("CREATE TABLE IF NOT EXISTS `options` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(50) NOT NULL,
            `type` VARCHAR(20) DEFAULT 'select',
            `sort_order` INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        Database::query("CREATE TABLE IF NOT EXISTS `option_values` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `option_id` INT NOT NULL,
            `name` VARCHAR(50) NOT NULL,
            `image` VARCHAR(255) DEFAULT NULL,
            `sort_order` INT DEFAULT 0,
            FOREIGN KEY (`option_id`) REFERENCES `options`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        Database::query("CREATE TABLE IF NOT EXISTS `product_options` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT NOT NULL,
            `option_id` INT NOT NULL,
            `option_value_id` INT NOT NULL,
            `quantity` INT DEFAULT 0,
            `subtract` TINYINT(1) DEFAULT 1,
            `price` DECIMAL(10,2) DEFAULT 0,
            `price_prefix` CHAR(1) DEFAULT '+',
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 7. Seed Data
        Database::query("INSERT IGNORE INTO `options` (id, name, type) VALUES (1, 'Renk', 'select'), (2, 'Beden', 'select')");
        Database::query("INSERT IGNORE INTO `option_values` (option_id, name) VALUES (1, 'Siyah'), (1, 'Beyaz'), (2, 'S'), (2, 'M'), (2, 'L')");

        // 8. Pazaryeri API Logları
        Database::query("CREATE TABLE IF NOT EXISTS `marketplace_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `marketplace` VARCHAR(50) NOT NULL,
            `action` VARCHAR(100) NOT NULL,
            `request_data` TEXT,
            `response_data` TEXT,
            `status` VARCHAR(20) DEFAULT 'success',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (`marketplace`), INDEX (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 9. Enterprise Varyasyon Tipleri & Seçenekleri
        Database::query("CREATE TABLE IF NOT EXISTS `product_variation_types` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT NOT NULL,
            `name` VARCHAR(50) NOT NULL,
            `display_name` VARCHAR(100) NOT NULL,
            `sort_order` INT DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (`product_id`),
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        Database::query("CREATE TABLE IF NOT EXISTS `product_variation_options` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `type_id` INT NOT NULL,
            `value` VARCHAR(100) NOT NULL,
            `price_modifier` DECIMAL(10,2) DEFAULT 0,
            `modifier_type` ENUM('fixed','percent') DEFAULT 'fixed',
            `sort_order` INT DEFAULT 0,
            `is_default` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`type_id`) REFERENCES `product_variation_types`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 10. Miktar Bazlı Fiyatlandırma
        Database::query("CREATE TABLE IF NOT EXISTS `product_quantity_pricing` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `product_id` INT NOT NULL,
            `min_qty` INT NOT NULL DEFAULT 1,
            `max_qty` INT DEFAULT NULL,
            `unit_price` DECIMAL(10,2) NOT NULL,
            `label` VARCHAR(100) DEFAULT '',
            `sort_order` INT DEFAULT 0,
            INDEX (`product_id`),
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

        // 11. Varsayılan Modüller & Ödeme Metotları (Seed)
        Database::query("INSERT IGNORE INTO `extensions` (type, category, code, status) VALUES
            ('payment', 'odeme', 'kapida_odeme', 1),
            ('payment', 'odeme', 'banka_havale', 1),
            ('payment', 'odeme', 'paytr', 0),
            ('payment', 'odeme', 'iyzico', 0),
            ('module', 'genel', 'xml_import', 1),
            ('module', 'genel', 'kampanya_motoru', 1),
            ('module', 'genel', 'affiliate', 0),
            ('module', 'genel', 'push_notification', 0),
            ('module', 'genel', 'seo_otomatik', 1),
            ('module', 'genel', 'visitor_analytics', 1)
        ");

        // Versiyonu güncelle
        Database::query("INSERT INTO settings (name, value) VALUES ('db_version', '3.2') ON DUPLICATE KEY UPDATE value = '3.2'");

    } catch (Exception $e) {
        // Migrasyon hatası (Zaten varsa sessizce devam et)
    }
}

// === DASHBOARD / ANASAYFA KONTROLÜ ===
// Route tanımları artık routes.php'de — temiz mimari
require_once ROOT_PATH . 'routes.php';
