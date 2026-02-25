<?php
/**
 * Bozok E-Ticaret — Boyut Bazlı Fiyatlandırma Migration
 * products tablosuna pricing alanları ekler.
 *
 * KULLANIM: /admin/migrations/run-dimension-pricing.php
 * SONRA: Bu dosyayı silin!
 */
require_once __DIR__ . '/../../config/config.php';
if (!isAdmin()) {
    http_response_code(403);
    die('Yetkisiz.');
}

$results = [];
$errors = [];

function runSqlDp(string $label, string $sql, array $params = []): void
{
    global $results, $errors;
    try {
        Database::query($sql, $params);
        $results[] = "✅ $label";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (
            stripos($msg, 'Duplicate') !== false ||
            stripos($msg, 'already exists') !== false ||
            stripos($msg, "already") !== false
        ) {
            $results[] = "ℹ️ $label (zaten mevcut, atlandı)";
        } else {
            $errors[] = "❌ $label — $msg";
        }
    }
}

// 1. pricing_type
runSqlDp(
    'products.pricing_type',
    "ALTER TABLE products ADD COLUMN pricing_type ENUM('fixed','per_m2','per_piece') NOT NULL DEFAULT 'fixed' AFTER price"
);

// 2. price_per_m2
runSqlDp(
    'products.price_per_m2',
    "ALTER TABLE products ADD COLUMN price_per_m2 DECIMAL(10,2) DEFAULT NULL COMMENT 'm2 başına fiyat (TL)' AFTER pricing_type"
);

// 3. min_width_cm
runSqlDp(
    'products.min_width_cm',
    "ALTER TABLE products ADD COLUMN min_width_cm INT UNSIGNED DEFAULT NULL COMMENT 'Min en (cm)' AFTER price_per_m2"
);

// 4. max_width_cm
runSqlDp(
    'products.max_width_cm',
    "ALTER TABLE products ADD COLUMN max_width_cm INT UNSIGNED DEFAULT NULL COMMENT 'Max en (cm)' AFTER min_width_cm"
);

// 5. min_height_cm
runSqlDp(
    'products.min_height_cm',
    "ALTER TABLE products ADD COLUMN min_height_cm INT UNSIGNED DEFAULT NULL COMMENT 'Min boy (cm)' AFTER max_width_cm"
);

// 6. max_height_cm
runSqlDp(
    'products.max_height_cm',
    "ALTER TABLE products ADD COLUMN max_height_cm INT UNSIGNED DEFAULT NULL COMMENT 'Max boy (cm)' AFTER min_height_cm"
);

// 7. pricing_extra_options (JSON: ek seçenekler - dikiş, germe vs.)
runSqlDp(
    'products.pricing_extra_options',
    "ALTER TABLE products ADD COLUMN pricing_extra_options JSON DEFAULT NULL COMMENT 'Ek fiyatlandırma seçenekleri' AFTER max_height_cm"
);

// 8. order_items: dimensions alanı (sipariş anında boyut kaydı)
runSqlDp(
    'order_items.dimensions',
    "ALTER TABLE order_items ADD COLUMN dimensions JSON DEFAULT NULL COMMENT 'Boyut bazlı sipariş detayı' AFTER total"
);

?><!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Boyut Fiyatlandırma Migration</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 700px;
            margin: 40px auto;
            padding: 0 20px
        }

        .ok {
            background: #f0fdf4;
            border-left: 3px solid #22c55e;
            padding: 8px 12px;
            margin: 4px 0;
            border-radius: 4px;
            font-size: .9rem
        }

        .err {
            background: #fef2f2;
            border-left: 3px solid #dc2626;
            padding: 8px 12px;
            margin: 4px 0;
            border-radius: 4px;
            font-size: .9rem
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
    <h2>📐 Boyut Bazlı Fiyatlandırma Migration</h2>
    <?php foreach ($results as $r): ?>
        <div class="ok">
            <?= htmlspecialchars($r) ?>
        </div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <div class="err">
            <?= htmlspecialchars($e) ?>
        </div>
    <?php endforeach; ?>
    <div class="warn">⚠️ <strong>ÖNEMLİ:</strong> Migration başarılı. Bu dosyayı sunucudan <strong>hemen sil!</strong>
    </div>
    <p style="margin-top:20px">
        <a href="../products.php"
            style="background:#2563eb;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none">→ Ürünlere
            Git</a>
    </p>
</body>

</html>
