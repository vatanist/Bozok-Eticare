<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();
$pageTitle = 'Modül Yönetimi';
$adminPage = 'extensions';
require_once __DIR__ . '/includes/header.php';

// Modül durumu değiştirme
if (isset($_POST['action']) && $_POST['action'] === 'toggle') {
    $code = $_POST['code'];
    $type = $_POST['type'];
    $status = intval($_POST['status']);

    // Veritabanında güncelle
    Database::query("UPDATE extensions SET status = ? WHERE code = ? AND type = ?", [$status, $code, $type]);
    mesaj('moduller', 'Modül durumu güncellendi.', 'success');
    git('/admin/moduller.php');
}

$odeme_metotlari = Database::fetchAll("SELECT * FROM extensions WHERE type = 'payment' ORDER BY code");
$diger_moduller = Database::fetchAll("SELECT * FROM extensions WHERE type = 'module' ORDER BY code");
?>

<div class="admin-header">
    <h1><i class="fas fa-plug" style="color:var(--admin-primary)"></i> Modül Yönetimi</h1>
</div>

<?php mesaj_goster('moduller'); ?>

<div class="admin-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- ÖDEME METOTLARI -->
    <div class="admin-card" style="padding:0">
        <div class="card-header"
            style="padding:15px; border-bottom:1px solid #eee; background:#f8f9fa; font-weight:600">
            <i class="fas fa-credit-card"></i> Ödeme Metotları
        </div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Modül Adı</th>
                        <th style="width:100px">Durum</th>
                        <th style="width:80px; text-align:right">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($odeme_metotlari)): ?>
                        <tr>
                            <td colspan="3" style="text-align:center; padding:30px; color:var(--admin-gray)">
                                <i class="fas fa-info-circle"></i> Henüz ödeme metodu tanımlanmamış.
                                <br><small>Anasayfayı ziyaret ederek otomatik migrasyon çalıştırabilirsiniz.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($odeme_metotlari as $ext): ?>
                        <tr>
                            <td><strong>
                                    <?= temiz(strtoupper($ext['code'])) ?>
                                </strong></td>
                            <td>
                                <span class="badge badge-<?= $ext['status'] ? 'success' : 'secondary' ?>">
                                    <?= $ext['status'] ? 'Aktif' : 'Pasif' ?>
                                </span>
                            </td>
                            <td style="text-align:right">
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="type" value="payment">
                                    <input type="hidden" name="code" value="<?= $ext['code'] ?>">
                                    <input type="hidden" name="status" value="<?= $ext['status'] ? 0 : 1 ?>">
                                    <button type="submit"
                                        class="admin-btn admin-btn-sm <?= $ext['status'] ? 'admin-btn-secondary' : 'admin-btn-primary' ?>"
                                        title="<?= $ext['status'] ? 'Durdur' : 'Başlat' ?>">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                <a href="#" class="admin-btn admin-btn-sm admin-btn-secondary" title="Ayarlar"><i
                                        class="fas fa-cog"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODÜLLER -->
    <div class="admin-card" style="padding:0">
        <div class="card-header"
            style="padding:15px; border-bottom:1px solid #eee; background:#f8f9fa; font-weight:600">
            <i class="fas fa-cubes"></i> Modüller
        </div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Modül Adı</th>
                        <th style="width:100px">Durum</th>
                        <th style="width:80px; text-align:right">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($diger_moduller)): ?>
                        <tr>
                            <td colspan="3" style="text-align:center; padding:30px; color:var(--admin-gray)">
                                <i class="fas fa-info-circle"></i> Henüz modül tanımlanmamış.
                                <br><small>Anasayfayı ziyaret ederek otomatik migrasyon çalıştırabilirsiniz.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($diger_moduller as $ext): ?>
                        <tr>
                            <td><strong>
                                    <?= temiz(ucfirst($ext['code'])) ?>
                                </strong></td>
                            <td>
                                <span class="badge badge-<?= $ext['status'] ? 'success' : 'secondary' ?>">
                                    <?= $ext['status'] ? 'Aktif' : 'Pasif' ?>
                                </span>
                            </td>
                            <td style="text-align:right">
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="type" value="module">
                                    <input type="hidden" name="code" value="<?= $ext['code'] ?>">
                                    <input type="hidden" name="status" value="<?= $ext['status'] ? 0 : 1 ?>">
                                    <button type="submit"
                                        class="admin-btn admin-btn-sm <?= $ext['status'] ? 'admin-btn-secondary' : 'admin-btn-primary' ?>"
                                        title="<?= $ext['status'] ? 'Durdur' : 'Başlat' ?>">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                <a href="#" class="admin-btn admin-btn-sm admin-btn-secondary" title="Ayarlar"><i
                                        class="fas fa-cog"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>