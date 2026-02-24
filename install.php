<?php
/**
 * V-Commerce - Kurulum Sihirbazı
 * İlk kurulumda veritabanı ve admin hesabı oluşturur.
 */
session_start();

// ── Production kilidi: .env APP_ENV=production ise kurulum devre dışı ──
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = @file_get_contents($envFile);
    if ($envContent !== false && preg_match('/^APP_ENV\s*=\s*production/mi', $envContent)) {
        http_response_code(403);
        die('<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>Erişim Engellendi</title>'
            . '<style>body{font-family:Inter,sans-serif;display:flex;justify-content:center;align-items:center;'
            . 'min-height:100vh;margin:0;background:#f8f9fa;color:#333}'
            . '.box{text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 2px 20px rgba(0,0,0,.08)}'
            . 'h1{font-size:1.5em;margin:0 0 10px;color:#e74c3c}p{color:#666;margin:5px 0}</style></head>'
            . '<body><div class="box"><h1>🔒 Kurulum Devre Dışı</h1>'
            . '<p>Bu sistem production modunda çalışıyor.</p>'
            . '<p><small>Kurulumu yeniden etkinleştirmek için .env dosyasında APP_ENV=development yapın.</small></p>'
            . '</div></body></html>');
    }
}

// Kurulum zaten yapılmış mı?
$lockFile = __DIR__ . '/config/.installed';
if (file_exists($lockFile)) {
    header('Location: index.php');
    exit;
}

$step = intval($_GET['step'] ?? 1);
$error = '';
$success = '';

// =================== STEP 2: DB & Admin Oluştur ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = $_POST['db_pass'] ?? '';
    $dbName = trim($_POST['db_name'] ?? 'vcommerce');

    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminFirst = trim($_POST['admin_first'] ?? '');
    $adminLast = trim($_POST['admin_last'] ?? '');

    $siteName = trim($_POST['site_name'] ?? 'V-Commerce');
    $siteEmail = trim($_POST['site_email'] ?? '');

    // Validasyon
    if (!$adminUser || !$adminEmail || !$adminPass) {
        $error = 'Admin bilgileri zorunludur.';
    } elseif (strlen($adminPass) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır.';
    } else {
        try {
            // DB bağlantı testi
            $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // DB oluştur
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci");
            $pdo->exec("USE `$dbName`");

            // ========== TABLOLAR ==========
            // ========== TABLOLAR ==========
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->exec("DROP TABLE IF EXISTS `wishlist`, `cart`, `order_items`, `orders`, `addresses`, `products`, `categories`, `users`, `settings`, `xml_imports`, `campaigns`, `campaign_usage`, `sliders`, `price_alerts`, `extensions` ");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            $pdo->exec("CREATE TABLE `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(50) UNIQUE NOT NULL,
                `email` VARCHAR(100) UNIQUE NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `first_name` VARCHAR(50) DEFAULT '',
                `last_name` VARCHAR(50) DEFAULT '',
                `phone` VARCHAR(20) DEFAULT '',
                `role` ENUM('admin','customer') DEFAULT 'customer',
                `avatar` VARCHAR(255) DEFAULT NULL,
                `status` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE `categories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(120) UNIQUE NOT NULL,
                `description` TEXT DEFAULT NULL,
                `image` VARCHAR(255) DEFAULT NULL,
                `icon` VARCHAR(50) DEFAULT 'fas fa-box',
                `parent_id` INT DEFAULT NULL,
                `sort_order` INT DEFAULT 0,
                `status` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE `products` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `category_id` INT DEFAULT NULL,
                `name` VARCHAR(255) NOT NULL,
                `model` VARCHAR(64) DEFAULT NULL,
                `slug` VARCHAR(255) UNIQUE NOT NULL,
                `product_type` ENUM('simple', 'variable') DEFAULT 'simple',
                `description` TEXT,
                `short_description` VARCHAR(500) DEFAULT '',
                `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
                `discount_price` DECIMAL(10,2) DEFAULT NULL,
                `stock` INT DEFAULT 0,
                `minimum_qty` INT DEFAULT 1,
                `subtract_stock` TINYINT(1) DEFAULT 1,
                `out_of_stock_status_id` INT DEFAULT 7,
                `requires_shipping` TINYINT(1) DEFAULT 1,
                `date_available` DATE DEFAULT NULL,
                `desi` DECIMAL(10,2) DEFAULT 0,
                `sku` VARCHAR(50) DEFAULT NULL,
                `barcode` VARCHAR(100) DEFAULT NULL,
                `upc` VARCHAR(12) DEFAULT NULL,
                `ean` VARCHAR(14) DEFAULT NULL,
                `jan` VARCHAR(13) DEFAULT NULL,
                `isbn` VARCHAR(17) DEFAULT NULL,
                `mpn` VARCHAR(64) DEFAULT NULL,
                `location` VARCHAR(128) DEFAULT NULL,
                `brand` VARCHAR(100) DEFAULT NULL,
                `image` VARCHAR(255) DEFAULT NULL,
                `images` JSON DEFAULT NULL,
                `length` DECIMAL(15,8) DEFAULT 0.00000000,
                `width` DECIMAL(15,8) DEFAULT 0.00000000,
                `height` DECIMAL(15,8) DEFAULT 0.00000000,
                `length_class_id` INT DEFAULT 1,
                `weight` DECIMAL(15,8) DEFAULT 0.00000000,
                `weight_class_id` INT DEFAULT 1,
                `pricing_type` ENUM('fixed','per_m2','per_piece') DEFAULT 'fixed',
                `price_per_m2` DECIMAL(10,2) DEFAULT 0,
                `min_width_cm` INT DEFAULT 0,
                `max_width_cm` INT DEFAULT 0,
                `min_height_cm` INT DEFAULT 0,
                `max_height_cm` INT DEFAULT 0,
                `is_featured` TINYINT(1) DEFAULT 0,
                `status` TINYINT(1) DEFAULT 1,
                `sort_order` INT DEFAULT 0,
                `pricing_extra_options` JSON DEFAULT NULL,
                `meta_title` VARCHAR(255) DEFAULT '',
                `meta_description` TEXT,
                `meta_keyword` TEXT,
                `tags` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE `orders` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `order_number` VARCHAR(50) UNIQUE NOT NULL,
                `user_id` INT DEFAULT NULL,
                `total` DECIMAL(10,2) NOT NULL,
                `subtotal` DECIMAL(10,2) NOT NULL,
                `shipping_cost` DECIMAL(10,2) DEFAULT 0,
                `discount_amount` DECIMAL(10,2) DEFAULT 0,
                `delivery_fee` DECIMAL(10,2) DEFAULT 0,
                `status` ENUM('pending','pending_payment','processing','shipped','delivered','cancelled') DEFAULT 'pending',
                `tracking_number` VARCHAR(100) DEFAULT NULL,
                `shipping_carrier` VARCHAR(50) DEFAULT NULL,
                `shipped_at` DATETIME DEFAULT NULL,
                `payment_method` VARCHAR(50) DEFAULT 'kapida_odeme',
                `payment_status` ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
                `payment_transaction_id` VARCHAR(100) DEFAULT NULL,
                `shipping_first_name` VARCHAR(100) NOT NULL,
                `shipping_last_name` VARCHAR(100) NOT NULL,
                `shipping_phone` VARCHAR(20) NOT NULL,
                `shipping_address` TEXT NOT NULL,
                `shipping_city` VARCHAR(50) NOT NULL,
                `shipping_district` VARCHAR(50) DEFAULT '',
                `shipping_neighborhood` VARCHAR(100) DEFAULT '',
                `shipping_zip` VARCHAR(10) DEFAULT '',
                `billing_address_same` TINYINT(1) DEFAULT 1,
                `notes` TEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE `order_items` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `order_id` INT NOT NULL,
                `product_id` INT DEFAULT NULL,
                `product_name` VARCHAR(255) NOT NULL,
                `product_image` VARCHAR(255) DEFAULT NULL,
                `quantity` INT NOT NULL,
                `price` DECIMAL(10,2) NOT NULL,
                `total` DECIMAL(10,2) NOT NULL,
                `dimensions` JSON DEFAULT NULL,
                `variation_data` JSON DEFAULT NULL,
                FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE `addresses` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `title` VARCHAR(50) DEFAULT 'Ev',
                `first_name` VARCHAR(50) NOT NULL,
                `last_name` VARCHAR(50) NOT NULL,
                `phone` VARCHAR(20) NOT NULL,
                `address_line` TEXT NOT NULL,
                `city` VARCHAR(50) NOT NULL,
                `district` VARCHAR(50) DEFAULT '',
                `neighborhood` VARCHAR(100) DEFAULT '',
                `zip_code` VARCHAR(10) DEFAULT '',
                `is_default` TINYINT(1) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE `wishlist` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `product_id` INT NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `user_product` (`user_id`, `product_id`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE `cart` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT DEFAULT NULL,
                `session_id` VARCHAR(100) DEFAULT NULL,
                `product_id` INT NOT NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `dimensions` JSON DEFAULT NULL,
                `unit_price_override` DECIMAL(10,2) DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE `settings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `group_key` VARCHAR(50) DEFAULT 'general',
                `name` VARCHAR(50) NOT NULL,
                `value` TEXT DEFAULT NULL,
                `type` VARCHAR(20) DEFAULT 'text',
                UNIQUE KEY `group_name` (`group_key`, `name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `xml_imports` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `filename` VARCHAR(255) NOT NULL DEFAULT '',
                `total_items` INT DEFAULT 0,
                `imported_items` INT DEFAULT 0,
                `failed_items` INT DEFAULT 0,
                `status` ENUM('pending','running','completed','failed') DEFAULT 'pending',
                `log` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            // Kampanya tabloları
            $pdo->exec("CREATE TABLE IF NOT EXISTS `campaigns` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `type` ENUM('percentage','gift_voucher','discount_code','customer_specific') NOT NULL DEFAULT 'discount_code',
                `name` VARCHAR(255) NOT NULL,
                `code` VARCHAR(50) DEFAULT NULL,
                `discount_percent` DECIMAL(5,2) DEFAULT 0,
                `discount_amount` DECIMAL(10,2) DEFAULT 0,
                `min_order_amount` DECIMAL(10,2) DEFAULT 0,
                `max_discount` DECIMAL(10,2) DEFAULT 0,
                `user_id` INT DEFAULT NULL,
                `usage_limit` INT DEFAULT 0,
                `used_count` INT DEFAULT 0,
                `start_date` DATETIME DEFAULT NULL,
                `end_date` DATETIME DEFAULT NULL,
                `status` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `campaign_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `campaign_usage` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `campaign_id` INT NOT NULL,
                `user_id` INT DEFAULT NULL,
                `order_id` INT DEFAULT NULL,
                `discount_amount` DECIMAL(10,2) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            // Slider tablosu
            $pdo->exec("CREATE TABLE IF NOT EXISTS `sliders` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `description` VARCHAR(500) DEFAULT '',
                `badge` VARCHAR(100) DEFAULT '',
                `button_text` VARCHAR(100) DEFAULT 'Keşfet',
                `button_url` VARCHAR(500) DEFAULT '#',
                `gradient_start` VARCHAR(7) DEFAULT '#1a56db',
                `gradient_end` VARCHAR(7) DEFAULT '#1e40af',
                `image` VARCHAR(500) DEFAULT NULL,
                `sort_order` INT DEFAULT 0,
                `status` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            // Fiyat uyarısı tablosu
            $pdo->exec("CREATE TABLE IF NOT EXISTS `price_alerts` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `product_id` INT NOT NULL,
                `target_price` DECIMAL(10,2) NOT NULL,
                `original_price` DECIMAL(10,2) NOT NULL,
                `notified` TINYINT(1) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_alert` (`user_id`, `product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            // Eklenti ve Tema Takip Tablosu
            $pdo->exec("CREATE TABLE IF NOT EXISTS `extensions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `type` ENUM('module','theme') NOT NULL,
                `category` VARCHAR(50) DEFAULT 'genel',
                `code` VARCHAR(100) NOT NULL,
                `status` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `type_code` (`type`, `code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `product_attributes` (
                `product_id` INT NOT NULL,
                `attribute_id` INT NOT NULL,
                `text` TEXT NOT NULL,
                PRIMARY KEY (`product_id`, `attribute_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            // SEÇENEK MOTORU (Enterprise Options)
            $pdo->exec("CREATE TABLE IF NOT EXISTS `options` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(50) NOT NULL,
                `type` VARCHAR(20) DEFAULT 'select',
                `sort_order` INT DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `option_values` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `option_id` INT NOT NULL,
                `name` VARCHAR(50) NOT NULL,
                `image` VARCHAR(255) DEFAULT NULL,
                `sort_order` INT DEFAULT 0,
                FOREIGN KEY (`option_id`) REFERENCES `options`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `product_options` (
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

            // ========== FAZ 3-7 EK TABLOLAR ==========
            $pdo->exec("CREATE TABLE IF NOT EXISTS `attributes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(50) NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `attribute_values` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `attribute_id` INT NOT NULL,
                `value` VARCHAR(50) NOT NULL,
                FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `product_variations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `sku` VARCHAR(50) DEFAULT NULL,
                `barcode` VARCHAR(100) DEFAULT NULL,
                `price_modifier` DECIMAL(10,2) DEFAULT 0,
                `stock` INT DEFAULT 0,
                `specs` JSON DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `inventory_log` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `variation_id` INT DEFAULT NULL,
                `change_amount` INT NOT NULL,
                `reason` VARCHAR(255) DEFAULT 'Manuel Güncelleme',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `order_history` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `order_id` INT NOT NULL,
                `status` VARCHAR(50) NOT NULL,
                `note` TEXT,
                `notify_user` TINYINT(1) DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,                FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `shipping_rates` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `carrier_name` VARCHAR(50) NOT NULL,
                `min_desi` DECIMAL(10,2) DEFAULT 0,
                `max_desi` DECIMAL(10,2) DEFAULT 0,
                `price` DECIMAL(10,2) NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `product_mappings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `marketplace` VARCHAR(50) NOT NULL,
                `remote_id` VARCHAR(100) NOT NULL,
                `sync_status` VARCHAR(20) DEFAULT 'pending',
                `last_sync` DATETIME DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`product_id`), INDEX (`marketplace`), INDEX (`remote_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `affiliates` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `ref_code` VARCHAR(50) UNIQUE NOT NULL,
                `commission_rate` DECIMAL(5,2) DEFAULT 10.00,
                `balance` DECIMAL(10,2) DEFAULT 0.00,
                `status` ENUM('active', 'passive') DEFAULT 'active',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`user_id`), INDEX (`ref_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `affiliate_referrals` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `affiliate_id` INT NOT NULL,
                `order_id` INT NOT NULL,
                `commission_amount` DECIMAL(10,2) NOT NULL,
                `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`affiliate_id`), INDEX (`order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `banners` (
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

            $pdo->exec("CREATE TABLE IF NOT EXISTS `device_tokens` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `token` TEXT NOT NULL,
                `platform` ENUM('android', 'ios', 'web') DEFAULT 'android',
                `last_used` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `visitor_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `ip` VARCHAR(45),
                `user_agent` TEXT,
                `page_url` TEXT,
                `referrer` TEXT,
                `session_id` VARCHAR(100),
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            // Pazaryeri API Log Tablosu
            $pdo->exec("CREATE TABLE IF NOT EXISTS `marketplace_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `marketplace` VARCHAR(50) NOT NULL,
                `action` VARCHAR(100) NOT NULL,
                `request_data` TEXT,
                `response_data` TEXT,
                `status` VARCHAR(20) DEFAULT 'success',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`marketplace`), INDEX (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            // Enterprise Varyasyon Tipleri
            $pdo->exec("CREATE TABLE IF NOT EXISTS `product_variation_types` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT NOT NULL,
                `name` VARCHAR(50) NOT NULL,
                `display_name` VARCHAR(100) NOT NULL,
                `sort_order` INT DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`product_id`),
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

            // Enterprise Varyasyon Seçenekleri
            $pdo->exec("CREATE TABLE IF NOT EXISTS `product_variation_options` (
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

            // Miktar Bazlı Fiyatlandırma
            $pdo->exec("CREATE TABLE IF NOT EXISTS `product_quantity_pricing` (
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

            // ========== ADMIN KULLANICI ==========
            $hashedPass = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO `users` (username, email, password, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, 'admin', 1)");
            $stmt->execute([$adminUser, $adminEmail, $hashedPass, $adminFirst, $adminLast]);

            // ========== ÖRNEK VERİLER (SEED) ==========
            // Özellikler
            $pdo->exec("INSERT INTO `attributes` (name) VALUES ('Marka'), ('Garanti Süresi'), ('Materyal'), ('Ekran Boyutu')");

            // Seçenekler (Varyasyonlar için)
            $pdo->exec("INSERT INTO `options` (name, type) VALUES ('Renk', 'select'), ('Beden', 'select'), ('Numara', 'select')");
            $colorOptId = $pdo->lastInsertId() - 2;
            $sizeOptId = $pdo->lastInsertId() - 1;

            $pdo->exec("INSERT INTO `option_values` (option_id, name) VALUES 
                ($colorOptId, 'Siyah'), ($colorOptId, 'Beyaz'), ($colorOptId, 'Kırmızı'),
                ($sizeOptId, 'S'), ($sizeOptId, 'M'), ($sizeOptId, 'L'), ($sizeOptId, 'XL')");

            // Varsayılan Modüller & Ödeme Metotları
            $pdo->exec("INSERT INTO `extensions` (type, category, code, status) VALUES
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

            // ========== AYARLAR ==========
            $settings = [
                ['site_name', $siteName],
                ['site_description', 'Modern E-Ticaret Platformu'],
                ['site_email', $siteEmail ?: $adminEmail],
                ['site_phone', ''],
                ['site_address', ''],
                ['currency', 'TRY'],
                ['currency_symbol', '₺'],
                ['shipping_cost', '49.90'],
                ['free_shipping_limit', '2000'],
                ['site_theme', 'varsayilan'],
                ['active_theme', 'varsayilan'],
                ['paytr_merchant_id', ''],
                ['paytr_merchant_key', ''],
                ['paytr_merchant_salt', ''],
                ['paytr_test_mode', '1'],
                ['bank_name', ''],
                ['bank_iban', ''],
                ['bank_account_holder', ''],
                ['bank_branch', ''],
                ['db_version', '3.2'],
            ];
            $stmt = $pdo->prepare("INSERT IGNORE INTO `settings` (group_key, name, value, type) VALUES (?, ?, ?, 'text')");
            foreach ($settings as $s)
                $stmt->execute(['general', $s[0], $s[1]]);

            // ========== db.php DOSYASINI YAZ ==========
            $dbConfig = "<?php\n/**\n * V-Commerce - Veritabanı Bağlantısı (PDO Singleton)\n */\n\nclass Database\n{\n    private static \$instance = null;\n    private \$pdo;\n\n    private \$host = " . var_export($dbHost, true) . ";\n    private \$dbname = " . var_export($dbName, true) . ";\n    private \$username = " . var_export($dbUser, true) . ";\n    private \$password = " . var_export($dbPass, true) . ";\n\n    private function __construct()\n    {\n        \$this->connect();\n    }\n\n    private function connect()\n    {\n        try {\n            \$this->pdo = new PDO(\n                \"mysql:host={\$this->host};dbname={\$this->dbname};charset=utf8mb4\",\n                \$this->username,\n                \$this->password,\n                [\n                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n                    PDO::ATTR_EMULATE_PREPARES => false,\n                    PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8mb4\"\n                ]\n            );\n        } catch (PDOException \$e) {\n            die(\"Veritabanı bağlantı hatası: \" . \$e->getMessage());\n        }\n    }\n\n    public static function getInstance()\n    {\n        if (self::\$instance === null) {\n            self::\$instance = new self();\n        }\n        return self::\$instance;\n    }\n\n    public function getConnection()\n    {\n        try {\n            \$this->pdo->query('SELECT 1');\n        } catch (PDOException \$e) {\n            \$this->connect();\n        }\n        return \$this->pdo;\n    }\n\n    public static function reconnect()\n    {\n        \$db = self::getInstance();\n        \$db->connect();\n    }\n\n    public static function query(\$sql, \$params = [])\n    {\n        \$pdo = self::getInstance()->getConnection();\n        try {\n            \$stmt = \$pdo->prepare(\$sql);\n            \$stmt->execute(\$params);\n            return \$stmt;\n        } catch (PDOException \$e) {\n            if (strpos(\$e->getMessage(), 'server has gone away') !== false || \$e->getCode() == 'HY000') {\n                self::reconnect();\n                \$pdo = self::getInstance()->getConnection();\n                \$stmt = \$pdo->prepare(\$sql);\n                \$stmt->execute(\$params);\n                return \$stmt;\n            }\n            throw \$e;\n        }\n    }\n\n    public static function fetch(\$sql, \$params = [])\n    {\n        return self::query(\$sql, \$params)->fetch();\n    }\n\n    public static function fetchAll(\$sql, \$params = [])\n    {\n        return self::query(\$sql, \$params)->fetchAll();\n    }\n\n    public static function lastInsertId()\n    {\n        return self::getInstance()->getConnection()->lastInsertId();\n    }\n}\n";

            file_put_contents(__DIR__ . '/config/db.php', $dbConfig);

            // Lock dosyası oluştur
            file_put_contents($lockFile, date('Y-m-d H:i:s') . "\nInstalled by: $adminUser");

            $_SESSION['install_success'] = true;
            header('Location: install.php?step=3');
            exit;

        } catch (PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>V-Commerce Kurulum</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .installer {
            background: #fff;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .3);
            overflow: hidden;
        }

        .installer-header {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            padding: 32px;
            text-align: center;
            color: #fff;
        }

        .installer-header h1 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .installer-header p {
            opacity: .85;
            font-size: .85rem;
        }

        .installer-logo {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, .2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 1.5rem;
            font-weight: 800;
            backdrop-filter: blur(10px);
        }

        .steps {
            display: flex;
            gap: 0;
            padding: 0 32px;
            margin-top: -20px;
            position: relative;
            z-index: 1;
        }

        .step-item {
            flex: 1;
            text-align: center;
        }

        .step-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 6px;
            font-weight: 700;
            font-size: .85rem;
            color: #94a3b8;
            transition: .3s;
        }

        .step-dot.active {
            background: #2563eb;
            color: #fff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, .4);
        }

        .step-dot.done {
            background: #22c55e;
            color: #fff;
        }

        .step-label {
            font-size: .7rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .step-label.active {
            color: #2563eb;
        }

        .step-label.done {
            color: #22c55e;
        }

        .step-line {
            flex: 0 0 auto;
            width: 60px;
            height: 2px;
            background: #e2e8f0;
            align-self: center;
            margin-bottom: 20px;
        }

        .step-line.done {
            background: #22c55e;
        }

        .installer-body {
            padding: 28px 32px 32px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: .9rem;
            font-family: inherit;
            transition: .2s;
        }

        .form-group input:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .section-title {
            font-size: .75rem;
            font-weight: 700;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 0 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #eff6ff;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .section-title i {
            font-size: .7rem;
        }

        .btn-install {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
            font-family: inherit;
            margin-top: 8px;
        }

        .btn-install:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, .3);
        }

        .error-msg {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: .85rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .success-card {
            text-align: center;
            padding: 20px 0;
        }

        .success-card .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: #fff;
            box-shadow: 0 10px 30px rgba(34, 197, 94, .3);
        }

        .success-card h2 {
            color: #1f2937;
            margin-bottom: 8px;
        }

        .success-card p {
            color: #6b7280;
            font-size: .9rem;
            margin-bottom: 20px;
        }

        .success-links {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .success-links a {
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: .85rem;
            transition: .2s;
        }

        .success-links a.primary {
            background: #2563eb;
            color: #fff;
        }

        .success-links a.secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .success-links a:hover {
            transform: translateY(-1px);
        }

        .req-list {
            list-style: none;
            padding: 0;
        }

        .req-list li {
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .85rem;
        }

        .req-list li i.fa-check-circle {
            color: #22c55e;
        }

        .req-list li i.fa-times-circle {
            color: #ef4444;
        }

        .btn-next {
            display: inline-block;
            padding: 11px 28px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: .2s;
            margin-top: 10px;
        }

        .btn-next:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, .3);
        }
    </style>
</head>

<body>
    <div class="installer">
        <div class="installer-header">
            <div class="installer-logo">V</div>
            <h1>V-Commerce Kurulum</h1>
            <p>E-Ticaret platformunuzu birkaç adımda kurun</p>
        </div>

        <div class="steps" style="padding-top:24px;padding-bottom:12px">
            <div class="step-item">
                <div class="step-dot <?= $step === 1 ? 'active' : ($step > 1 ? 'done' : '') ?>">
                    <?= $step > 1 ? '<i class="fas fa-check"></i>' : '1' ?>
                </div>
                <div class="step-label <?= $step === 1 ? 'active' : ($step > 1 ? 'done' : '') ?>">Gereksinimler</div>
            </div>
            <div class="step-line <?= $step > 1 ? 'done' : '' ?>"></div>
            <div class="step-item">
                <div class="step-dot <?= $step === 2 ? 'active' : ($step > 2 ? 'done' : '') ?>">
                    <?= $step > 2 ? '<i class="fas fa-check"></i>' : '2' ?>
                </div>
                <div class="step-label <?= $step === 2 ? 'active' : ($step > 2 ? 'done' : '') ?>">Kurulum</div>
            </div>
            <div class="step-line <?= $step > 2 ? 'done' : '' ?>"></div>
            <div class="step-item">
                <div class="step-dot <?= $step === 3 ? 'active' : '' ?>">
                    <?= $step === 3 ? '<i class="fas fa-check"></i>' : '3' ?>
                </div>
                <div class="step-label <?= $step === 3 ? 'done' : '' ?>">Tamamlandı</div>
            </div>
        </div>

        <div class="installer-body">
            <?php if ($step === 1): ?>
                <!-- ADIM 1: Gereksinimler -->
                <div class="section-title"><i class="fas fa-clipboard-check"></i> Sistem Gereksinimleri</div>
                <?php
                $phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
                $pdoOk = extension_loaded('pdo_mysql');
                $jsonOk = extension_loaded('json');
                $mbOk = extension_loaded('mbstring');
                $writableConfig = is_writable(__DIR__ . '/config/');
                $writableUploads = is_writable(__DIR__ . '/assets/uploads/') || !is_dir(__DIR__ . '/assets/uploads/');
                $allOk = $phpOk && $pdoOk && $jsonOk && $mbOk && $writableConfig;
                ?>
                <ul class="req-list">
                    <li><i class="fas <?= $phpOk ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> PHP 8.0+ <span
                            style="margin-left:auto;color:#94a3b8;font-size:.8rem">
                            <?= PHP_VERSION ?>
                        </span></li>
                    <li><i class="fas <?= $pdoOk ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> PDO MySQL Extension</li>
                    <li><i class="fas <?= $jsonOk ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> JSON Extension</li>
                    <li><i class="fas <?= $mbOk ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> Multibyte String Extension
                    </li>
                    <li><i class="fas <?= $writableConfig ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> config/ dizini
                        yazılabilir</li>
                </ul>
                <?php if ($allOk): ?>
                    <a href="install.php?step=2" class="btn-next" style="display:block;text-align:center;margin-top:20px">
                        Devam Et <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <div class="error-msg" style="margin-top:16px">
                        <i class="fas fa-exclamation-triangle"></i> Bazı gereksinimler karşılanmıyor. Lütfen düzeltin.
                    </div>
                <?php endif; ?>

            <?php elseif ($step === 2): ?>
                <!-- ADIM 2: Kurulum Formu -->
                <?php if ($error): ?>
                    <div class="error-msg"><i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="install.php?step=2">
                    <div class="section-title"><i class="fas fa-database"></i> Veritabanı Bilgileri</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Sunucu</label>
                            <input type="text" name="db_host"
                                value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Veritabanı Adı</label>
                            <input type="text" name="db_name"
                                value="<?= htmlspecialchars($_POST['db_name'] ?? 'vcommerce') ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Kullanıcı Adı</label>
                            <input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? 'root') ?>"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Şifre</label>
                            <input type="password" name="db_pass" value="">
                        </div>
                    </div>

                    <div class="section-title"><i class="fas fa-user-shield"></i> Yönetici Hesabı</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ad</label>
                            <input type="text" name="admin_first"
                                value="<?= htmlspecialchars($_POST['admin_first'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Soyad</label>
                            <input type="text" name="admin_last" value="<?= htmlspecialchars($_POST['admin_last'] ?? '') ?>"
                                required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Kullanıcı Adı</label>
                        <input type="text" name="admin_user" value="<?= htmlspecialchars($_POST['admin_user'] ?? '') ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label>E-posta</label>
                        <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Şifre (min. 6 karakter)</label>
                        <input type="password" name="admin_pass" required minlength="6">
                    </div>

                    <div class="section-title"><i class="fas fa-cog"></i> Site Bilgileri</div>
                    <div class="form-group">
                        <label>Site Adı</label>
                        <input type="text" name="site_name"
                            value="<?= htmlspecialchars($_POST['site_name'] ?? 'V-Commerce') ?>">
                    </div>
                    <div class="form-group">
                        <label>İletişim E-postası</label>
                        <input type="email" name="site_email" value="<?= htmlspecialchars($_POST['site_email'] ?? '') ?>"
                            placeholder="boş bırakırsanız admin e-postası kullanılır">
                    </div>

                    <button type="submit" class="btn-install">
                        <i class="fas fa-rocket"></i> Kurulumu Başlat
                    </button>
                </form>

            <?php elseif ($step === 3): ?>
                <!-- ADIM 3: Tamamlandı -->
                <div class="success-card">
                    <div class="icon"><i class="fas fa-check"></i></div>
                    <h2>Kurulum Tamamlandı! 🎉</h2>
                    <p>V-Commerce başarıyla kuruldu. Artık e-ticaret sitenizi yönetmeye başlayabilirsiniz.</p>
                    <div class="success-links">
                        <a href="admin/login.php" class="primary"><i class="fas fa-lock"></i> Admin Paneli</a>
                        <a href="index.php" class="secondary"><i class="fas fa-home"></i> Ana Sayfa</a>
                    </div>
                    <p style="margin-top:20px;font-size:.75rem;color:#9ca3b8">
                        <i class="fas fa-shield-alt"></i> Güvenlik için <code>install.php</code> ve <code>setup.php</code>
                        dosyalarını silmenizi öneririz.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>