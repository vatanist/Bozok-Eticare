<?php
/**
 * Bozok E-Ticaret - Veri Temizleme Scripti
 *
 * Bu script:
 * 1. image alanında 'Array' yazan ürünleri NULL'a düzeltir
 * 2. Genel veri tutarsızlıklarını raporlar
 *
 * KULLANIM: Tarayıcıdan /admin/fix-data.php adresine git
 * SONRA: Bu dosyayı sunucudan sil!
 */

require_once __DIR__ . '/includes/header.php';

$results = [];
$errors = [];

// 1. image = 'Array' olanları NULL yap
try {
    $brokenImages = Database::fetchAll("SELECT id, name, image FROM products WHERE image = 'Array' OR image LIKE 'Array%'");
    $count = count($brokenImages);
    if ($count > 0) {
        Database::query("UPDATE products SET image = NULL WHERE image = 'Array' OR image LIKE 'Array%'");
        $results[] = "✅ <strong>{$count} ürünün</strong> bozuk 'Array' görseli temizlendi (NULL yapıldı).";
        foreach ($brokenImages as $p) {
            $results[] = "&nbsp;&nbsp;→ ID #{$p['id']}: " . htmlspecialchars($p['name']);
        }
    } else {
        $results[] = "✅ 'Array' yazılı bozuk görsel kaydı yok.";
    }
} catch (Exception $e) {
    $errors[] = "image temizleme hatası: " . $e->getMessage();
}

// 2. Slug çakışmalarını kontrol et
try {
    $dupSlugs = Database::fetchAll(
        "SELECT slug, COUNT(*) as cnt FROM products GROUP BY slug HAVING cnt > 1"
    );
    if (!empty($dupSlugs)) {
        foreach ($dupSlugs as $d) {
            $errors[] = "⚠️ Çakışan slug: <code>" . htmlspecialchars($d['slug']) . "</code> — {$d['cnt']} üründe tekrar ediyor";
        }
    } else {
        $results[] = "✅ Slug çakışması yok.";
    }
} catch (Exception $e) {
    $errors[] = "Slug kontrol hatası: " . $e->getMessage();
}

// 3. Kategorisi olmayan ürünler
try {
    $noCat = Database::fetch("SELECT COUNT(*) as c FROM products WHERE category_id IS NULL");
    if ($noCat['c'] > 0) {
        $results[] = "ℹ️ Kategorisiz ürün sayısı: <strong>{$noCat['c']}</strong> — Bu normal olabilir.";
    } else {
        $results[] = "✅ Tüm ürünlerin kategorisi var.";
    }
} catch (Exception $e) {
}

// 4. Stokta sıfır olanları raporla
try {
    $zeroStock = Database::fetch("SELECT COUNT(*) as c FROM products WHERE stock = 0 AND status = 1");
    if ($zeroStock['c'] > 0) {
        $results[] = "ℹ️ Stoku 0 ama aktif olan ürün: <strong>{$zeroStock['c']}</strong>";
    }
} catch (Exception $e) {
}

?>

<div class="admin-header">
    <h1><i class="fas fa-tools" style="color:var(--admin-primary)"></i> Veri Temizleme Scripti</h1>
</div>

<div class="admin-card">
    <h3>Temizleme Sonuçları</h3>
    <?php foreach ($results as $r): ?>
        <p style="padding:8px 12px;margin:4px 0;background:#f0fdf4;border-radius:6px;border-left:3px solid #22c55e">
            <?= $r ?>
        </p>
    <?php endforeach; ?>

    <?php if (!empty($errors)): ?>
        <h3 style="margin-top:16px;color:#dc2626">Uyarılar / Hatalar</h3>
        <?php foreach ($errors as $e): ?>
            <p style="padding:8px 12px;margin:4px 0;background:#fef2f2;border-radius:6px;border-left:3px solid #dc2626">
                <?= $e ?>
            </p>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="margin-top:24px;padding:16px;background:#fef3c7;border-radius:8px;border:1px solid #f59e0b">
        <strong>⚠️ ÖNEMLİ:</strong> Bu scripti çalıştırdıktan sonra sunucudan silmeyi unutma!<br>
        <code>admin/fix-data.php</code> dosyasını FTP veya dosya yöneticisinden sil.
    </div>

    <div style="margin-top:16px">
        <a href="products.php" class="admin-btn admin-btn-primary">
            <i class="fas fa-box"></i> Ürün Yönetimine Dön
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
