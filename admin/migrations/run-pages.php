<?php
/**
 * V-Commerce — Sayfa Yönetimi Migration
 * pages tablosunu oluşturur ve varsayılan hukuki sayfaları ekler.
 *
 * KULLANIM: /admin/migrations/run-pages.php
 * SONRA: Bu dosyayı silin!
 */
require_once __DIR__ . '/../../config/config.php';
if (!isAdmin()) {
    http_response_code(403);
    die('Yetkisiz.');
}

$results = [];
$errors = [];

function runSql(string $label, string $sql, array $params = []): void
{
    global $results, $errors;
    try {
        Database::query($sql, $params);
        $results[] = "✅ $label";
    } catch (Exception $e) {
        if (
            strpos($e->getMessage(), 'Duplicate') !== false ||
            strpos($e->getMessage(), 'already exists') !== false
        ) {
            $results[] = "ℹ️ $label (zaten mevcut, atlandı)";
        } else {
            $errors[] = "❌ $label — " . $e->getMessage();
        }
    }
}

// 1. pages tablosu
runSql('pages tablosu', "CREATE TABLE IF NOT EXISTS `pages` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`            VARCHAR(255) NOT NULL,
    `slug`             VARCHAR(280) NOT NULL,
    `content`          LONGTEXT DEFAULT NULL,
    `meta_title`       VARCHAR(255) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `status`           TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`       INT NOT NULL DEFAULT 0,
    `show_in_footer`   TINYINT(1) NOT NULL DEFAULT 1,
    `is_system`        TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Sistem sayfaları silinemez',
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 2. Varsayılan sayfalar
$defaultPages = [
    [
        'slug' => 'hakkimizda',
        'title' => 'Hakkımızda',
        'content' => '<h2>Hakkımızda</h2><p>Şirketiniz hakkında bilgi buraya eklenecek.</p>',
        'meta_title' => 'Hakkımızda',
        'meta_description' => 'Şirketimiz hakkında detaylı bilgi.',
        'sort_order' => 1,
        'is_system' => 1,
    ],
    [
        'slug' => 'iletisim',
        'title' => 'İletişim',
        'content' => '<h2>İletişim</h2><p>İletişim bilgileri buraya eklenecek.</p>',
        'meta_title' => 'İletişim',
        'meta_description' => 'Bizimle iletişime geçin.',
        'sort_order' => 2,
        'is_system' => 1,
    ],
    [
        'slug' => 'gizlilik-politikasi',
        'title' => 'Gizlilik Politikası',
        'content' => '<h2>Gizlilik Politikası</h2><p>KVKK kapsamında gizlilik politikanız buraya eklenecek.</p>',
        'meta_title' => 'Gizlilik Politikası',
        'meta_description' => 'Kişisel verilerinizi nasıl koruduğumuzu öğrenin.',
        'sort_order' => 3,
        'is_system' => 1,
    ],
    [
        'slug' => 'kvkk',
        'title' => 'KVKK Aydınlatma Metni',
        'content' => '<h2>KVKK Aydınlatma Metni</h2><p>6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında aydınlatma metni buraya eklenecek.</p>',
        'meta_title' => 'KVKK Aydınlatma Metni',
        'meta_description' => 'KVKK kapsamında kişisel verilerinizin işlenmesi hakkında bilgi.',
        'sort_order' => 4,
        'is_system' => 1,
    ],
    [
        'slug' => 'mesafeli-satis-sozlesmesi',
        'title' => 'Mesafeli Satış Sözleşmesi',
        'content' => '<h2>Mesafeli Satış Sözleşmesi</h2><p>Mesafeli satış sözleşmeniz buraya eklenecek. Bu sözleşme 6502 sayılı Tüketicinin Korunması Hakkında Kanun kapsamında zorunludur.</p>',
        'meta_title' => 'Mesafeli Satış Sözleşmesi',
        'meta_description' => 'Mesafeli satış sözleşmesi şartları ve koşulları.',
        'sort_order' => 5,
        'is_system' => 1,
    ],
    [
        'slug' => 'cerez-politikasi',
        'title' => 'Çerez Politikası',
        'content' => '<h2>Çerez Politikası</h2><p>Sitemizde kullanılan çerezler hakkında bilgi buraya eklenecek.</p>',
        'meta_title' => 'Çerez Politikası',
        'meta_description' => 'Çerez kullanımı hakkında bilgi.',
        'sort_order' => 6,
        'is_system' => 1,
    ],
    [
        'slug' => 'iptal-iade',
        'title' => 'İptal ve İade Politikası',
        'content' => '<h2>İptal ve İade Politikası</h2><p>İptal ve iade koşullarınız buraya eklenecek.</p>',
        'meta_title' => 'İptal ve İade',
        'meta_description' => 'Sipariş iptal ve iade politikamız.',
        'sort_order' => 7,
        'is_system' => 1,
    ],
];

foreach ($defaultPages as $p) {
    runSql(
        "Sayfa: {$p['title']}",
        "INSERT IGNORE INTO pages (title, slug, content, meta_title, meta_description, sort_order, is_system, show_in_footer, status)
         VALUES (?,?,?,?,?,?,?,1,1)",
        [$p['title'], $p['slug'], $p['content'], $p['meta_title'], $p['meta_description'], $p['sort_order'], $p['is_system']]
    );
}

?><!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Pages Migration</title>
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
    <h2>📄 Sayfa Yönetimi Migration</h2>
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
    <div class="warn">⚠️ <strong>ÖNEMLİ:</strong> Migration başarılı. Bu dosyayı sunucudan <strong>hemen
            sil!</strong><br>
        <code>admin/migrations/run-pages.php</code>
    </div>
    <p style="margin-top:20px">
        <a href="../pages.php"
            style="background:#2563eb;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none">→ Sayfa
            Yönetimine Git</a>
    </p>
</body>

</html>
