<?php
/**
 * Admin — Baskı Dosyası Kuyruğu
 * Müşterilerin yüklediği dosyaları ve tasarım taleplerini yönet.
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

// POST: Durum güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_print_status') {
        $allowed = ['pending', 'approved', 'rejected', 'used'];
        $status = in_array($_POST['status'], $allowed) ? $_POST['status'] : null;
        if ($status) {
            Database::query(
                "UPDATE print_files SET status=?, admin_notes=?, reviewed_at=NOW() WHERE id=?",
                [$status, trim($_POST['admin_notes'] ?? ''), intval($_POST['file_id'])]
            );
            flash('print_queue', 'Dosya durumu güncellendi.', 'success');
        }
    }

    if ($action === 'update_design_status') {
        $allowed = ['new', 'in_progress', 'completed', 'cancelled'];
        $status = in_array($_POST['status'], $allowed) ? $_POST['status'] : null;
        if ($status) {
            Database::query(
                "UPDATE design_requests SET status=?, admin_notes=? WHERE id=?",
                [$status, trim($_POST['admin_notes'] ?? ''), intval($_POST['request_id'])]
            );
            flash('print_queue', 'Tasarım talebi güncellendi.', 'success');
        }
    }

    redirect('/admin/print-queue.php');
}

// Filtre
$statusFilter = $_GET['status'] ?? '';
$tab = $_GET['tab'] ?? 'files';

// Baskı dosyaları
$fileParams = $statusFilter ? [$statusFilter] : [];
$fileWhere  = $statusFilter ? 'WHERE pf.status = ?' : '';
$printFiles = Database::fetchAll(
    "SELECT pf.*, u.first_name, u.last_name, u.email, o.order_number
     FROM print_files pf
     LEFT JOIN users u ON u.id = pf.user_id
     LEFT JOIN orders o ON o.id = pf.order_id
     $fileWhere
     ORDER BY FIELD(pf.status,'pending','approved','used','rejected'), pf.uploaded_at DESC
     LIMIT 100",
    $fileParams
);

// Tasarım talepleri
$drParams = $statusFilter ? [$statusFilter] : [];
$drWhere  = $statusFilter ? 'WHERE dr.status = ?' : '';
$designRequests = Database::fetchAll(
    "SELECT dr.*, u.first_name, u.last_name, u.email, p.name as product_name
     FROM design_requests dr
     LEFT JOIN users u ON u.id = dr.user_id
     LEFT JOIN products p ON p.id = dr.product_id
     $drWhere
     ORDER BY FIELD(dr.status,'new','in_progress','completed','cancelled'), dr.created_at DESC
     LIMIT 100",
    $drParams
);

$pendingCount = Database::fetch(
    "SELECT COUNT(*) as c FROM print_files WHERE status = 'pending'"
)['c'] ?? 0;
$newDrCount = Database::fetch(
    "SELECT COUNT(*) as c FROM design_requests WHERE status = 'new'"
)['c'] ?? 0;

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1><i class="fas fa-print" style="color:var(--admin-primary)"></i> Baskı Kuyruğu</h1>
        <small style="color:var(--admin-gray)">Yüklenen dosyalar ve tasarım talepleri</small>
    </div>
    <div style="display:flex;gap:10px">
        <select onchange="location='?tab=<?= e($tab) ?>&status='+this.value" class="form-control" style="width:160px">
            <option value="">Tüm Durumlar</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>İnceleniyor</option>
            <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Onaylandı</option>
            <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Reddedildi</option>
        </select>
    </div>
</div>

<?php showFlash('print_queue'); ?>

<!-- Tab Navigation -->
<div style="display:flex;gap:4px;margin-bottom:20px">
    <a href="?tab=files" class="admin-btn <?= $tab === 'files' ? 'admin-btn-primary' : 'admin-btn-outline' ?>">
        <i class="fas fa-file"></i> Dosyalar
        <?php if ($pendingCount): ?><span class="admin-badge admin-badge-danger" style="margin-left:6px">
                <?= $pendingCount ?>
            </span>
        <?php endif; ?>
    </a>
    <a href="?tab=requests" class="admin-btn <?= $tab === 'requests' ? 'admin-btn-primary' : 'admin-btn-outline' ?>">
        <i class="fas fa-paint-brush"></i> Tasarım Talepleri
        <?php if ($newDrCount): ?><span class="admin-badge admin-badge-danger" style="margin-left:6px">
                <?= $newDrCount ?>
            </span>
        <?php endif; ?>
    </a>
</div>

<?php if ($tab === 'files'): ?>
    <!-- ===== DOSYALAR ===== -->
    <?php
    $statusColors = ['pending' => '#f59e0b', 'approved' => '#22c55e', 'rejected' => '#ef4444', 'used' => '#6b7280'];
    $statusLabels = ['pending' => 'İnceleniyor', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', 'used' => 'Kullanıldı'];
    ?>
    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Müşteri</th>
                    <th>Dosya</th>
                    <th>Boyut</th>
                    <th>Sipariş</th>
                    <th>Durum</th>
                    <th>Yüklenme</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($printFiles as $f): ?>
                    <?php $sizeMb = round($f['file_size'] / 1048576, 2); ?>
                    <tr>
                        <td>
                            <?= $f['id'] ?>
                        </td>
                        <td>
                            <strong>
                                <?= e($f['first_name'] . ' ' . $f['last_name']) ?>
                            </strong><br>
                            <small style="color:var(--admin-gray)">
                                <?= e($f['email']) ?>
                            </small>
                        </td>
                        <td style="font-size:.85rem">
                            <i class="fas fa-file-alt" style="color:var(--admin-primary)"></i>
                            <?= e($f['original_name']) ?>
                            <br><small style="color:var(--admin-gray)">
                                <?= $f['mime_type'] ?>
                            </small>
                        </td>
                        <td>
                            <?= $sizeMb ?>MB
                        </td>
                        <td>
                            <?= $f['order_number'] ? '#' . $f['order_number'] : '-' ?>
                        </td>
                        <td>
                            <span
                                style="background:<?= $statusColors[$f['status']] ?>22;color:<?= $statusColors[$f['status']] ?>;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:600">
                                <?= $statusLabels[$f['status']] ?>
                            </span>
                        </td>
                        <td style="font-size:.82rem">
                            <?= date('d.m.Y H:i', strtotime($f['uploaded_at'])) ?>
                        </td>
                        <td>
                            <button onclick="openFileModal(<?= htmlspecialchars(json_encode($f), ENT_QUOTES) ?>)"
                                class="admin-btn admin-btn-primary admin-btn-sm">
                                <i class="fas fa-edit"></i> İncele
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($printFiles)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;color:var(--admin-gray);padding:32px">Dosya yok.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($tab === 'requests'): ?>
    <!-- ===== TASARIM TALEPLERİ ===== -->
    <?php
    $drColors = ['new' => '#3b82f6', 'in_progress' => '#f59e0b', 'completed' => '#22c55e', 'cancelled' => '#6b7280'];
    $drLabels = ['new' => 'Yeni', 'in_progress' => 'İşlemde', 'completed' => 'Tamamlandı', 'cancelled' => 'İptal'];
    ?>
    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Müşteri</th>
                    <th>Açıklama</th>
                    <th>Ürün</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($designRequests as $dr): ?>
                    <tr>
                        <td>
                            <?= $dr['id'] ?>
                        </td>
                        <td>
                            <strong>
                                <?= e($dr['first_name'] . ' ' . $dr['last_name']) ?>
                            </strong><br>
                            <small style="color:var(--admin-gray)">
                                <?= e($dr['email']) ?>
                            </small>
                        </td>
                        <td style="max-width:260px;font-size:.85rem">
                            <?= e(truncate($dr['description'], 100)) ?>
                        </td>
                        <td style="font-size:.85rem">
                            <?= e($dr['product_name'] ?? '-') ?>
                        </td>
                        <td>
                            <span
                                style="background:<?= $drColors[$dr['status']] ?>22;color:<?= $drColors[$dr['status']] ?>;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:600">
                                <?= $drLabels[$dr['status']] ?>
                            </span>
                        </td>
                        <td style="font-size:.82rem">
                            <?= date('d.m.Y H:i', strtotime($dr['created_at'])) ?>
                        </td>
                        <td>
                            <button onclick="openDrModal(<?= htmlspecialchars(json_encode($dr), ENT_QUOTES) ?>)"
                                class="admin-btn admin-btn-primary admin-btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($designRequests)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;color:var(--admin-gray);padding:32px">Talep yok.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Dosya Durum Modalı -->
<div id="fileModal" class="admin-modal-bg" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3><i class="fas fa-file"></i> Dosya İncele</h3>
            <button class="admin-modal-close"
                onclick="document.getElementById('fileModal').classList.remove('active')">&times;</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_print_status">
            <input type="hidden" name="file_id" id="fmFileId">
            <div class="admin-modal-body">
                <p id="fmDetails" style="font-size:.9rem;color:var(--admin-gray);margin-bottom:12px"></p>
                <div class="form-group">
                    <label>Durum</label>
                    <select name="status" id="fmStatus" class="form-control">
                        <option value="pending">İnceleniyor</option>
                        <option value="approved">Onayla</option>
                        <option value="rejected">Reddet</option>
                        <option value="used">Kullanıldı</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Admin Notu (müşteri görmez)</label>
                    <textarea name="admin_notes" id="fmNotes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="submit" class="admin-btn admin-btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Tasarım Talebi Modalı -->
<div id="drModal" class="admin-modal-bg" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="admin-modal" style="max-width:560px">
        <div class="admin-modal-header">
            <h3><i class="fas fa-paint-brush"></i> Tasarım Talebi</h3>
            <button class="admin-modal-close"
                onclick="document.getElementById('drModal').classList.remove('active')">&times;</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_design_status">
            <input type="hidden" name="request_id" id="drId">
            <div class="admin-modal-body">
                <div style="padding:12px;background:var(--admin-bg);border-radius:8px;margin-bottom:12px;font-size:.9rem"
                    id="drDesc"></div>
                <div class="form-group">
                    <label>Durum</label>
                    <select name="status" id="drStatus" class="form-control">
                        <option value="new">Yeni</option>
                        <option value="in_progress">İşlemde</option>
                        <option value="completed">Tamamlandı</option>
                        <option value="cancelled">İptal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Admin Notu</label>
                    <textarea name="admin_notes" id="drNotes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="submit" class="admin-btn admin-btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openFileModal(f) {
        document.getElementById('fmFileId').value = f.id;
        document.getElementById('fmStatus').value = f.status;
        document.getElementById('fmNotes').value = f.admin_notes || '';
        document.getElementById('fmDetails').innerHTML =
            `<strong>${f.original_name}</strong> · ${(f.file_size / 1048576).toFixed(2)}MB · ${f.mime_type}
         <br>Müşteri: ${f.first_name} ${f.last_name} (${f.email})`;
        document.getElementById('fileModal').classList.add('active');
    }

    function openDrModal(dr) {
        document.getElementById('drId').value = dr.id;
        document.getElementById('drStatus').value = dr.status;
        document.getElementById('drNotes').value = dr.admin_notes || '';
        document.getElementById('drDesc').innerHTML =
            `<strong>Açıklama:</strong> ${dr.description}` +
            (dr.reference_url ? `<br><a href="${dr.reference_url}" target="_blank">Referans</a>` : '');
        document.getElementById('drModal').classList.add('active');
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
