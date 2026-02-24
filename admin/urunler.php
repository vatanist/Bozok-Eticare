<?php
$pageTitle = 'Ürün Yönetimi';
$adminPage = 'products';
require_once __DIR__ . '/includes/header.php';

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $data = [
            'name' => trim($_POST['name']),
            'slug' => slugify($_POST['name']),
            'product_type' => $_POST['product_type'] ?? 'simple',
            'category_id' => intval($_POST['category_id']),
            'brand' => trim($_POST['brand'] ?? ''),
            'sku' => trim($_POST['sku'] ?? ''),
            'barcode' => trim($_POST['barcode'] ?? ''),
            'desi' => floatval($_POST['desi'] ?? 0),
            'price' => floatval($_POST['price'] ?? 0),
            'discount_price' => floatval($_POST['discount_price'] ?? 0) ?: null,
            'stock' => intval($_POST['stock'] ?? 0),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'status' => isset($_POST['status']) ? 1 : 0,
            // Fiyatlandırma
            'pricing_type' => in_array($_POST['pricing_type'] ?? '', ['fixed', 'per_m2', 'per_piece']) ? $_POST['pricing_type'] : 'fixed',
            'price_per_m2' => floatval($_POST['price_per_m2'] ?? 0) ?: null,
            'min_width_cm' => intval($_POST['min_width_cm'] ?? 0) ?: null,
            'max_width_cm' => intval($_POST['max_width_cm'] ?? 0) ?: null,
            'min_height_cm' => intval($_POST['min_height_cm'] ?? 0) ?: null,
            'max_height_cm' => intval($_POST['max_height_cm'] ?? 0) ?: null,
            'pricing_extra_options' => !empty($_POST['pricing_extra_options']) ? $_POST['pricing_extra_options'] : null,
            // Enterprise Fields
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'meta_keyword' => trim($_POST['meta_keyword'] ?? ''),
            'tags' => trim($_POST['tags'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'upc' => trim($_POST['upc'] ?? ''),
            'ean' => trim($_POST['ean'] ?? ''),
            'jan' => trim($_POST['jan'] ?? ''),
            'isbn' => trim($_POST['isbn'] ?? ''),
            'mpn' => trim($_POST['mpn'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'minimum_qty' => intval($_POST['minimum_qty'] ?? 1),
            'subtract_stock' => intval($_POST['subtract_stock'] ?? 1),
            'out_of_stock_status_id' => intval($_POST['out_of_stock_status_id'] ?? 7),
            'requires_shipping' => intval($_POST['requires_shipping'] ?? 1),
            'date_available' => !empty($_POST['date_available']) ? $_POST['date_available'] : date('Y-m-d'),
            'length' => floatval($_POST['length'] ?? 0),
            'width' => floatval($_POST['width'] ?? 0),
            'height' => floatval($_POST['height'] ?? 0),
            'length_class_id' => intval($_POST['length_class_id'] ?? 1),
            'weight' => floatval($_POST['weight'] ?? 0),
            'weight_class_id' => intval($_POST['weight_class_id'] ?? 1),
            'sort_order' => intval($_POST['sort_order'] ?? 0),
        ];

        // Resim yükleme
        $imageFilename = null;
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = uploadImageSecure($_FILES['image'], 'products');
            if ($uploadResult['success']) {
                $imageFilename = $uploadResult['filename'];
            } else {
                mesaj('urunler', 'Resim yüklenemedi: ' . ($uploadResult['error'] ?? 'Bilinmeyen hata'), 'hata');
                git('/admin/urunler.php');
            }
        }

        if ($action === 'add') {
            Database::query(
                "INSERT INTO products (name, product_type, slug, category_id, brand, sku, barcode, desi, price, discount_price, stock,
                 short_description, description, image, is_featured, status,
                 pricing_type, price_per_m2, min_width_cm, max_width_cm, min_height_cm, max_height_cm, pricing_extra_options,
                 meta_title, meta_description, meta_keyword, tags, model, upc, ean, jan, isbn, mpn, location, minimum_qty,
                 subtract_stock, out_of_stock_status_id, requires_shipping, date_available, length, width, height,
                 length_class_id, weight, weight_class_id, sort_order)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [
                    $data['name'],
                    $data['product_type'],
                    $data['slug'],
                    $data['category_id'],
                    $data['brand'],
                    $data['sku'],
                    $data['barcode'],
                    $data['desi'],
                    $data['price'],
                    $data['discount_price'],
                    $data['stock'],
                    $data['short_description'],
                    $data['description'],
                    $imageFilename,
                    $data['featured'],
                    $data['status'],
                    $data['pricing_type'],
                    $data['price_per_m2'],
                    $data['min_width_cm'],
                    $data['max_width_cm'],
                    $data['min_height_cm'],
                    $data['max_height_cm'],
                    $data['pricing_extra_options'],
                    $data['meta_title'],
                    $data['meta_description'],
                    $data['meta_keyword'],
                    $data['tags'],
                    $data['model'],
                    $data['upc'],
                    $data['ean'],
                    $data['jan'],
                    $data['isbn'],
                    $data['mpn'],
                    $data['location'],
                    $data['minimum_qty'],
                    $data['subtract_stock'],
                    $data['out_of_stock_status_id'],
                    $data['requires_shipping'],
                    $data['date_available'],
                    $data['length'],
                    $data['width'],
                    $data['height'],
                    $data['length_class_id'],
                    $data['weight'],
                    $data['weight_class_id'],
                    $data['sort_order']
                ]
            );
            $productId = Database::lastInsertId();

            // İlk stok kaydı
            if ($data['product_type'] === 'simple' && $data['stock'] > 0) {
                Product::logInventory($productId, null, $data['stock'], 'Ürün açılış stoğu');
            }

            flash('admin_products', 'Ürün eklendi.', 'success');
        } else {
            $productId = intval($_POST['product_id']);
            $oldProduct = Database::fetch("SELECT stock FROM products WHERE id = ?", [$productId]);

            $sql = "UPDATE products SET name=?, product_type=?, slug=?, category_id=?, brand=?, sku=?, barcode=?, desi=?, price=?, discount_price=?, stock=?,
                    short_description=?, description=?, is_featured=?, status=?,
                    pricing_type=?, price_per_m2=?, min_width_cm=?, max_width_cm=?, min_height_cm=?, max_height_cm=?,
                    pricing_extra_options=?, meta_title=?, meta_description=?, meta_keyword=?, tags=?, model=?,
                    upc=?, ean=?, jan=?, isbn=?, mpn=?, location=?, minimum_qty=?, subtract_stock=?,
                    out_of_stock_status_id=?, requires_shipping=?, date_available=?, length=?, width=?, height=?,
                    length_class_id=?, weight=?, weight_class_id=?, sort_order=?";
            $params = [
                $data['name'],
                $data['product_type'],
                $data['slug'],
                $data['category_id'],
                $data['brand'],
                $data['sku'],
                $data['barcode'],
                $data['desi'],
                $data['price'],
                $data['discount_price'],
                $data['stock'],
                $data['short_description'],
                $data['description'],
                $data['featured'],
                $data['status'],
                $data['pricing_type'],
                $data['price_per_m2'],
                $data['min_width_cm'],
                $data['max_width_cm'],
                $data['min_height_cm'],
                $data['max_height_cm'],
                $data['pricing_extra_options'],
                $data['meta_title'],
                $data['meta_description'],
                $data['meta_keyword'],
                $data['tags'],
                $data['model'],
                $data['upc'],
                $data['ean'],
                $data['jan'],
                $data['isbn'],
                $data['mpn'],
                $data['location'],
                $data['minimum_qty'],
                $data['subtract_stock'],
                $data['out_of_stock_status_id'],
                $data['requires_shipping'],
                $data['date_available'],
                $data['length'],
                $data['width'],
                $data['height'],
                $data['length_class_id'],
                $data['weight'],
                $data['weight_class_id'],
                $data['sort_order'],
            ];
            if ($imageFilename) {
                $sql .= ", image=?";
                $params[] = $imageFilename;
            }
            $sql .= " WHERE id=?";
            $params[] = $productId;
            Database::query($sql, $params);

            // Stok değişimi logla
            if ($data['product_type'] === 'simple' && $oldProduct['stock'] != $data['stock']) {
                $diff = $data['stock'] - $oldProduct['stock'];
                Product::logInventory($productId, null, $diff, 'Manuel stok güncelleme');
            }

            mesaj('urunler', 'Ürün güncellendi.', 'basari');
        }

        // ESKİ SİSTEM SEÇENEKLERİ (product_options)
        Database::query("DELETE FROM product_options WHERE product_id = ?", [$productId]);
        if (!empty($_POST['product_option'])) {
            foreach ($_POST['product_option'] as $optId => $values) {
                foreach ($values as $v) {
                    if (!empty($v['option_value_id'])) {
                        Database::query("
                            INSERT INTO product_options (product_id, option_id, option_value_id, quantity, subtract, price, price_prefix)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ", [
                            $productId,
                            $optId,
                            intval($v['option_value_id']),
                            intval($v['quantity'] ?? 0),
                            1,
                            floatval($v['price'] ?? 0),
                            $v['price_prefix'] ?? '+'
                        ]);
                    }
                }
            }
        }

        // Ürün Özellikleri (Attributes)
        Database::query("DELETE FROM product_attributes WHERE product_id = ?", [$productId]);
        if (!empty($_POST['product_attribute'])) {
            foreach ($_POST['product_attribute'] as $pa) {
                if (!empty($pa['attribute_id'])) {
                    Database::query("INSERT INTO product_attributes (product_id, attribute_id, text) VALUES (?, ?, ?)", [
                        $productId,
                        intval($pa['attribute_id']),
                        trim($pa['text'] ?? '')
                    ]);
                }
            }
        }

        // YENİ SİSTEM VARYASYON MATRİSİ (product_variations)
        if ($data['product_type'] === 'variable' && !empty($_POST['v'])) {
            Variation::saveVariations($productId, $_POST['v']);

            // Matris girildiyse toplam stoğu ana tabloya yansıt
            $totalStock = 0;
            foreach ($_POST['v'] as $v) {
                $totalStock += intval($v['stock'] ?? 0);
            }
            Database::query("UPDATE products SET stock = ? WHERE id = ?", [$totalStock, $productId]);
        }

        git('/admin/urunler.php');
    }

    if ($action === 'delete') {
        Database::query("DELETE FROM products WHERE id = ?", [intval($_POST['product_id'])]);
        mesaj('urunler', 'Ürün silindi.', 'basari');
        git('/admin/urunler.php');
    }

    if ($action === 'bulk_delete') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids)) {
            $ids = array_map('intval', $ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            Database::query("DELETE FROM products WHERE id IN ($placeholders)", $ids);
            mesaj('urunler', count($ids) . ' ürün silindi.', 'basari');
        }
        git('/admin/urunler.php');
    }
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$search = trim($_GET['q'] ?? '');
$where = '1=1';
$params = [];
if ($search) {
    $where .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
    $params = ["%$search%", "%$search%"];
}
$total = Database::fetch("SELECT COUNT(*) as c FROM products p WHERE $where", $params)['c'];
$pagination = paginate($total, $perPage, $page);
$products = Database::fetchAll("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where ORDER BY p.id DESC LIMIT $perPage OFFSET {$pagination['offset']}", $params);
$categories = getCategories();
?>

<div class="admin-header">
    <h1><i class="fas fa-box" style="color:var(--admin-primary)"></i> Ürün Yönetimi</h1>
    <a href="urun-formu.php" class="admin-btn admin-btn-primary"><i class="fas fa-plus"></i> Yeni Ürün</a>
</div>

<?php mesaj_goster('urunler'); ?>

<div class="admin-toolbar">
    <div class="admin-search">
        <i class="fas fa-search"></i>
        <form method="GET"><input type="text" name="q" placeholder="Ürün ara..." value="<?= e($search) ?>"></form>
    </div>
    <span style="font-size:0.875rem;color:var(--admin-gray)">
        <?= $total ?> ürün
    </span>
</div>

<!-- Toplu İşlem Barı -->
<form id="bulkForm" method="POST">
    <input type="hidden" name="action" value="bulk_delete">
    <div id="bulkBar"
        style="display:none;padding:10px 16px;background:var(--admin-danger, #e74c3c);color:#fff;border-radius:8px;margin-bottom:12px;align-items:center;gap:12px;justify-content:space-between">
        <span><strong id="selectedCount">0</strong> ürün seçildi</span>
        <button type="submit" class="admin-btn admin-btn-sm"
            style="background:#fff;color:var(--admin-danger, #e74c3c);font-weight:600"
            onclick="return confirm('Seçili ürünleri silmek istediğinize emin misiniz?')">
            <i class="fas fa-trash"></i> Seçilenleri Sil
        </button>
    </div>

    <div class="admin-card" style="padding:0">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px"><input type="checkbox" id="selectAll" title="Tümünü Seç"></th>
                        <th>Resim</th>
                        <th>Ürün</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Stok</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><input type="checkbox" class="product-check" name="ids[]" value="<?= $p['id'] ?>"></td>
                            <td><img src="<?= resim_linki($p['image']) ?>" class="admin-product-img" alt=""></td>
                            <td><strong>
                                    <?= temiz(truncate($p['name'], 50)) ?>
                                </strong><br><span style="font-size:0.75rem;color:var(--admin-gray)">SKU:
                                    <?= temiz($p['sku'] ?: '-') ?>
                                </span></td>
                            <td>
                                <?= temiz($p['category_name'] ?: '-') ?>
                            </td>
                            <td>
                                <?php if ($p['discount_price']): ?>
                                    <span style="text-decoration:line-through;color:var(--admin-gray);font-size:0.75rem">
                                        <?= para_yaz($p['price']) ?>
                                    </span><br>
                                <?php endif; ?>
                                <strong>
                                    <?= para_yaz($p['discount_price'] ?: $p['price']) ?>
                                </strong>
                            </td>
                            <td>
                                <?= $p['stock'] <= 0 ? '<span class="admin-badge admin-badge-red">Tükendi</span>' : $p['stock'] ?>
                            </td>
                            <td>
                                <?= $p['status'] ? '<span class="admin-badge admin-badge-green">Aktif</span>' : '<span class="admin-badge admin-badge-red">Pasif</span>' ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/urun-detay.php?slug=<?= temiz($p['slug']) ?>" target="_blank"
                                    class="admin-btn admin-btn-outline admin-btn-sm" title="Görüntüle"><i
                                        class="fas fa-eye"></i></a>
                                <a href="urun-formu.php?id=<?= $p['id'] ?>"
                                    class="admin-btn admin-btn-sm admin-btn-secondary" title="Düzenle"><i
                                        class="fas fa-edit"></i></a>
                                <form method="POST" style="display:inline"
                                    onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="product_id"
                                        value="<?= $p['id'] ?>">
                                    <button class="admin-btn admin-btn-danger admin-btn-sm"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</form><!-- /bulkForm -->

<script>
    window.BASE_URL = '<?= BASE_URL ?>';
    const selectAll = document.getElementById('selectAll');
    const bulkBar = document.getElementById('bulkBar');
    const selectedCount = document.getElementById('selectedCount');
    const checkboxes = () => document.querySelectorAll('.product-check');

    function updateBulkBar() {
        const checked = document.querySelectorAll('.product-check:checked').length;
        selectedCount.textContent = checked;
        bulkBar.style.display = checked > 0 ? 'flex' : 'none';
        selectAll.checked = checked > 0 && checked === checkboxes().length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes().forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('product-check')) updateBulkBar();
    });

    function openEditModal(p) {
        const m = document.getElementById('editModal');
        m.querySelector('[name="product_id"]').value = p.id;
        m.querySelector('[name="name"]').value = p.name || '';
        m.querySelector('[name="category_id"]').value = p.category_id || '';
        m.querySelector('[name="brand"]').value = p.brand || '';
        m.querySelector('[name="sku"]').value = p.sku || '';
        m.querySelector('[name="price"]').value = p.price || '';
        m.querySelector('[name="discount_price"]').value = p.discount_price || '';
        m.querySelector('[name="stock"]').value = p.stock || 0;
        m.querySelector('[name="short_description"]').value = p.short_description || '';
        m.querySelector('[name="description"]').value = p.description || '';
        // Pricing
        const ptSel = m.querySelector('[name="pricing_type"]');
        if (ptSel) {
            ptSel.value = p.pricing_type || 'fixed';
            togglePricingFields(ptSel);
        }
        const setf = (n, v) => { const el = m.querySelector('[name="' + n + '"]'); if (el) el.value = v ?? ''; };
        setf('price_per_m2', p.price_per_m2);
        setf('min_width_cm', p.min_width_cm);
        setf('max_width_cm', p.max_width_cm);
        setf('min_height_cm', p.min_height_cm);
        setf('max_height_cm', p.max_height_cm);
        m.querySelector('[name="featured"]').checked = p.featured == 1;
        m.querySelector('[name="status"]').checked = p.status == 1;
        // Mevcut resim önizleme
        const imgPreview = m.querySelector('#editImgPreview');
        if (p.image && p.image !== 'Array') {
            imgPreview.src = p.image.startsWith('http') ? p.image : (window.BASE_URL + '/assets/uploads/' + p.image);
            imgPreview.style.display = 'block';
        } else {
            imgPreview.style.display = 'none';
        }
        m.classList.add('active');
    }

    function togglePricingFields(sel) {
        const modal = sel.closest('.admin-modal');
        const m2box = modal ? modal.querySelectorAll('.edit-pf-m2') : [];
        const isM2 = sel.value === 'per_m2';
        m2box.forEach(el => el.style.display = isM2 ? 'block' : 'none');
    }
</script>

<!-- Edit Product Modal -->
<div id="editModal" class="admin-modal-bg" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3><i class="fas fa-edit" style="color:var(--admin-primary)"></i> Ürünü Düzenle</h3>
            <button class="admin-modal-close"
                onclick="document.getElementById('editModal').classList.remove('active')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="admin-modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="product_id" value="">
                <div class="form-group"><label>Ürün Adı *</label><input type="text" name="name" class="form-control"
                        required></div>
                <div class="form-row">
                    <div class="form-group"><label>Kategori *</label><select name="category_id" class="form-control"
                            required>
                            <option value="">Seçin</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="form-group"><label>Marka</label><input type="text" name="brand" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Fiyat *</label><input type="number" name="price" class="form-control"
                            step="0.01" required></div>
                    <div class="form-group"><label>İndirimli Fiyat</label><input type="number" name="discount_price"
                            class="form-control" step="0.01"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Stok *</label><input type="number" name="stock" class="form-control"
                            value="0" required></div>
                    <div class="form-group"><label>SKU</label><input type="text" name="sku" class="form-control"></div>
                </div>
                <div class="form-group"><label>Kısa Açıklama</label><input type="text" name="short_description"
                        class="form-control"></div>
                <div class="form-group"><label>Açıklama</label><textarea name="description"
                        class="form-control"></textarea></div>

                <!-- Fiyatlandırma Tipi -->
                <div class="form-group">
                    <label>Fiyatlandırma Tipi</label>
                    <select name="pricing_type" class="form-control edit-pricing-type"
                        onchange="togglePricingFields(this)">
                        <option value="fixed">Sabit Fiyat</option>
                        <option value="per_m2">M² Başına Fiyat</option>
                        <option value="per_piece">Adet Başına (sipariş formu)</option>
                    </select>
                </div>
                <div class="edit-pf-m2" style="display:none">
                    <div class="form-row">
                        <div class="form-group"><label>M² Fiyatı (TL)</label>
                            <input type="number" name="price_per_m2" class="form-control" step="0.01" min="0"
                                placeholder="0.00">
                        </div>
                        <div class="form-group"><label>Min En (cm)</label>
                            <input type="number" name="min_width_cm" class="form-control" min="1">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Max En (cm)</label>
                            <input type="number" name="max_width_cm" class="form-control" min="1">
                        </div>
                        <div class="form-group"><label>Min Boy (cm)</label>
                            <input type="number" name="min_height_cm" class="form-control" min="1">
                        </div>
                    </div>
                    <div class="form-group"><label>Max Boy (cm)</label>
                        <input type="number" name="max_height_cm" class="form-control" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label>Ürün Resmi <small style="color:var(--admin-gray)">(Boş bırakırsanız mevcut resim
                            korunur)</small></label>
                    <img id="editImgPreview" src="" alt="Mevcut resim"
                        style="display:none;max-height:80px;border-radius:6px;margin-bottom:8px">
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div style="display:flex;gap:16px">
                    <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="featured"> Öne
                        Çıkan</label>
                    <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="status" checked>
                        Aktif</label>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-outline"
                    onclick="document.getElementById('editModal').classList.remove('active')">İptal</button>
                <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save"></i> Güncelle</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addModal" class="admin-modal-bg" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3><i class="fas fa-plus" style="color:var(--admin-primary)"></i> Yeni Ürün</h3><button
                class="admin-modal-close"
                onclick="document.getElementById('addModal').classList.remove('active')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="admin-modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group"><label>Ürün Adı *</label><input type="text" name="name" class="form-control"
                        required></div>
                <div class="form-row">
                    <div class="form-group"><label>Kategori *</label><select name="category_id" class="form-control"
                            required>
                            <option value="">Seçin</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="form-group"><label>Marka</label><input type="text" name="brand" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Fiyat *</label><input type="number" name="price" class="form-control"
                            step="0.01" required></div>
                    <div class="form-group"><label>İndirimli Fiyat</label><input type="number" name="discount_price"
                            class="form-control" step="0.01"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Stok *</label><input type="number" name="stock" class="form-control"
                            value="0" required></div>
                    <div class="form-group"><label>SKU</label><input type="text" name="sku" class="form-control"></div>
                </div>
                <div class="form-group"><label>Kısa Açıklama</label><input type="text" name="short_description"
                        class="form-control"></div>
                <div class="form-group"><label>Açıklama</label><textarea name="description"
                        class="form-control"></textarea></div>

                <!-- Fiyatlandırma Tipi -->
                <div class="form-group">
                    <label>Fiyatlandırma Tipi</label>
                    <select name="pricing_type" class="form-control" onchange="togglePricingFields(this)">
                        <option value="fixed">Sabit Fiyat</option>
                        <option value="per_m2">M² Başına Fiyat</option>
                        <option value="per_piece">Adet Başına (sipariş formu)</option>
                    </select>
                </div>
                <div class="edit-pf-m2" style="display:none">
                    <div class="form-row">
                        <div class="form-group"><label>M² Fiyatı (TL)</label>
                            <input type="number" name="price_per_m2" class="form-control" step="0.01" min="0"
                                placeholder="0.00">
                        </div>
                        <div class="form-group"><label>Min En (cm)</label>
                            <input type="number" name="min_width_cm" class="form-control" min="1">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Max En (cm)</label>
                            <input type="number" name="max_width_cm" class="form-control" min="1">
                        </div>
                        <div class="form-group"><label>Min Boy (cm)</label>
                            <input type="number" name="min_height_cm" class="form-control" min="1">
                        </div>
                    </div>
                    <div class="form-group"><label>Max Boy (cm)</label>
                        <input type="number" name="max_height_cm" class="form-control" min="1">
                    </div>
                </div>

                <div class="form-group"><label>Ürün Resmi</label><input type="file" name="image" class="form-control"
                        accept="image/*"></div>
                <div style="display:flex;gap:16px">
                    <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="featured"> Öne
                        Çıkan</label>
                    <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" name="status" checked>
                        Aktif</label>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-outline"
                    onclick="document.getElementById('addModal').classList.remove('active')">İptal</button>
                <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>