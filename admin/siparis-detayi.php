<?php
/**
 * Admin — Sipariş Detayı
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    git('/admin/siparisler.php');
}

$statuses = [
    'pending' => 'Beklemede',
    'processing' => 'İşleniyor',
    'shipped' => 'Kargoda',
    'delivered' => 'Teslim Edildi',
    'cancelled' => 'İptal',
];
$statusBadge = [
    'pending' => '#f59e0b',
    'processing' => '#3b82f6',
    'shipped' => '#8b5cf6',
    'delivered' => '#22c55e',
    'cancelled' => '#ef4444',
];

// Durum veya kargo güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dogrula_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $newStatus = $_POST['new_status'] ?? '';
        $note = trim($_POST['status_note'] ?? '');
        $notify = isset($_POST['notify_user']);

        if (Order::updateStatus($id, $newStatus, $note, $notify)) {
            mesaj('siparis_detay', 'Sipariş durumu güncellendi.', 'basari');
        }
    } elseif ($action === 'update_shipping') {
        $carrier = $_POST['shipping_carrier'] ?? '';
        $tracking = trim($_POST['tracking_number'] ?? '');

        if (Order::updateShipping($id, $carrier, $tracking)) {
            // Eğer statü henüz kargoda değilse kargoya çekelim (isteğe bağlı)
            if ($tracking && $order['status'] !== 'shipped') {
                Order::updateStatus($id, 'shipped', 'Kargo bilgileri girildi: ' . $tracking, true);
            }
            mesaj('siparis_detay', 'Kargo bilgileri güncellendi.', 'basari');
        }
    } elseif ($action === 'add_note') {
        $note = trim($_POST['admin_note'] ?? '');
        if ($note) {
            Database::query(
                "UPDATE orders SET notes = CONCAT(IFNULL(notes,''), ?) WHERE id = ?",
                ["\n[Yönetici " . date('d.m.Y H:i') . "]: " . $note, $id]
            );
            mesaj('siparis_detay', 'Not eklendi.', 'basari');
        }
    }
    git('/admin/siparis-detayi.php?id=' . $id);
}

// Sipariş verisi
$order = Database::fetch(
    "SELECT o.*, u.first_name, u.last_name, u.email, u.phone AS user_phone
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     WHERE o.id = ?",
    [$id]
);
if (!$order) {
    git('/admin/siparisler.php');
}

// Ürünler
$items = Database::fetchAll(
    "SELECT oi.*, p.slug AS product_slug
     FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ?
     ORDER BY oi.id",
    [$id]
);

// Havale bilgileri (settings'ten)
$bankaBilgi = [
    'banka_adi' => ayar_getir('bank_name', ''),
    'banka_iban' => ayar_getir('bank_iban', ''),
    'banka_hesap' => ayar_getir('bank_account_holder', ''),
    'banka_sube' => ayar_getir('bank_branch', ''),
];

$odemeEtiketleri = ['kapida_odeme' => 'Kapıda Ödeme', 'havale' => 'Havale / EFT', 'kredi_karti' => 'Kredi Kartı (PayTR)'];

$pageTitle = 'Sipariş #' . temiz($order['order_number']);
$adminPage = 'orders';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="siparisler.php" class="admin-btn admin-btn-outline admin-btn-sm">
            <i class="fas fa-arrow-left"></i> Siparişler
        </a>
        <div>
            <h1 style="margin:0"><i class="fas fa-receipt" style="color:var(--admin-primary)"></i>
                Sipariş #
                <?= temiz($order['order_number']) ?>
            </h1>
            <small style="color:var(--admin-gray)">
                <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
            </small>
        </div>
    </div>
        </div>
    </div>
    <?= Order::getStatusBadge($order['status']) ?>
</div>

<?php mesaj_goster('siparis_detay'); ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

    <!-- Sol Sütun -->
    <div>
        <!-- Ürünler -->
        <div class="admin-card">
            <h3 style="margin-bottom:16px"><i class="fas fa-box" style="color:var(--admin-primary)"></i> Sipariş İçeriği
            </h3>
            <table class="admin-table" style="font-size:.9rem">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th style="text-align:center">Adet</th>
                        <th style="text-align:right">Birim Fiyat</th>
                        <th style="text-align:right">Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item):
                        $ekVeri = !empty($item['dimensions']) ? json_decode($item['dimensions'], true) : null;
                        ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?= temiz($item['product_name']) ?>
                                    </strong>
                                    <?php if ($item['product_slug']): ?>
                                            <a href="<?= BASE_URL ?>/urun-detay.php?slug=<?= temiz($item['product_slug']) ?>"
                                                target="_blank" style="font-size:.75rem;color:var(--admin-primary);margin-left:6px">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                    <?php endif; ?>
                                    <?php if ($ekVeri): ?>
                                            <div
                                                style="margin-top:4px;font-size:.75rem;background:#f0fdf4;
                                        border:1px solid #86efac;border-radius:6px;padding:3px 8px;display:inline-block;color:#166534;">
                                                <i class="fas fa-ruler-combined"></i>
                                                <strong><?= $ekVeri['w'] ?> × <?= $ekVeri['h'] ?> cm</strong>
                                                &nbsp;|&nbsp; <?= number_format($ekVeri['area_m2'], 4) ?> m²
                                                &nbsp;|&nbsp; <?= para_yaz($ekVeri['price_per_m2']) ?> / m²
                                            </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['product_image'])): ?>
                                            <div style="margin-top:4px">
                                                <img src="<?= resim_linki($item['product_image']) ?>"
                                                    style="height:40px;width:40px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0">
                                            </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center">
                                    <?= $item['quantity'] ?>
                                </td>
                                <td style="text-align:right">
                                    <?= para_yaz($item['price']) ?>
                                </td>
                                <td style="text-align:right"><strong>
                                        <?= para_yaz($item['total']) ?>
                                    </strong></td>
                            </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid #e2e8f0">
                        <td colspan="3" style="text-align:right;font-weight:600;padding-top:10px">Ara Toplam</td>
                        <td style="text-align:right;padding-top:10px">
                            <?= para_yaz($order['subtotal']) ?>
                        </td>
                    </tr>
                    <?php if ($order['shipping_cost'] > 0): ?>
                            <tr>
                                <td colspan="3" style="text-align:right">Kargo</td>
                                <td style="text-align:right">
                                    <?= para_yaz($order['shipping_cost']) ?>
                                </td>
                            </tr>
                    <?php endif; ?>
                    <?php if ($order['discount_amount'] > 0): ?>
                            <tr>
                                <td colspan="3" style="text-align:right;color:#22c55e">İndirim</td>
                                <td style="text-align:right;color:#22c55e">-
                                    <?= para_yaz($order['discount_amount']) ?>
                                </td>
                            </tr>
                    <?php endif; ?>
                    <?php if (!empty($order['delivery_fee']) && $order['delivery_fee'] > 0): ?>
                            <tr>
                                <td colspan="3" style="text-align:right">Teslimat Ücreti</td>
                                <td style="text-align:right">
                                    <?= para_yaz($order['delivery_fee']) ?>
                                </td>
                            </tr>
                    <?php endif; ?>
                    <tr style="font-size:1.05rem;font-weight:700;color:var(--admin-primary)">
                        <td colspan="3" style="text-align:right;border-top:2px solid #e2e8f0;padding-top:10px">GENEL
                            TOPLAM</td>
                        <td style="text-align:right;border-top:2px solid #e2e8f0;padding-top:10px">
                            <?= para_yaz($order['total']) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Kargo Adresi -->
        <div class="admin-card" style="margin-top:16px">
            <h3 style="margin-bottom:14px"><i class="fas fa-map-marker-alt" style="color:var(--admin-primary)"></i>
                Teslimat Adresi</h3>
            <div style="line-height:1.8;color:#374151">
                <strong>
                    <?= temiz($order['shipping_first_name'] . ' ' . $order['shipping_last_name']) ?>
                </strong><br>
                <?php if ($order['shipping_phone']): ?>
                        <i class="fas fa-phone" style="color:var(--admin-gray);font-size:.8rem"></i>
                        <?= temiz($order['shipping_phone']) ?><br>
                <?php endif; ?>
                <?= temiz($order['shipping_address']) ?><br>
                <?= temiz(implode(', ', array_filter([$order['shipping_neighborhood'], $order['shipping_district'], $order['shipping_city'], $order['shipping_zip']]))) ?>
            </div>
        </div>

        <!-- Ödeme Yöntemi & Havale Bilgisi -->
        <div class="admin-card" style="margin-top:16px">
            <h3 style="margin-bottom:14px"><i class="fas fa-credit-card" style="color:var(--admin-primary)"></i> Ödeme
                Bilgisi</h3>
            <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
                <div>
                    <label
                        style="font-size:.75rem;color:var(--admin-gray);font-weight:600;text-transform:uppercase">Ödeme
                        Yöntemi</label>
                    <p style="font-weight:600;font-size:.95rem;margin-top:4px">
                        <?= $odemeEtiketleri[$order['payment_method']] ?? temiz($order['payment_method']) ?>
                    </p>
                </div>
                <div>
                    <label
                        style="font-size:.75rem;color:var(--admin-gray);font-weight:600;text-transform:uppercase">Ödeme
                        Durumu</label>
                    <p style="margin-top:4px">
                        <?php
                        $psBadge = ['pending' => '#f59e0b', 'paid' => '#22c55e', 'failed' => '#ef4444', 'refunded' => '#8b5cf6'];
                        $psLabel = ['pending' => 'Bekliyor', 'paid' => 'Ödendi', 'failed' => 'Başarısız', 'refunded' => 'İade'];
                        $ps = $order['payment_status'] ?? 'pending';
                        ?>
                        <span
                            style="background:<?= $psBadge[$ps] ?? '#6b7280' ?>;color:#fff;padding:3px 12px;border-radius:12px;font-size:.82rem;font-weight:600">
                            <?= $psLabel[$ps] ?? $ps ?>
                        </span>
                    </p>
                </div>
            </div>

            <?php if ($order['payment_method'] === 'havale'): ?>
                    <div style="margin-top:16px;padding:16px;background:#eff6ff;border-radius:10px;border:1px solid #bfdbfe">
                        <p style="font-weight:700;color:#1e40af;margin-bottom:10px">
                            <i class="fas fa-university"></i> Havale / EFT Banka Bilgileri
                        </p>
                        <?php if ($bankaBilgi['banka_adi']): ?>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:.88rem">
                                    <div><strong>Banka:</strong>
                                        <?= temiz($bankaBilgi['banka_adi']) ?>
                                    </div>
                                    <div><strong>Hesap Sahibi:</strong>
                                        <?= temiz($bankaBilgi['banka_hesap']) ?>
                                    </div>
                                    <div style="grid-column:1/-1"><strong>IBAN:</strong>
                                        <code
                                            style="background:#dbeafe;padding:4px 8px;border-radius:4px;font-size:.9rem;letter-spacing:1px">
                                                                    <?= temiz($bankaBilgi['banka_iban']) ?>
                                                                </code>
                                    </div>
                                    <?php if ($bankaBilgi['banka_sube']): ?>
                                            <div><strong>Şube:</strong>
                                                <?= temiz($bankaBilgi['banka_sube']) ?>
                                            </div>
                                    <?php endif; ?>
                                </div>
                                <p style="margin-top:10px;font-size:.82rem;color:#1e40af">
                                    <i class="fas fa-info-circle"></i> Açıklama: <strong>
                                        <?= temiz($order['order_number']) ?>
                                    </strong>
                                </p>
                        <?php else: ?>
                                <p style="color:#6b7280;font-size:.85rem">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Banka bilgileri henüz girilmemiş.
                                    <a href="settings.php">Ayarlar'dan ekle →</a>
                                </p>
                        <?php endif; ?>
                    </div>
            <?php endif; ?>
        </div>

        <!-- Notlar -->
        <?php if ($order['notes']): ?>
                <div class="admin-card" style="margin-top:16px">
                    <h3 style="margin-bottom:10px"><i class="fas fa-sticky-note" style="color:var(--admin-primary)"></i> Sipariş Notları</h3>
                    <pre style="white-space:pre-wrap;font-family:inherit;font-size:.88rem;color:#374151;line-height:1.7;margin:0"><?= temiz($order['notes']) ?></pre>
                </div>
        <?php endif; ?>

        <!-- Statü Geçmişi (Timeline) -->
        <div class="admin-card" style="margin-top:16px">
            <h3 style="margin-bottom:16px"><i class="fas fa-history" style="color:var(--admin-primary)"></i> Sipariş Geçmişi</h3>
            <div class="admin-timeline">
                <?php
                $history = Order::getHistory($id);
                if (empty($history)): ?>
                        <p style="color:var(--admin-gray);font-size:.85rem">Henüz bir işlem kaydı bulunmuyor.</p>
                <?php else:
                    foreach ($history as $h): ?>
                            <div class="timeline-item" style="border-left:2px solid #e2e8f0;padding-left:16px;margin-bottom:16px;position:relative">
                                <div style="position:absolute;left:-6px;top:0;width:10px;height:10px;background:#e2e8f0;border-radius:50%"></div>
                                <div style="font-size:.85rem;display:flex;justify-content:space-between;align-items:center">
                                    <strong><?= Order::getStatusBadge($h['status']) ?></strong>
                                    <span style="color:var(--admin-gray);font-size:.75rem"><?= date('d.m.Y H:i', strtotime($h['created_at'])) ?></span>
                                </div>
                                <?php if ($h['note']): ?>
                                        <p style="font-size:.82rem;margin-top:4px;color:#4b5563"><?= e($h['note']) ?></p>
                                <?php endif; ?>
                                <?php if ($h['notify_user']): ?>
                                        <span style="font-size:.7rem;color:#10b981"><i class="fas fa-check-circle"></i> Müşteriye bildirildi</span>
                                <?php endif; ?>
                            </div>
                    <?php endforeach; endif; ?>
            </div>
        </div>
    </div><!-- /Sol -->

    <!-- Sağ Sütun -->
    <div>
        <!-- Müşteri Bilgisi -->
        <div class="admin-card">
            <h3 style="margin-bottom:14px"><i class="fas fa-user" style="color:var(--admin-primary)"></i> Müşteri</h3>
            <div style="line-height:1.9;font-size:.9rem">
                <strong>
                    <?= temiz($order['first_name'] . ' ' . $order['last_name']) ?>
                </strong><br>
                <?php if ($order['email']): ?>
                        <a href="mailto:<?= temiz($order['email']) ?>" style="color:var(--admin-primary)">
                            <i class="fas fa-envelope" style="font-size:.75rem"></i>
                            <?= temiz($order['email']) ?>
                        </a><br>
                <?php endif; ?>
                <?php if ($order['user_phone'] ?? null): ?>
                        <i class="fas fa-phone" style="font-size:.75rem;color:var(--admin-gray)"></i>
                        <?= temiz($order['user_phone']) ?><br>
                <?php endif; ?>
            </div>
        </div>

        <!-- Durum Güncelle -->
        <div class="admin-card" style="margin-top:16px">
            <h3 style="margin-bottom:14px"><i class="fas fa-edit" style="color:var(--admin-primary)"></i> Durum Güncelle</h3>
            <form method="POST">
                <?= csrf_kod() ?>
                <input type="hidden" name="action" value="update_status">
                <div class="form-group" style="margin-bottom:10px">
                    <select name="new_status" class="form-control">
                        <?php foreach ($statuses as $sk => $sv): ?>
                                <option value="<?= $sk ?>" <?= $order['status'] == $sk ? 'selected' : '' ?>>
                                    <?= $sv ?>
                                </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:10px">
                    <textarea name="status_note" class="form-control" rows="2" placeholder="Değişiklik sebebi/notu..."></textarea>
                </div>
                <div class="form-group" style="margin-bottom:10px">
                    <label style="display:flex;align-items:center;gap:8px;font-size:.85rem;cursor:pointer">
                        <input type="checkbox" name="notify_user" value="1"> Müşteriye Bilgi Ver
                    </label>
                </div>
                <button type="submit" class="admin-btn admin-btn-primary" style="width:100%">
                    <i class="fas fa-save"></i> Güncelle
                </button>
            </form>
        </div>

        <!-- Kargo Bilgileri -->
        <div class="admin-card" style="margin-top:16px">
            <h3 style="margin-bottom:14px"><i class="fas fa-truck" style="color:var(--admin-primary)"></i> Kargo Yönetimi</h3>
            <form method="POST">
                <?= csrf_kod() ?>
                <input type="hidden" name="action" value="update_shipping">
                <div class="form-group" style="margin-bottom:10px">
                    <label style="font-size:.75rem;color:var(--admin-gray)">Kargo Firması</label>
                    <select name="shipping_carrier" class="form-control">
                        <option value="">Seçin</option>
                        <?php foreach (Cargo::getCarriers() as $ck => $cv): ?>
                                <option value="<?= $ck ?>" <?= $order['shipping_carrier'] == $ck ? 'selected' : '' ?>>
                                    <?= $cv['name'] ?>
                                </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:10px">
                    <label style="font-size:.75rem;color:var(--admin-gray)">Takip Numarası</label>
                    <input type="text" name="tracking_number" class="form-control" placeholder="Örn: 12345678" value="<?= e($order['tracking_number']) ?>">
                </div>
                <button type="submit" class="admin-btn admin-btn-secondary" style="width:100%">
                    <i class="fas fa-shipping-fast"></i> Kargo Kaydet
                </button>
            </form>
            <?php if ($order['tracking_number']): ?>
                    <div style="margin-top:12px">
                        <a href="<?= Cargo::getTrackingUrl($order['shipping_carrier'], $order['tracking_number']) ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm" style="width:100%;text-align:center">
                            <i class="fas fa-external-link-alt"></i> Kargomu Takip Et
                        </a>
                    </div>
            <?php endif; ?>
        </div>

        <!-- Admin Not Ekle -->
        <div class="admin-card" style="margin-top:16px">
            <h3 style="margin-bottom:14px"><i class="fas fa-comment" style="color:var(--admin-primary)"></i> Not Ekle
            </h3>
            <form method="POST">
                <?= csrf_kod() ?>
                <input type="hidden" name="action" value="add_note">
                <textarea name="admin_note" class="form-control" rows="3" placeholder="Sipariş hakkında not giriniz..."
                    style="margin-bottom:10px"></textarea>
                <button type="submit" class="admin-btn admin-btn-outline" style="width:100%">
                    <i class="fas fa-plus"></i> Not Ekle
                </button>
            </form>
        </div>

        <!-- Yazdır -->
        <div class="admin-card" style="margin-top:16px">
            <button onclick="window.print()" class="admin-btn admin-btn-outline" style="width:100%">
                <i class="fas fa-print"></i> Yazdır / PDF
            </button>
        </div>
    </div><!-- /Sağ -->

</div><!-- /grid -->

<style>
    @media print {

        .admin-sidebar,
        .admin-topbar,
        form,
        .admin-btn {
            display: none !important;
        }

        .admin-card {
            box-shadow: none !important;
            border: 1px solid #e2e8f0 !important;
        }
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
