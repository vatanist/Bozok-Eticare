<?php
/**
 * Admin — Sipariş Yönetimi
 * N+1 fix: order_items tek sorguda GROUP_CONCAT ile çekilir.
 */
$pageTitle = 'Sipariş Yönetimi';
$adminPage = 'orders';
require_once __DIR__ . '/includes/header.php';

$statuses = [
    'pending' => 'Beklemede',
    'processing' => 'İşleniyor',
    'shipped' => 'Kargoda',
    'delivered' => 'Teslim Edildi',
    'cancelled' => 'İptal',
];
$statusBadge = [
    'pending' => 'yellow',
    'processing' => 'blue',
    'shipped' => 'purple',
    'delivered' => 'green',
    'cancelled' => 'red',
];

// Durum güncelleme (whitelist korumalı)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    dogrula_csrf();
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['new_status'] ?? '';

    if (Order::updateStatus($orderId, $newStatus, 'Sipariş listesinden hızlı güncelleme', true)) {
        mesaj('siparisler', 'Sipariş durumu güncellendi.', 'basari');
    }
    git('/admin/siparisler.php');
}

$filterStatus = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$where = '1=1';
$params = [];

if ($filterStatus && array_key_exists($filterStatus, $statuses)) {
    $where .= ' AND o.status = ?';
    $params[] = $filterStatus;
}
if ($search) {
    $where .= ' AND (o.order_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)';
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

// ── N+1 FIX: tek sorguda ürün özetini çek (GROUP_CONCAT) ────────
$orders = Database::fetchAll(
    "SELECT
        o.*,
        u.first_name, u.last_name, u.email,
        GROUP_CONCAT(oi.product_name, ' x', oi.quantity ORDER BY oi.id SEPARATOR '\n') AS items_summary
     FROM orders o
     LEFT JOIN users u  ON u.id  = o.user_id
     LEFT JOIN order_items oi ON oi.order_id = o.id
     WHERE $where
     GROUP BY o.id
     ORDER BY o.created_at DESC
     LIMIT 200",
    $params
);

// Filtre sayaçları (tek sorgu ile)
$statusCounts = [];
foreach (Database::fetchAll("SELECT status, COUNT(*) AS c FROM orders GROUP BY status") as $row) {
    $statusCounts[$row['status']] = $row['c'];
}
?>

<div class="admin-header">
    <div>
        <h1><i class="fas fa-shopping-bag" style="color:var(--admin-primary)"></i> Sipariş Yönetimi</h1>
        <small style="color:var(--admin-gray)">Toplam <?= array_sum($statusCounts) ?> sipariş</small>
    </div>
</div>

<?php mesaj_goster('siparisler'); ?>

<div class="admin-toolbar">
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="?status="
            class="admin-btn <?= !$filterStatus ? 'admin-btn-primary' : 'admin-btn-outline' ?> admin-btn-sm">
            Tümü (<?= array_sum($statusCounts) ?>)
        </a>
        <?php foreach ($statuses as $k => $v): ?>
            <a href="?status=<?= $k ?>"
                class="admin-btn <?= $filterStatus == $k ? 'admin-btn-primary' : 'admin-btn-outline' ?> admin-btn-sm">
                <?= $v ?> (<?= $statusCounts[$k] ?? 0 ?>)
            </a>
        <?php endforeach; ?>
    </div>
    <div class="admin-search"><i class="fas fa-search"></i>
        <form method="GET">
            <input type="text" name="q" placeholder="Sipariş no, müşteri, e-posta..." value="<?= temiz($search) ?>">
            <?php if ($filterStatus): ?><input type="hidden" name="status"
                    value="<?= temiz($filterStatus) ?>"><?php endif; ?>
        </form>
    </div>
</div>

<div class="admin-card" style="padding:0">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Sipariş No</th>
                    <th>Müşteri</th>
                    <th>Ürünler</th>
                    <th>Tutar</th>
                    <th>Ödeme</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:32px;color:var(--admin-gray)">
                            Sipariş bulunamadı.
                        </td>
                    </tr>
                <?php else:
                    foreach ($orders as $o): ?>
                        <tr>
                            <td><strong><?= temiz($o['order_number']) ?></strong></td>
                            <td>
                                <?= temiz($o['first_name'] . ' ' . $o['last_name']) ?>
                                <br><span style="font-size:.75rem;color:var(--admin-gray)"><?= temiz($o['email']) ?></span>
                            </td>
                            <td>
                                <?php foreach (explode("\n", $o['items_summary'] ?? '') as $line): ?>
                                    <div style="font-size:.8rem"><?= temiz(kirp($line, 40)) ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td><strong><?= para_yaz($o['total']) ?></strong></td>
                            <td><span style="font-size:.8rem">
                                    <?php
                                    $pm = ['kapida_odeme' => 'Kapıda', 'havale' => 'Havale', 'kredi_karti' => 'Kart'];
                                    echo temiz($pm[$o['payment_method']] ?? $o['payment_method']);
                                    ?>
                                </span></td>
                            <td>
                                <?= Order::getStatusBadge($o['status']) ?>
                            </td>
                            <td style="font-size:.82rem"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
                                    <!-- Detay butonu -->
                                    <a href="siparis-detayi.php?id=<?= $o['id'] ?>"
                                        class="admin-btn admin-btn-outline admin-btn-sm" title="Sipariş Detayı">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <!-- Hızlı durum değiştir -->
                                    <form method="POST">
                                        <?= csrf_kod() ?>
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="new_status" class="form-control"
                                            style="padding:4px 8px;font-size:.75rem;width:auto" onchange="this.form.submit()">
                                            <?php foreach ($statuses as $sk => $sv): ?>
                                                <option value="<?= $sk ?>" <?= $o['status'] == $sk ? 'selected' : '' ?>>
                                                    <?= $sv ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>