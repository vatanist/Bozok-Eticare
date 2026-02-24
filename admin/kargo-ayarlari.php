<?php
/**
 * Admin — Kargo Ayarları (Desi Matrisi)
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Kargo Ayarları';
$adminPage = 'settings';

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dogrula_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_rate') {
        $carrier = $_POST['carrier_name'] ?? 'Genel';
        $min = floatval($_POST['min_desi'] ?? 0);
        $max = floatval($_POST['max_desi'] ?? 999);
        $price = floatval($_POST['price'] ?? 0);

        Database::query(
            "INSERT INTO shipping_rates (carrier_name, min_desi, max_desi, price) VALUES (?, ?, ?, ?)",
            [$carrier, $min, $max, $price]
        );
        mesaj('kargo', 'Yeni kargo oranı eklendi.', 'basari');
    } elseif ($action === 'delete_rate') {
        $id = intval($_POST['rate_id'] ?? 0);
        Database::query("DELETE FROM shipping_rates WHERE id = ?", [$id]);
        mesaj('kargo', 'Kargo oranı silindi.', 'basari');
    }
    git('/admin/kargo-ayarlari.php');
}

$rates = Database::fetchAll("SELECT * FROM shipping_rates ORDER BY min_desi ASC");

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <h1><i class="fas fa-truck-loading" style="color:var(--admin-primary)"></i> Kargo & Desi Ayarları</h1>
</div>

<?php mesaj_goster('kargo'); ?>

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">

    <!-- Yeni Oran Ekle -->
    <div class="admin-card">
        <h3 style="margin-bottom:16px">Yeni Oran Ekle</h3>
        <form method="POST" class="admin-form">
            <?= csrf_kod() ?>
            <input type="hidden" name="action" value="add_rate">

            <div class="form-group">
                <label>Kargo Firması</label>
                <select name="carrier_name" class="form-control">
                    <option value="Genel">Genel (Tümü)</option>
                    <?php foreach (Cargo::getCarriers() as $ck => $cv): ?>
                        <option value="<?= e($cv['name']) ?>">
                            <?= e($cv['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Min Desi</label>
                    <input type="number" step="0.1" name="min_desi" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>Max Desi</label>
                    <input type="number" step="0.1" name="max_desi" class="form-control" value="10">
                </div>
            </div>

            <div class="form-group">
                <label>Fiyat (TL)</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>

            <button type="submit" class="admin-btn admin-btn-primary" style="width:100%">
                <i class="fas fa-plus"></i> Oranı Kaydet
            </button>
        </form>
    </div>

    <!-- Mevcut Oranlar -->
    <div class="admin-card" style="padding:0">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Firma</th>
                        <th>Desi Aralığı</th>
                        <th>Fiyat</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rates)): ?>
                        <tr>
                            <td colspan="4" class="text-center" style="padding:40px;color:var(--admin-gray)">
                                Henüz kargo oranı tanımlanmadı. Sistem varsayılan kargo ücretini kullanacaktır.
                            </td>
                        </tr>
                    <?php else:
                        foreach ($rates as $r): ?>
                            <tr>
                                <td><strong>
                                        <?= e($r['carrier_name']) ?>
                                    </strong></td>
                                <td>
                                    <?= $r['min_desi'] ?> -
                                    <?= $r['max_desi'] ?> Desi
                                </td>
                                <td><strong>
                                        <?= para_yaz($r['price']) ?>
                                    </strong></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Bu oranı silmek istediğinize emin misiniz?')">
                                        <?= csrf_kod() ?>
                                        <input type="hidden" name="action" value="delete_rate">
                                        <input type="hidden" name="rate_id" value="<?= $r['id'] ?>">
                                        <button class="admin-btn admin-btn-sm text-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>