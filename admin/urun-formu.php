<?php
$pageTitle = 'Ürün Ekle/Düzenle';
$adminPage = 'products';
require_once __DIR__ . '/includes/header.php';

$productId = intval($_GET['id'] ?? 0);
$product = null;
$productOptions = [];
$productAttributes = [];

if ($productId) {
    $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$productId]);
    if (!$product) {
        flash('admin_products', 'Ürün bulunamadı.', 'error');
        redirect('/admin/products.php');
    }

    // Mevcut varyasyonları çek
    $rows = Database::fetchAll("
        SELECT po.*, o.name as option_name, ov.name as value_name
        FROM product_options po 
        JOIN options o ON po.option_id = o.id 
        JOIN option_values ov ON po.option_value_id = ov.id
        WHERE po.product_id = ?
        ORDER BY o.sort_order, o.id, ov.sort_order
    ", [$productId]);

    foreach ($rows as $row) {
        $optId = $row['option_id'];
        if (!isset($productOptions[$optId])) {
            $productOptions[$optId] = [
                'name' => $row['option_name'],
                'values' => []
            ];
        }
        $productOptions[$optId]['values'][] = $row;
    }

    // Özellikleri çek
    $productAttributes = Database::fetchAll("
        SELECT pa.*, a.name as attribute_name 
        FROM product_attributes pa 
        JOIN attributes a ON pa.attribute_id = a.id 
        WHERE pa.product_id = ?
    ", [$productId]);

    // Yeni varyasyon matrisini (Matrix) çek
    $product['variations'] = Product::getVariations($productId);
}

$allCats = Product::categoriesFlat(); // Hiyerarşik kategoriler
$allAttributes = Database::fetchAll("SELECT * FROM attributes ORDER BY name ASC");
?>

<div class="admin-header">
    <h1>
        <i class="fas fa-box" style="color:var(--admin-primary)"></i>
        <?= $productId ? 'Ürünü Düzenle' : 'Yeni Ürün Ekle' ?>
    </h1>
    <div class="admin-header-actions">
        <button form="productForm" type="submit" class="admin-btn admin-btn-primary">
            <i class="fas fa-save"></i> Kaydet
        </button>
        <a href="products.php" class="admin-btn admin-btn-secondary">
            <i class="fas fa-times"></i> İptal
        </a>
    </div>
</div>

<?php showFlash('admin_products'); ?>

<form id="productForm" action="products.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?= $productId ? 'edit' : 'add' ?>">
    <?php if ($productId): ?>
        <input type="hidden" name="product_id" value="<?= $productId ?>">
    <?php endif; ?>

    <div class="admin-card product-form-card" style="padding:0">
        <!-- Tab Menüsü -->
        <ul class="nav-tabs enterprise-tabs">
            <li class="active" onclick="showTab('tab-general')">Genel</li>
            <li onclick="showTab('tab-data')">Veri</li>
            <li onclick="showTab('tab-links')">Bağlantılar</li>
            <li onclick="showTab('tab-attribute')">Özellik</li>
            <li onclick="showTab('tab-option')">Seçenek</li>
            <li onclick="showTab('tab-discount')">İndirim</li>
            <li onclick="showTab('tab-special')">Kampanya</li>
            <li onclick="showTab('tab-image')">Resim</li>
            <li onclick="showTab('tab-reward')">Puan</li>
            <li onclick="showTab('tab-design')">Tasarım</li>
        </ul>

        <div class="tab-content enterprise-tab-content" style="padding:25px">
            <!-- GENEL SEKEMESİ -->
            <div id="tab-general" class="tab-pane active">
                <div class="form-group mb-4">
                    <label>Ürün Adı *</label>
                    <input type="text" name="name" class="form-control" required
                        value="<?= e($product['name'] ?? '') ?>" placeholder="Ürün Adı">
                </div>
                <div class="form-group mb-4">
                    <label>Açıklama</label>
                    <textarea name="description" id="editor" class="form-control"
                        rows="10"><?= e($product['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group mb-4">
                    <label>Meta Başlığı *</label>
                    <input type="text" name="meta_title" class="form-control"
                        value="<?= e($product['meta_title'] ?? '') ?>" placeholder="Meta Tag Title">
                </div>
                <div class="form-group mb-4">
                    <label>Meta Açıklaması</label>
                    <textarea name="meta_description" class="form-control" rows="3"
                        placeholder="Meta Tag Description"><?= e($product['meta_description'] ?? '') ?></textarea>
                </div>
                <div class="form-group mb-4">
                    <label>Meta Kelimeleri</label>
                    <textarea name="meta_keyword" class="form-control" rows="3"
                        placeholder="Meta Tag Keywords"><?= e($product['meta_keyword'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Ürün Etiketleri</label>
                    <input type="text" name="tags" class="form-control" value="<?= e($product['tags'] ?? '') ?>"
                        placeholder="Ürün Etiketleri">
                    <small class="text-muted">Virgül ile ayırın</small>
                </div>
            </div>

            <!-- VERİ SEKEMESİ -->
            <div id="tab-data" class="tab-pane">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Model *</label>
                        <input type="text" name="model" class="form-control" value="<?= e($product['model'] ?? '') ?>"
                            required placeholder="Model">
                    </div>
                    <div class="form-group">
                        <label>SKU (Stok Kodu)</label>
                        <input type="text" name="sku" class="form-control" value="<?= e($product['sku'] ?? '') ?>"
                            placeholder="SKU">
                    </div>
                    <div class="form-group">
                        <label>UPC</label>
                        <input type="text" name="upc" class="form-control" value="<?= e($product['upc'] ?? '') ?>"
                            placeholder="UPC">
                    </div>
                    <div class="form-group">
                        <label>EAN</label>
                        <input type="text" name="ean" class="form-control" value="<?= e($product['ean'] ?? '') ?>"
                            placeholder="EAN">
                    </div>
                    <div class="form-group">
                        <label>JAN</label>
                        <input type="text" name="jan" class="form-control" value="<?= e($product['jan'] ?? '') ?>"
                            placeholder="JAN">
                    </div>
                    <div class="form-group">
                        <label>ISBN</label>
                        <input type="text" name="isbn" class="form-control" value="<?= e($product['isbn'] ?? '') ?>"
                            placeholder="ISBN">
                    </div>
                    <div class="form-group">
                        <label>MPN</label>
                        <input type="text" name="mpn" class="form-control" value="<?= e($product['mpn'] ?? '') ?>"
                            placeholder="MPN">
                    </div>
                    <div class="form-group">
                        <label>Konum</label>
                        <input type="text" name="location" class="form-control"
                            value="<?= e($product['location'] ?? '') ?>" placeholder="Location">
                    </div>
                    <div class="form-group">
                        <label>Fiyat</label>
                        <input type="number" step="0.01" name="price" class="form-control"
                            value="<?= $product['price'] ?? '0.00' ?>">
                    </div>
                    <div class="form-group">
                        <label>İndirimli Fiyat</label>
                        <input type="number" step="0.01" name="discount_price" class="form-control"
                            value="<?= $product['discount_price'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Miktar (Stok)</label>
                        <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?? '1' ?>">
                    </div>
                    <div class="form-group">
                        <label>Asgari Miktar</label>
                        <input type="number" name="minimum_qty" class="form-control"
                            value="<?= $product['minimum_qty'] ?? '1' ?>">
                    </div>
                    <div class="form-group">
                        <label>Stoktan Düş</label>
                        <select name="subtract_stock" class="form-control">
                            <option value="1" <?= ($product['subtract_stock'] ?? 1) == 1 ? 'selected' : '' ?>>Evet</option>
                            <option value="0" <?= ($product['subtract_stock'] ?? 0) == 0 ? 'selected' : '' ?>>Hayır
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stok Dışı Durumu</label>
                        <select name="out_of_stock_status_id" class="form-control">
                            <option value="7" <?= ($product['out_of_stock_status_id'] ?? 7) == 7 ? 'selected' : '' ?>>2-3
                                Gün</option>
                            <option value="5" <?= ($product['out_of_stock_status_id'] ?? 0) == 5 ? 'selected' : '' ?>>
                                Stokta Yok</option>
                            <option value="8" <?= ($product['out_of_stock_status_id'] ?? 0) == 8 ? 'selected' : '' ?>>Ön
                                Sipariş</option>
                            <option value="6" <?= ($product['out_of_stock_status_id'] ?? 0) == 6 ? 'selected' : '' ?>>
                                Stokta Var</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kargo Gerekli</label>
                        <div class="radio-inline">
                            <label><input type="radio" name="requires_shipping" value="1"
                                    <?= ($product['requires_shipping'] ?? 1) == 1 ? 'checked' : '' ?>> Evet</label>
                            <label><input type="radio" name="requires_shipping" value="0"
                                    <?= ($product['requires_shipping'] ?? 1) == 0 ? 'checked' : '' ?>> Hayır</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Geçerlilik Tarihi</label>
                        <input type="date" name="date_available" class="form-control"
                            value="<?= $product['date_available'] ?? date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Boyutlar (B x E x Y)</label>
                        <div class="d-flex gap-1">
                            <input type="number" step="0.01" name="length" class="form-control" placeholder="Boy"
                                value="<?= $product['length'] ?? '' ?>">
                            <input type="number" step="0.01" name="width" class="form-control" placeholder="En"
                                value="<?= $product['width'] ?? '' ?>">
                            <input type="number" step="0.01" name="height" class="form-control" placeholder="Yükseklik"
                                value="<?= $product['height'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Boyut Birimi</label>
                        <select name="length_class_id" class="form-control">
                            <option value="1" <?= ($product['length_class_id'] ?? 1) == 1 ? 'selected' : '' ?>>Santimetre
                            </option>
                            <option value="2" <?= ($product['length_class_id'] ?? 0) == 2 ? 'selected' : '' ?>>Milimetre
                            </option>
                            <option value="3" <?= ($product['length_class_id'] ?? 0) == 3 ? 'selected' : '' ?>>İnç</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ağırlık</label>
                        <input type="number" step="0.01" name="weight" class="form-control"
                            value="<?= $product['weight'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Ağırlık Birimi</label>
                        <select name="weight_class_id" class="form-control">
                            <option value="1" <?= ($product['weight_class_id'] ?? 1) == 1 ? 'selected' : '' ?>>Kilogram
                            </option>
                            <option value="2" <?= ($product['weight_class_id'] ?? 0) == 2 ? 'selected' : '' ?>>Gram
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Durum</label>
                        <select name="status" class="form-control">
                            <option value="1" <?= ($product['status'] ?? 1) == 1 ? 'selected' : '' ?>>Açık</option>
                            <option value="0" <?= ($product['status'] ?? 1) == 0 ? 'selected' : '' ?>>Kapalı</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sıralama</label>
                        <input type="number" name="sort_order" class="form-control"
                            value="<?= $product['sort_order'] ?? '1' ?>">
                    </div>
                </div>
            </div>

            <!-- BAĞLANTILAR SEKEMESİ -->
            <div id="tab-links" class="tab-pane">
                <div class="form-group mb-4">
                    <label>Üretici (Marka)</label>
                    <input type="text" name="brand" class="form-control" value="<?= e($product['brand'] ?? '') ?>"
                        placeholder="Üretici">
                </div>
                <div class="form-group mb-4">
                    <label>Kategoriler</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">--- Seçiniz ---</option>
                        <?php
                        $currentCatId = $product['category_id'] ?? 0;
                        foreach ($allCats as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($currentCatId == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['display_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Benzer Ürünler</label>
                    <input type="text" class="form-control" placeholder="Benzer Ürünler">
                </div>
            </div>

            <!-- ÖZELLİK SEKEMESİ -->
            <div id="tab-attribute" class="tab-pane">
                <table id="attributeTable" class="admin-table enterprise-table">
                    <thead>
                        <tr>
                            <th>Özellik Adı</th>
                            <th>Açıklama (Text)</th>
                            <th style="width: 50px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($productAttributes)): ?>
                            <?php foreach ($productAttributes as $idx => $pa): ?>
                                <tr>
                                    <td>
                                        <select name="product_attribute[<?= $idx ?>][attribute_id]" class="form-control">
                                            <?php foreach ($allAttributes as $a): ?>
                                                <option value="<?= $a['id'] ?>" <?= $a['id'] == $pa['attribute_id'] ? 'selected' : '' ?>>
                                                    <?= e($a['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><textarea name="product_attribute[<?= $idx ?>][text]"
                                            class="form-control"><?= e($pa['text']) ?></textarea></td>
                                    <td><button type="button" class="btn btn-danger btn-sm"
                                            onclick="this.closest('tr').remove()">&times;</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"></td>
                            <td><button type="button" class="btn btn-primary btn-sm" onclick="addAttributeRow()"><i
                                        class="fas fa-plus"></i></button></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- SEÇENEK SEKEMESİ -->
            <div id="tab-option" class="tab-pane">
                <div class="option-layout" style="border: 1px solid #eee; border-radius: 8px;">
                    <div class="option-sidebar" style="background: #f8f9fa;">
                        <input type="text" id="optionSearch" class="form-control form-control-sm mb-2"
                            placeholder="Seçenek Ara..." onkeyup="searchGlobalOptions(this.value)">
                        <div id="optionSearchResults"
                            style="display:none; position:absolute; z-index:100; background:#fff; border:1px solid #ddd; width:180px;">
                        </div>
                        <ul class="option-nav" id="activeProductOptions" style="list-style:none; padding:10px;"></ul>
                    </div>
                    <div class="option-content" id="optionValuesContainer" style="padding: 20px;">
                        <div class="alert alert-light text-center">Bir seçenek seçin veya soldan ekleyin.</div>
                    </div>
                </div>

                <div class="variation-matrix-wrapper mt-5 pt-4" style="border-top: 2px dashed #eee;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Varyasyon Matrisi</h3>
                        <button type="button" class="btn btn-warning" onclick="generateVariationMatrix()">
                            <i class="fas fa-magic"></i> Matrisi Yeniden Oluştur
                        </button>
                    </div>
                    <div class="admin-table-wrapper">
                        <table class="admin-table" id="variationMatrixTable">

                            <thead>
                                <tr>
                                    <th>Kombinasyon</th>
                                    <th>SKU</th>
                                    <th>Fiyat ±</th>
                                    <th>Stok</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($product['variations'])): ?>
                                    <?php foreach ($product['variations'] as $vIdx => $v):
                                        $specs = json_decode($v['specs'] ?? '{}', true);
                                        $specStr = implode(' / ', array_values($specs));
                                        ?>
                                        <tr>
                                            <td><strong><?= e($specStr) ?></strong><input type="hidden"
                                                    name="v[<?= $vIdx ?>][specs]" value='<?= e($v['specs']) ?>'></td>
                                            <td><input type="text" name="v[<?= $vIdx ?>][sku]"
                                                    class="form-control form-control-sm" value="<?= e($v['sku']) ?>"></td>
                                            <td><input type="number" step="0.01" name="v[<?= $vIdx ?>][price_modifier]"
                                                    class="form-control form-control-sm" value="<?= $v['price_modifier'] ?>">
                                            </td>
                                            <td><input type="number" name="v[<?= $vIdx ?>][stock]"
                                                    class="form-control form-control-sm" value="<?= $v['stock'] ?>"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm"
                                                    onclick="this.closest('tr').remove()">&times;</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- DİĞER SEKEMELER -->
            <div id="tab-discount" class="tab-pane">
                <div class="alert alert-info">İndirim tanımlamaları.</div>
            </div>
            <div id="tab-special" class="tab-pane">
                <div class="alert alert-info">Kampanya ayarları.</div>
            </div>
            <div id="tab-image" class="tab-pane">
                <div class="form-group mb-4">
                    <label>Ana Resim</label>
                    <?php if (!empty($product['image'])): ?>
                        <div class="mb-2">
                            <img src="<?= e(getImageUrl($product['image'])) ?>" style="width: 100px; border-radius: 4px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control">
                </div>
            </div>
            <div id="tab-reward" class="tab-pane">
                <div class="form-group">
                    <label>Puan</label>
                    <input type="number" name="reward_points" class="form-control" value="0">
                </div>
            </div>
            <div id="tab-design" class="tab-pane">
                <div class="alert alert-light">Tasarım Taslakları</div>
            </div>
        </div>
    </div>
</form>

<style>
    .enterprise-tabs {
        background: #fdfdfd;
        border-bottom: 1px solid #eee;
        display: flex;
        flex-wrap: wrap;
        padding: 0 10px;
        list-style: none;
        margin: 0;
    }

    .enterprise-tabs li {
        padding: 15px 25px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        color: #777;
    }

    .enterprise-tabs li.active {
        color: var(--admin-primary);
        border-bottom-color: var(--admin-primary);
        background: #fff;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    .option-layout {
        display: flex;
        min-height: 400px;
    }

    .option-sidebar {
        width: 200px;
        border-right: 1px solid #eee;
        padding: 15px;
    }

    .option-nav li {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 5px;
        cursor: pointer;
    }

    .option-nav li.active {
        background: var(--admin-primary);
        color: #fff;
    }
</style>

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.enterprise-tabs li').forEach(el => el.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function addAttributeRow() {
        const tbody = document.querySelector('#attributeTable tbody');
        const idx = tbody.children.length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="product_attribute[${idx}][attribute_id]" class="form-control">
                    <?php foreach ($allAttributes as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= e($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><textarea name="product_attribute[${idx}][text]" class="form-control"></textarea></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">&times;</button></td>
        `;
        tbody.appendChild(tr);
    }

    // Basit Seçenek JS
    let productOptions = <?= json_encode($productOptions) ?>;
    let activeOptionId = null;

    function searchGlobalOptions(q) {
        const results = document.getElementById('optionSearchResults');
        if (q.length < 1) { results.style.display = 'none'; return; }
        fetch(`api/options.php?action=search&q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                results.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(opt => {
                        const div = document.createElement('div');
                        div.style.padding = '8px 12px'; div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #eee';
                        div.innerHTML = `<i class="fas fa-plus-circle text-primary"></i> ${opt.name}`;
                        div.onclick = () => {
                            if (!productOptions[opt.id]) {
                                productOptions[opt.id] = { name: opt.name, values: [] };
                                renderOptionNav();
                                selectOption(opt.id);
                            } else {
                                alert('Bu seçenek zaten ekli.');
                            }
                            results.style.display = 'none';
                        };
                        div.onmouseover = () => div.style.background = '#f8f9fa';
                        div.onmouseout = () => div.style.background = '#fff';
                        results.appendChild(div);
                    });
                    results.style.display = 'block';
                } else {
                    results.style.display = 'none';
                }
            });
    }

    function renderOptionNav() {
        const nav = document.getElementById('activeProductOptions');
        nav.innerHTML = '';
        Object.keys(productOptions).forEach(id => {
            const li = document.createElement('li');
            li.className = (activeOptionId == id) ? 'active' : '';
            li.innerHTML = `<span>${productOptions[id].name}</span> <i class="fas fa-times-circle text-danger" onclick="removeOption(${id})"></i>`;
            li.onclick = (e) => { if(!e.target.classList.contains('fa-times-circle')) selectOption(id); };
            nav.appendChild(li);
        });
    }

    function selectOption(id) {
        activeOptionId = id;
        renderOptionNav();
        const container = document.getElementById('optionValuesContainer');
        container.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>${productOptions[id].name} Değerleri</h4>
                <button type="button" class="btn btn-sm btn-primary" onclick="addValueRow(${id})"><i class="fas fa-plus"></i> Değer Ekle</button>
            </div>
            <table class="admin-table">
                <thead><tr><th>Seçenek Değeri</th><th>Miktar</th><th>Fiyat Farkı</th><th>Sıralama</th><th></th></tr></thead>
                <tbody id="valRows"></tbody>
            </table>
        `;
        productOptions[id].values.forEach((v, idx) => renderValRow(id, v, idx));
        if (productOptions[id].values.length === 0) {
            container.querySelector('tbody').innerHTML = '<tr><td colspan="5" class="text-center text-muted">Henüz değer eklenmemiş.</td></tr>';
        }
    }

    function addValueRow(optId) {
        fetch(`api/options.php?action=get_values&option_id=${optId}`)
            .then(res => res.json())
            .then(data => {
                const newValue = { option_value_id: data[0]?.id || 0, quantity: 0, price: 0, price_prefix: '+', _available: data };
                productOptions[optId].values.push(newValue);
                renderValRow(optId, newValue, productOptions[optId].values.length - 1);
                // "Henüz değer eklenmemiş" yazısını temizle
                const empty = document.querySelector('#valRows tr td[colspan="5"]');
                if (empty) empty.closest('tr').remove();
            });
    }

    function renderValRow(optId, v, idx) {
        const tbody = document.getElementById('valRows');
        const tr = document.createElement('tr');
        const options = (v._available || []).map(av => `<option value="${av.id}" ${av.id == v.option_value_id ? 'selected' : ''}>${av.name}</option>`).join('');
        
        tr.innerHTML = `
            <td><select name="product_option[${optId}][${idx}][option_value_id]" class="form-control form-control-sm">${options}</select></td>
            <td><input type="number" name="product_option[${optId}][${idx}][quantity]" class="form-control form-control-sm" value="${v.quantity}"></td>
            <td>
                <div class="input-group input-group-sm">
                    <select name="product_option[${optId}][${idx}][price_prefix]" class="form-control" style="max-width:50px"><option value="+">+</option><option value="-">-</option></select>
                    <input type="number" step="0.01" name="product_option[${optId}][${idx}][price]" class="form-control" value="${v.price}">
                </div>
            </td>
            <td><input type="number" class="form-control form-control-sm" value="0"></td>
            <td><button type="button" class="btn btn-sm text-danger" onclick="productOptions[${optId}].values.splice(${idx},1); selectOption(${optId})">&times;</button></td>
        `;
        tbody.appendChild(tr);
    }

    function removeOption(id) {
        if (confirm('Seçeneği silmek istediğinize emin misiniz?')) {
            delete productOptions[id];
            if (activeOptionId == id) activeOptionId = null;
            renderOptionNav();
            document.getElementById('optionValuesContainer').innerHTML = '<div class="alert alert-light text-center">Bir seçenek seçin.</div>';
        }
    }

    function generateVariationMatrix() {
        const matrixTable = document.getElementById('variationMatrixTable').querySelector('tbody');
        const attributes = {};
        
        Object.keys(productOptions).forEach(optId => {
            const values = [];
            // DOM'dan mevcut seçili değerleri topla
            document.querySelectorAll(`select[name^="product_option[${optId}]"][name$="[option_value_id]"]`).forEach(sel => {
                values.push({ id: sel.value, name: sel.options[sel.selectedIndex].text, attrName: productOptions[optId].name });
            });
            if (values.length > 0) attributes[optId] = values;
        });

        if (Object.keys(attributes).length === 0) { alert('Önce seçenek ve değerlerini ekleyin!'); return; }

        function cartesian(args) {
            var r = [], max = args.length - 1;
            function helper(arr, i) {
                for (var j = 0, l = args[i].length; j < l; j++) {
                    var a = arr.slice(0); a.push(args[i][j]);
                    if (i == max) r.push(a); else helper(a, i + 1);
                }
            }
            helper([], 0); return r;
        }

        const combinations = cartesian(Object.values(attributes));
        matrixTable.innerHTML = '';
        combinations.forEach((combo, idx) => {
            const specStr = combo.map(c => c.name).join(' / ');
            const specs = {}; combo.forEach(c => specs[c.attrName] = c.name);
            tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${specStr}</strong><input type="hidden" name="v[${idx}][specs]" value='${JSON.stringify(specs)}'></td>
                <td><input type="text" name="v[${idx}][sku]" class="form-control form-control-sm" placeholder="SKU"></td>
                <td><input type="number" step="0.01" name="v[${idx}][price_modifier]" class="form-control form-control-sm" value="0"></td>
                <td><input type="number" name="v[${idx}][stock]" class="form-control form-control-sm" value="0"></td>
                <td><button type="button" class="btn btn-sm text-danger" onclick="this.closest('tr').remove()">&times;</button></td>
            `;
            matrixTable.appendChild(tr);
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>