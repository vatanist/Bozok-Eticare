<?php
/**
 * Admin — Ürün Varyasyon Yönetimi
 * Bir ürüne varyasyon tipleri, seçenekleri ve miktar bazlı fiyatlandırma ekler.
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$productId = intval($_GET['product_id'] ?? 0);
if (!$productId)
    redirect('/admin/products.php');

$product = Database::fetch("SELECT id, name, price FROM products WHERE id = ?", [$productId]);
if (!$product)
    redirect('/admin/products.php');

// ── POST İŞLEMLERİ ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    // Varyasyon tipi ekle
    if ($action === 'add_type') {
        Database::query(
            "INSERT INTO product_variation_types (product_id, name, display_name, sort_order) VALUES (?,?,?,?)",
            [$productId, trim($_POST['name']), trim($_POST['display_name']), intval($_POST['sort_order'] ?? 0)]
        );
        flash('variations', 'Varyasyon tipi eklendi.', 'success');
    }

    // Varyasyon tipi sil
    elseif ($action === 'delete_type') {
        $typeId = intval($_POST['type_id']);
        Database::query("DELETE FROM product_variation_options WHERE type_id = ?", [$typeId]);
        Database::query("DELETE FROM product_variation_types WHERE id = ? AND product_id = ?", [$typeId, $productId]);
        flash('variations', 'Varyasyon tipi silindi.', 'success');
    }

    // Seçenek ekle
    elseif ($action === 'add_option') {
        $typeId = intval($_POST['type_id']);
        $mod = floatval($_POST['price_modifier'] ?? 0);
        $modType = in_array($_POST['modifier_type'], ['fixed', 'percent']) ? $_POST['modifier_type'] : 'fixed';
        Database::query(
            "INSERT INTO product_variation_options (type_id, value, price_modifier, modifier_type, sort_order, is_default) VALUES (?,?,?,?,?,?)",
            [$typeId, trim($_POST['value']), $mod, $modType, intval($_POST['sort_order'] ?? 0), isset($_POST['is_default']) ? 1 : 0]
        );
        flash('variations', 'Seçenek eklendi.', 'success');
    }

    // Seçenek sil
    elseif ($action === 'delete_option') {
        Database::query(
            "DELETE FROM product_variation_options WHERE id = ? AND type_id IN (SELECT id FROM product_variation_types WHERE product_id = ?)",
            [intval($_POST['option_id']), $productId]
        );
        flash('variations', 'Seçenek silindi.', 'success');
    }

    // Miktar fiyatı ekle
    elseif ($action === 'add_qty_price') {
        Database::query(
            "INSERT INTO product_quantity_pricing (product_id, min_qty, max_qty, unit_price, label, sort_order) VALUES (?,?,?,?,?,?)",
            [
                $productId,
                intval($_POST['min_qty']),
                !empty($_POST['max_qty']) ? intval($_POST['max_qty']) : null,
                floatval($_POST['unit_price']),
                trim($_POST['label'] ?? ''),
                intval($_POST['sort_order'] ?? 0)
            ]
        );
        flash('variations', 'Miktar fiyatı eklendi.', 'success');
    }

    // Miktar fiyatı sil
    elseif ($action === 'delete_qty_price') {
        Database::query(
            "DELETE FROM product_quantity_pricing WHERE id = ? AND product_id = ?",
            [intval($_POST['qty_price_id']), $productId]
        );
        flash('variations', 'Miktar fiyatı silindi.', 'success');
    }

    redirect('/admin/variations.php?product_id=' . $productId);
}

// ── VERİ ÇEK ──────────────────────────────────────────────────
$types = Database::fetchAll(
    "SELECT * FROM product_variation_types WHERE product_id = ? ORDER BY sort_order, id",
    [$productId]
);
foreach ($types as &$type) {
    $type['options'] = Database::fetchAll(
        "SELECT * FROM product_variation_options WHERE type_id = ? ORDER BY sort_order, id",
        [$type['id']]
    );
}
unset($type);

$qtyPrices = Database::fetchAll(
    "SELECT * FROM product_quantity_pricing WHERE product_id = ? ORDER BY min_qty",
    [$productId]
);

$products = Database::fetchAll("SELECT id, name FROM products WHERE status = 1 ORDER BY name ASC");

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1><i class="fas fa-sliders" style="color:var(--admin-primary)"></i> Varyasyon Yönetimi</h1>
        <small style="color:var(--admin-gray)">Ürün: <strong>
                <?= e($product['name']) ?>
            </strong> — Taban Fiyat: <strong>
                <?= formatPrice($product['price']) ?>
            </strong></small>
    </div>
    <div style="display:flex;gap:10px">
        <form method="GET" style="display:flex;gap:8px;align-items:center">
            <label style="font-size:.85rem;white-space:nowrap">Ürün Değiştir:</label>
            <select name="product_id" class="form-control" style="width:220px" onchange="this.form.submit()">
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $productId ? 'selected' : '' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <a href="products.php" class="admin-btn admin-btn-outline"><i class="fas fa-arrow-left"></i> Ürünlere Dön</a>
    </div>
</div>

<?php showFlash('variations'); ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

    <!-- SOL: Varyasyon Tipleri & Seçenekleri -->
    <div>
        <!-- Mevcut tipler -->
        <?php foreach ($types as $type): ?>
            <div class="admin-card" style="margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <h3 style="margin:0">
                        <i class="fas fa-tag" style="color:var(--admin-primary)"></i>
                        <?= e($type['display_name']) ?>
                        <small style="color:var(--admin-gray);font-size:.8rem">(
                            <?= e($type['name']) ?>)
                        </small>
                    </h3>
                    <form method="POST" onsubmit="return confirm('Tipi ve tüm seçeneklerini sil?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_type">
                        <input type="hidden" name="type_id" value="<?= $type['id'] ?>">
                        <button class="admin-btn admin-btn-danger admin-btn-sm"><i class="fas fa-trash"></i></button>
                    </form>
                </div>

                <!-- Seçenekler listesi -->
                <div style="margin-bottom:12px">
                    <?php foreach ($type['options'] as $opt): ?>
                        <div
                            style="display:flex;justify-content:space-between;align-items:center;padding:6px 10px;background:var(--admin-bg);border-radius:6px;margin-bottom:4px">
                            <span>
                                <?php if ($opt['is_default']): ?><span class="admin-badge admin-badge-success"
                                        style="font-size:.7rem">Varsayılan</span>
                                <?php endif; ?>
                                <strong>
                                    <?= e($opt['value']) ?>
                                </strong>
                                <?php if ($opt['price_modifier'] != 0): ?>
                                    <span style="color:<?= $opt['price_modifier'] > 0 ? '#22c55e' : '#ef4444' ?>;font-size:.85rem">
                                        <?= $opt['price_modifier'] > 0 ? '+' : '' ?>
                                        <?= $opt['modifier_type'] === 'percent' ? $opt['price_modifier'] . '%' : formatPrice($opt['price_modifier']) ?>
                                    </span>
                                <?php endif; ?>
                            </span>
                            <form method="POST" style="display:inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_option">
                                <input type="hidden" name="option_id" value="<?= $opt['id'] ?>">
                                <button class="admin-btn admin-btn-danger admin-btn-sm"
                                    onclick="return confirm('Seçeneği sil?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($type['options'])): ?>
                        <p style="color:var(--admin-gray);font-size:.85rem;margin:0">Henüz seçenek yok.</p>
                    <?php endif; ?>
                </div>

                <!-- Seçenek ekle formu -->
                <details>
                    <summary style="cursor:pointer;color:var(--admin-primary);font-size:.85rem">
                        <i class="fas fa-plus"></i> Seçenek Ekle
                    </summary>
                    <form method="POST" class="admin-form"
                        style="margin-top:10px;padding:10px;background:var(--admin-bg);border-radius:8px">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="add_option">
                        <input type="hidden" name="type_id" value="<?= $type['id'] ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Değer *</label>
                                <input type="text" name="value" class="form-control" placeholder="Örn: A4, Kuşe 170gr"
                                    required>
                            </div>
                            <div class="form-group">
                                <label>Fiyat Farkı</label>
                                <input type="number" name="price_modifier" class="form-control" value="0" step="0.01"
                                    placeholder="0 = etkisiz">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Fark Tipi</label>
                                <select name="modifier_type" class="form-control">
                                    <option value="fixed">Sabit TL</option>
                                    <option value="percent">Yüzde %</option>
                                </select>
                            </div>
                            <div class="form-group" style="display:flex;align-items:flex-end;gap:10px">
                                <label style="display:flex;align-items:center;gap:6px;font-weight:400">
                                    <input type="checkbox" name="is_default"> Varsayılan
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Ekle</button>
                    </form>
                </details>
            </div>
        <?php endforeach; ?>

        <!-- Yeni Varyasyon Tipi Ekle -->
        <div class="admin-card">
            <h3><i class="fas fa-plus-circle" style="color:var(--admin-primary)"></i> Yeni Varyasyon Tipi</h3>
            <form method="POST" class="admin-form">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_type">
                <div class="form-group">
                    <label>İç İsim (slug benzeri) *</label>
                    <input type="text" name="name" class="form-control" placeholder="Örn: boyut, malzeme, baski_teknigi"
                        required>
                </div>
                <div class="form-group">
                    <label>Gösterilen İsim *</label>
                    <input type="text" name="display_name" class="form-control" placeholder="Örn: Boyut Seçimi, Malzeme"
                        required>
                </div>
                <div class="form-group">
                    <label>Sıra</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                </div>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus"></i> Tip Ekle
                </button>
            </form>
        </div>
    </div>

    <!-- SAĞ: Miktar Bazlı Fiyatlandırma -->
    <div>
        <div class="admin-card">
            <h3><i class="fas fa-layer-group" style="color:var(--admin-primary)"></i> Miktar Bazlı Fiyatlandırma</h3>
            <p style="color:var(--admin-gray);font-size:.85rem">Farklı sipariş adetlerine göre farklı birim fiyat
                tanımlayın. En küçük min_qty'den başlayarak eşleşme yapılır.</p>

            <?php if (!empty($qtyPrices)): ?>
                <table class="admin-table" style="margin-bottom:16px">
                    <thead>
                        <tr>
                            <th>Adet Aralığı</th>
                            <th>Birim Fiyat</th>
                            <th>Etiket</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($qtyPrices as $qp): ?>
                            <tr>
                                <td>
                                    <?= $qp['min_qty'] ?>
                                    <?= $qp['max_qty'] ? '– ' . $qp['max_qty'] : '+' ?>
                                    adet
                                </td>
                                <td><strong>
                                        <?= formatPrice($qp['unit_price']) ?>
                                    </strong></td>
                                <td>
                                    <?= e($qp['label'] ?? '-') ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete_qty_price">
                                        <input type="hidden" name="qty_price_id" value="<?= $qp['id'] ?>">
                                        <button class="admin-btn admin-btn-danger admin-btn-sm"
                                            onclick="return confirm('Silinsin mi?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Yeni ekle -->
            <form method="POST" class="admin-form">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_qty_price">
                <div class="form-row">
                    <div class="form-group">
                        <label>Min Adet *</label>
                        <input type="number" name="min_qty" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>Max Adet <small>(boş=limitsiz)</small></label>
                        <input type="number" name="max_qty" class="form-control" min="1" placeholder="Boş bırak">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Birim Fiyat (TL) *</label>
                        <input type="number" name="unit_price" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Etiket <small>(opsiyonel)</small></label>
                        <input type="text" name="label" class="form-control" placeholder="Örn: 10+ adet fiyatı">
                    </div>
                </div>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus"></i> Fiyat Dilimi Ekle
                </button>
            </form>
        </div>

        <!-- Önizleme: Fiyat Hesaplama Testi -->
        <div class="admin-card">
            <h3><i class="fas fa-calculator" style="color:var(--admin-primary)"></i> Canlı Fiyat Hesaplama Testi</h3>
            <p style="color:var(--admin-gray);font-size:.85rem">Müşterinin göreceği fiyatı simüle et:</p>
            <div class="form-group">
                <label>Adet</label>
                <input type="number" id="calcQty" class="form-control" value="1" min="1">
            </div>
            <div id="calcVariations"></div>
            <div id="calcResult"
                style="margin-top:12px;padding:12px;background:var(--admin-primary);color:#fff;border-radius:8px;display:none">
                <strong>Toplam Fiyat: <span id="calcTotal"></span></strong>
            </div>
            <button onclick="calcPrice()" class="admin-btn admin-btn-primary" style="margin-top:10px">
                <i class="fas fa-calculator"></i> Hesapla
            </button>
        </div>
    </div>
</div>

<script>
    const basePrice = <?= json_encode((float) $product['price']) ?>;
    const qtyPrices = <?= json_encode(array_values($qtyPrices)) ?>;
    const variations = <?= json_encode(array_values($types)) ?>;

    // Varyasyon seçicilerini oluştur
    const container = document.getElementById('calcVariations');
    variations.forEach(t => {
        if (!t.options.length) return;
        const g = document.createElement('div');
        g.className = 'form-group';
        g.innerHTML = `<label>${t.display_name}</label>
        <select class="form-control var-select" data-type-id="${t.id}">
            ${t.options.map(o => `<option value="${o.price_modifier}" data-mod-type="${o.modifier_type}" ${o.is_default == '1' ? 'selected' : ''}>${o.value} ${o.price_modifier != 0 ? (o.price_modifier > 0 ? '+' : '') + (o.modifier_type === 'percent' ? o.price_modifier + '%' : o.price_modifier + ' ₺') : ''}</option>`).join('')}
        </select>`;
        container.appendChild(g);
    });

    function calcPrice() {
        const qty = parseInt(document.getElementById('calcQty').value) || 1;

        // Miktar bazlı fiyat
        let unitPrice = basePrice;
        if (qtyPrices.length) {
            const match = qtyPrices.filter(q => qty >= q.min_qty && (!q.max_qty || qty <= q.max_qty));
            if (match.length) unitPrice = parseFloat(match[match.length - 1].unit_price);
        }

        // Varyasyon farkları
        document.querySelectorAll('.var-select').forEach(sel => {
            const mod = parseFloat(sel.value) || 0;
            const type = sel.options[sel.selectedIndex].dataset.modType;
            if (type === 'percent') {
                unitPrice += unitPrice * (mod / 100);
            } else {
                unitPrice += mod;
            }
        });

        const total = unitPrice * qty;
        document.getElementById('calcResult').style.display = 'block';
        document.getElementById('calcTotal').textContent =
            new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(total);
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
