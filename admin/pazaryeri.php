<?php
/**
 * Admin — Pazaryeri Entegrasyonu
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Pazaryeri Yönetimi';
$adminPage = 'marketplace';

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dogrula_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'sync_all_stock') {
        try {
            $trendyol = new TrendyolAdapter();
            $mappings = Database::fetchAll("SELECT product_id FROM product_mappings WHERE marketplace = 'trendyol'");
            foreach ($mappings as $m) {
                $trendyol->syncInventory($m['product_id']);
            }
            mesaj('pazaryeri', 'Tüm ürünlerin stokları Trendyol ile başarıyla senkronize edildi.', 'basari');
        } catch (Exception $e) {
            mesaj('pazaryeri', 'Senkronizasyon sırasında hata: ' . $e->getMessage(), 'hata');
        }
    } elseif ($action === 'push_product') {
        try {
            $trendyol = new TrendyolAdapter();
            $pid = intval($_POST['product_id']);
            if ($trendyol->pushProduct($pid)) {
                mesaj('pazaryeri', 'Ürün Trendyol kataloğuna gönderildi.', 'basari');
            }
        } catch (Exception $e) {
            mesaj('pazaryeri', 'Ürün gönderiminde hata: ' . $e->getMessage(), 'hata');
        }
    }
    git('/admin/pazaryeri.php');
}

// Verileri güvenle çek — tablo yoksa boş dizi döndür
try {
    $mappings = Database::fetchAll("
        SELECT pm.*, p.name as product_name, p.stock, p.price, p.sku
        FROM product_mappings pm
        JOIN products p ON p.id = pm.product_id
        ORDER BY pm.last_sync DESC
    ");
} catch (Exception $e) {
    $mappings = [];
}

try {
    $logs = Database::fetchAll("SELECT * FROM marketplace_logs ORDER BY created_at DESC LIMIT 10");
} catch (Exception $e) {
    $logs = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <h1><i class="fas fa-store" style="color:var(--admin-primary)"></i> Pazaryeri Entegrasyonu</h1>
    <div class="admin-header-actions">
        <form method="POST">
            <?= csrf_kod() ?>
            <input type="hidden" name="action" value="sync_all_stock">
            <button type="submit" class="admin-btn admin-btn-secondary">
                <i class="fas fa-sync"></i> Tüm Stokları Güncelle
            </button>
        </form>
    </div>
</div>

<?php mesaj_goster('pazaryeri'); ?>

<div style="display:grid;grid-template-columns:1fr 350px;gap:20px;align-items:start">

    <!-- Aktif Eşleşmeler -->
    <div class="admin-card" style="padding:0">
        <div class="admin-card-header" style="padding:20px">
            <h3 style="margin:0"><i class="fas fa-link"></i> Aktif Ürün Eşleşmeleri</h3>
        </div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Platform</th>
                        <th>Harici ID / Batch</th>
                        <th>Durum</th>
                        <th>Son Senkron</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mappings)): ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding:40px;color:var(--admin-gray)">
                                Henüz pazaryeri ile eşleşmiş bir ürün bulunmuyor.
                            </td>
                        </tr>
                    <?php else:
                        foreach ($mappings as $m): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?= e($m['product_name']) ?>
                                    </strong><br>
                                    <small style="color:var(--admin-gray)">
                                        <?= e($m['sku']) ?> | Stok:
                                        <?= $m['stock'] ?>
                                    </small>
                                </td>
                                <td><span class="admin-badge admin-badge-orange">
                                        <?= strtoupper($m['marketplace']) ?>
                                    </span></td>
                                <td><code><?= e($m['remote_id']) ?></code></td>
                                <td>
                                    <span
                                        class="admin-badge admin-badge-<?= $m['sync_status'] == 'success' ? 'green' : 'blue' ?>">
                                        <?= ucfirst($m['sync_status']) ?>
                                    </span>
                                </td>
                                <td style="font-size:.8rem">
                                    <?= $m['last_sync'] ? date('d.m.Y H:i', strtotime($m['last_sync'])) : '-' ?>
                                </td>
                                <td>
                                    <form method="POST">
                                        <?= csrf_kod() ?>
                                        <input type="hidden" name="action" value="push_product">
                                        <input type="hidden" name="product_id" value="<?= $m['product_id'] ?>">
                                        <button class="admin-btn admin-btn-sm" title="Tekrar Gönder"><i
                                                class="fas fa-paper-plane"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Son İşlemler (Logs) -->
    <div>
        <div class="admin-card">
            <h3 style="margin-bottom:16px"><i class="fas fa-list-alt"></i> Son API İşlemleri</h3>
            <div class="admin-timeline">
                <?php if (empty($logs)): ?>
                    <p style="color:var(--admin-gray);font-size:.85rem">Henüz bir işlem kaydı yok.</p>
                <?php else:
                    foreach ($logs as $l): ?>
                        <div class="timeline-item"
                            style="border-left:2px solid #e2e8f0;padding-left:15px;margin-bottom:15px;position:relative">
                            <div
                                style="position:absolute;left:-6px;top:0;width:10px;height:10px;background:<?= $l['status'] == 'success' ? '#10b981' : '#ef4444' ?>;border-radius:50%">
                            </div>
                            <div style="font-size:.85rem;font-weight:600">
                                <?= e($l['action']) ?>
                            </div>
                            <div style="font-size:.75rem;color:var(--admin-gray)">
                                <?= date('H:i:s', strtotime($l['created_at'])) ?> -
                                <?= strtoupper($l['marketplace']) ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="admin-card" style="margin-top:16px;background:#fff7ed;border:1px solid #ffedd5">
            <h3 style="margin-bottom:10px;color:#9a3412"><i class="fas fa-info-circle"></i> Entegrasyon Durumu</h3>
            <ul style="font-size:.85rem;margin:0;padding-left:20px;color:#9a3412;line-height:1.6">
                <li><strong>Trendyol:</strong> Bağlı (Mock) ✅</li>
                <li><strong>Hepsiburada:</strong> Bekliyor ⏳</li>
                <li><strong>N11:</strong> Bekliyor ⏳</li>
            </ul>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>