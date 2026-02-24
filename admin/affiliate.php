<?php
/**
 * Admin — Affiliate (Satış Ortaklığı) Yönetimi
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Affiliate Yönetimi';
$adminPage = 'marketing';

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dogrula_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_affiliate') {
        $userId = intval($_POST['user_id']);
        $refCode = trim($_POST['ref_code']);
        $rate = floatval($_POST['commission_rate']);

        Database::query(
            "INSERT INTO affiliates (user_id, ref_code, commission_rate, status) VALUES (?, ?, ?, 'active')",
            [$userId, $refCode, $rate]
        );
        mesaj('affiliate', 'Yeni satış ortağı başarıyla eklendi.', 'basari');
    } elseif ($action === 'approve_commission') {
        $refId = intval($_POST['ref_id']);
        if (Affiliate::approveCommission($refId)) {
            mesaj('affiliate', 'Komisyon onaylandı ve bakiyeye yansıtıldı.', 'basari');
        }
    }
    git('/admin/affiliate.php');
}

$affiliates = Database::fetchAll("
    SELECT a.*, u.first_name, u.last_name, u.email 
    FROM affiliates a
    JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC
");

$referrals = Database::fetchAll("
    SELECT r.*, o.order_number, u.first_name, u.last_name
    FROM affiliate_referrals r
    JOIN orders o ON o.id = r.order_id
    JOIN affiliates a ON a.id = r.affiliate_id
    JOIN users u ON u.id = a.user_id
    ORDER BY r.created_at DESC LIMIT 20
");

$users = Database::fetchAll("SELECT id, first_name, last_name, email FROM users WHERE role != 'admin' ORDER BY first_name ASC");

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <h1><i class="fas fa-handshake" style="color:var(--admin-primary)"></i> Affiliate Yönetimi</h1>
    <button class="admin-btn admin-btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
        <i class="fas fa-plus"></i> Yeni Ortak Ekle
    </button>
</div>

<?php mesaj_goster('affiliate'); ?>

<div style="display:grid;grid-template-columns:1fr 400px;gap:20px;align-items:start">

    <!-- Aktif Ortaklar -->
    <div class="admin-card" style="padding:0">
        <div class="admin-card-header" style="padding:20px">
            <h3 style="margin:0"><i class="fas fa-users"></i> Satış Ortakları</h3>
        </div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ortak</th>
                        <th>Ref Kodu</th>
                        <th>Oran</th>
                        <th>Bakiye</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($affiliates as $a): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?= e($a['first_name'] . ' ' . $a['last_name']) ?>
                                </strong><br>
                                <small style="color:var(--admin-gray)">
                                    <?= e($a['email']) ?>
                                </small>
                            </td>
                            <td><code><?= e($a['ref_code']) ?></code></td>
                            <td>%
                                <?= $a['commission_rate'] ?>
                            </td>
                            <td><strong>
                                    <?= para_yaz($a['balance']) ?>
                                </strong></td>
                            <td>
                                <span class="admin-badge admin-badge-<?= $a['status'] == 'active' ? 'green' : 'gray' ?>">
                                    <?= ucfirst($a['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Son Komisyonlar -->
    <div class="admin-card" style="padding:0">
        <div class="admin-card-header" style="padding:20px">
            <h3 style="margin:0"><i class="fas fa-money-bill-wave"></i> Son Kazançlar</h3>
        </div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ortak</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrals as $r): ?>
                        <tr>
                            <td>
                                <small>Sipariş:
                                    <?= $r['order_number'] ?>
                                </small><br>
                                <?= e($r['first_name']) ?>
                            </td>
                            <td>
                                <?= para_yaz($r['commission_amount']) ?>
                            </td>
                            <td>
                                <span
                                    class="admin-badge admin-badge-<?= $r['status'] == 'approved' ? 'green' : ($r['status'] == 'pending' ? 'blue' : 'red') ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($r['status'] == 'pending'): ?>
                                    <form method="POST" style="display:inline">
                                        <?= csrf_kod() ?>
                                        <input type="hidden" name="action" value="approve_commission">
                                        <input type="hidden" name="ref_id" value="<?= $r['id'] ?>">
                                        <button class="admin-btn admin-btn-sm" title="Onayla"><i
                                                class="fas fa-check"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Ekleme Modal -->
<div id="addModal" class="admin-modal"
    style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:9999">
    <div class="admin-card" style="width:400px">
        <h3>Yeni Satış Ortağı Tanımla</h3>
        <form method="POST">
            <?= csrf_kod() ?>
            <input type="hidden" name="action" value="add_affiliate">
            <div class="admin-form-group">
                <label>Kullanıcı Seç</label>
                <select name="user_id" required class="admin-input">
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>">
                            <?= e($u['first_name'] . ' ' . $u['last_name']) ?> (
                            <?= e($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group">
                <label>Referans Kodu</label>
                <input type="text" name="ref_code" class="admin-input" placeholder="örn: EGE20" required>
            </div>
            <div class="admin-form-group">
                <label>Komisyon Oranı (%)</label>
                <input type="number" name="commission_rate" class="admin-input" value="10" step="0.5" required>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="admin-btn admin-btn-primary" style="flex:1">Kaydet</button>
                <button type="button" class="admin-btn admin-btn-outline" style="flex:1"
                    onclick="document.getElementById('addModal').style.display='none'">İptal</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/header.php'; ?>