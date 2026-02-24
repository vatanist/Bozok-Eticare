<?php
$pageTitle = 'Ürün Seçenekleri';
$adminPage = 'options';
require_once __DIR__ . '/includes/header.php';

// Silme işlemi
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    Database::query("DELETE FROM options WHERE id = ?", [$id]);
    mesaj('secenekler', 'Seçenek silindi.', 'success');
    git('/admin/secenekler.php');
}

$secenekler = Database::fetchAll("SELECT * FROM options ORDER BY sort_order, name");
?>

<div class="admin-header">
    <h1><i class="fas fa-layer-group" style="color:var(--admin-primary)"></i> Ürün Seçenekleri</h1>
    <a href="secenek-formu.php" class="admin-btn admin-btn-primary"><i class="fas fa-plus"></i> Yeni Seçenek</a>
</div>

<?php mesaj_goster('secenekler'); ?>

<div class="admin-card" style="padding:0">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Seçenek Adı</th>
                    <th>Tip</th>
                    <th style="width:100px">Sıralama</th>
                    <th style="width:120px; text-align:right">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($secenekler)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:40px; color:var(--admin-gray)">Henüz seçenek
                            tanımlanmamış.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($secenekler as $s): ?>
                    <tr>
                        <td><strong>
                                <?= temiz($s['name']) ?>
                            </strong></td>
                        <td><span class="badge badge-info">
                                <?= temiz($s['type']) ?>
                            </span></td>
                        <td>
                            <?= $s['sort_order'] ?>
                        </td>
                        <td style="text-align:right">
                            <a href="secenek-formu.php?id=<?= $s['id'] ?>"
                                class="admin-btn admin-btn-sm admin-btn-secondary"><i class="fas fa-edit"></i></a>
                            <form method="POST" style="display:inline"
                                onsubmit="return confirm('Bu seçeneği ve buna bağlı tüm değerleri silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger"><i
                                        class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
