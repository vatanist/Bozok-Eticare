<?php
$pageTitle = 'Seçenek Düzenle';
$adminPage = 'options';
require_once __DIR__ . '/includes/header.php';

$secenekId = intval($_GET['id'] ?? 0);
$secenek = null;
$secenekDegerleri = [];

if ($secenekId) {
    $secenek = Database::fetch("SELECT * FROM options WHERE id = ?", [$secenekId]);
    if (!$secenek) {
        mesaj('secenekler', 'Seçenek bulunamadı.', 'error');
        git('/admin/secenekler.php');
    }
    $secenekDegerleri = Database::fetchAll("SELECT * FROM option_values WHERE option_id = ? ORDER BY sort_order, name", [$secenekId]);
}

// Kaydetme İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['name']);
    $tip = $_POST['type'] ?? 'select';
    $sira = intval($_POST['sort_order'] ?? 0);

    if ($secenekId) {
        Database::query("UPDATE options SET name = ?, type = ?, sort_order = ? WHERE id = ?", [$ad, $tip, $sira, $secenekId]);
    } else {
        Database::query("INSERT INTO options (name, type, sort_order) VALUES (?, ?, ?)", [$ad, $tip, $sira]);
        $secenekId = Database::getInstance()->getConnection()->lastInsertId();
    }

    // Değerleri güncelle
    Database::query("DELETE FROM option_values WHERE option_id = ?", [$secenekId]);

    if (!empty($_POST['option_value'])) {
        foreach ($_POST['option_value'] as $val) {
            if (trim($val['name'])) {
                Database::query("INSERT INTO option_values (option_id, name, sort_order) VALUES (?, ?, ?)", [
                    $secenekId,
                    trim($val['name']),
                    intval($val['sort_order'])
                ]);
            }
        }
    }

    mesaj('secenekler', 'Seçenek başarıyla kaydedildi.', 'success');
    git('/admin/secenekler.php');
}
?>

<div class="admin-header">
    <h1>
        <i class="fas fa-layer-group" style="color:var(--admin-primary)"></i>
        <?= $secenekId ? 'Seçeneği Düzenle' : 'Yeni Seçenek' ?>
    </h1>
    <div class="admin-header-actions">
        <button form="optionForm" type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save"></i>
            Kaydet</button>
        <a href="secenekler.php" class="admin-btn admin-btn-secondary"><i class="fas fa-times"></i> İptal</a>
    </div>
</div>

<form id="optionForm" method="POST">
    <div class="admin-card mb-4">
        <div class="form-row">
            <div class="form-group" style="flex:2">
                <label>Seçenek Adı (Örn: Renk, Beden) *</label>
                <input type="text" name="name" class="form-control" required value="<?= temiz($secenek['name'] ?? '') ?>">
            </div>
            <div class="form-group" style="flex:1">
                <label>Tip</label>
                <select name="type" class="form-control">
                    <option value="select" <?= ($secenek['type'] ?? '') == 'select' ? 'selected' : '' ?>>Açılır Liste (Select)</option>
                    <option value="radio" <?= ($secenek['type'] ?? '') == 'radio' ? 'selected' : '' ?>>Tekli Seçim (Radio)</option>
                    <option value="checkbox" <?= ($secenek['type'] ?? '') == 'checkbox' ? 'selected' : '' ?>>Çoklu Seçim (Checkbox)</option>
                </select>
            </div>
            <div class="form-group" style="flex:0.5">
                <label>Sıralama</label>
                <input type="number" name="sort_order" class="form-control" value="<?= $secenek['sort_order'] ?? '0' ?>">
            </div>
        </div>
    </div>

    <div class="admin-card">
        <h3>Seçenek Değerleri</h3>
        <div class="admin-table-wrapper">
            <table class="admin-table" id="optionValuesTable">
                <thead>
                    <tr>
                        <th>Değer Adı (Örn: Mavi, XL) *</th>
                        <th style="width:150px">Sıralama</th>
                        <th style="width:100px; text-align:right">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $satirIndex = 0;
                    foreach ($secenekDegerleri as $sd): ?>
                        <tr id="row-<?= $satirIndex ?>">
                            <td><input type="text" name="option_value[<?= $satirIndex ?>][name]" class="form-control" required
                                    value="<?= temiz($sd['name']) ?>"></td>
                            <td><input type="number" name="option_value[<?= $satirIndex ?>][sort_order]" class="form-control"
                                    value="<?= $sd['sort_order'] ?>"></td>
                            <td style="text-align:right">
                                <button type="button" onclick="document.getElementById('row-<?= $satirIndex ?>').remove()"
                                    class="admin-btn admin-btn-sm admin-btn-danger"><i
                                        class="fas fa-minus-circle"></i></button>
                            </td>
                        </tr>
                        <?php
                        $satirIndex++;
                    endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"></td>
                        <td style="text-align:right">
                            <button type="button" onclick="secenekDegeriEkle()"
                                class="admin-btn admin-btn-sm admin-btn-primary" title="Yeni Değer Ekle"><i
                                    class="fas fa-plus-circle"></i></button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</form>

<script>
    let satirIndex = <?= $satirIndex ?>;

    function secenekDegeriEkle() {
        const table = document.getElementById('optionValuesTable').getElementsByTagName('tbody')[0];
        const row = table.insertRow();
        row.id = 'row-' + satirIndex;

        row.innerHTML = `
        <td><input type="text" name="option_value[${satirIndex}][name]" class="form-control" required placeholder="Değer adı..."></td>
        <td><input type="number" name="option_value[${satirIndex}][sort_order]" class="form-control" value="${satirIndex}"></td>
        <td style="text-align:right">
            <button type="button" onclick="document.getElementById('row-${satirIndex}').remove()" class="admin-btn admin-btn-sm admin-btn-danger"><i class="fas fa-minus-circle"></i></button>
        </td>
    `;
        satirIndex++;
    }

    // Eğer hiç değer yoksa otomatik bir tane ekle
    if (satirIndex === 0) {
        secenekDegeriEkle();
    }
</script>

<style>
    .form-row {
        display: flex;
        gap: 20px;
    }

    .form-group {
        flex: 1;
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
