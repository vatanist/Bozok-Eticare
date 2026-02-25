<?php
/**
 * Bozok E-Ticaret — Modüler Çekirdek Migration
 * extensions tablosunu oluşturur.
 */
require_once __DIR__ . '/../../config/config.php';
if (!isAdmin()) {
    http_response_code(403);
    die('Yetkisiz.');
}

try {
    Database::query("CREATE TABLE IF NOT EXISTS `extensions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `type` VARCHAR(50) NOT NULL COMMENT 'payment, module, shipping',
        `code` VARCHAR(100) NOT NULL COMMENT 'paytr, havale, dimensions',
        `status` TINYINT(1) DEFAULT 1,
        `sort_order` INT DEFAULT 0,
        `settings` JSON DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `idx_ext` (`type`, `code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");

    // Varsayılan modülleri ekle (zaten kodda olanları sisteme tanıt)
    Database::query("INSERT IGNORE INTO extensions (type, code, status) VALUES ('payment', 'paytr', 1)");
    Database::query("INSERT IGNORE INTO extensions (type, code, status) VALUES ('payment', 'havale', 1)");
    Database::query("INSERT IGNORE INTO extensions (type, code, status) VALUES ('module', 'dimensions', 1)");

    echo "✅ Modüler çekirdek başarıyla kuruldu.";
} catch (Exception $e) {
    echo "❌ HATA: " . $e->getMessage();
}
