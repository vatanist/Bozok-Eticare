<?php
$pageTitle = 'Kategori Yönetimi';
$adminPage = 'categories';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $ad = trim($_POST['name']);
        $ustId = intval($_POST['parent_id']) ?: null;
        Database::query(
            "INSERT INTO categories (name, slug, icon, parent_id, status) VALUES (?,?,?,?,1)",
            [$ad, link_yap($ad), trim($_POST['icon'] ?? 'fas fa-folder'), $ustId]
        );
        Product::syncCategoryPaths();
        mesaj('kategoriler', 'Kategori eklendi.', 'basari');
        git('/admin/kategoriler.php');
    }
    if ($action === 'edit') {
        $ad = trim($_POST['name']);
        $ustId = intval($_POST['parent_id']) ?: null;
        Database::query(
            "UPDATE categories SET name=?, slug=?, icon=?, parent_id=? WHERE id=?",
            [$ad, link_yap($ad), trim($_POST['icon']), $ustId, intval($_POST['category_id'])]
        );
        Product::syncCategoryPaths();
        mesaj('kategoriler', 'Kategori güncellendi.', 'basari');
        git('/admin/kategoriler.php');
    }
    if ($action === 'delete') {
        Database::query("DELETE FROM categories WHERE id = ?", [intval($_POST['category_id'])]);
        Product::syncCategoryPaths();
        mesaj('kategoriler', 'Kategori silindi.', 'basari');
        git('/admin/kategoriler.php');
    }

    if ($action === 'bulk_delete') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids)) {
            $ids = array_map('intval', $ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            Database::query("DELETE FROM categories WHERE id IN ($placeholders)", $ids);
            mesaj('kategoriler', count($ids) . ' kategori silindi.', 'basari');
        }
        git('/admin/kategoriler.php');
    }

    // XML'den Kategori Import
    if ($action === 'xml_import_categories') {
        $url = trim($_POST['xml_url'] ?? '');
        if (empty($url)) {
            mesaj('kategoriler', 'Lütfen bir XML URL girin.', 'hata');
            git('/admin/kategoriler.php');
        }

        $xml_icerik = @file_get_contents($url);
        if (!$xml_icerik) {
            mesaj('kategoriler', 'XML dosyası okunamadı. URL\'yi kontrol edin.', 'hata');
            git('/admin/kategoriler.php');
        }

        $xml = @simplexml_load_string($xml_icerik);
        if (!$xml) {
            mesaj('kategoriler', 'Geçerli bir XML yapısı bulunamadı.', 'hata');
            git('/admin/kategoriler.php');
        }

        // XML'den kategori isimlerini çıkar
        $kategori_yollari = [];
        $urunler = $xml->urun ?? $xml->product ?? $xml->item ?? $xml->children();
        foreach ($urunler as $item) {
            $catStr = trim((string) ($item->kategori ?? $item->category ?? $item->categoryName ?? ''));
            if (!empty($catStr) && !in_array($catStr, $kategori_yollari)) {
                $kategori_yollari[] = $catStr;
            }
        }

        if (empty($kategori_yollari)) {
            mesaj('kategoriler', 'XML içinde kategori bilgisi bulunamadı.', 'hata');
            git('/admin/kategoriler.php');
        }

        $eklenen = 0;
        foreach ($kategori_yollari as $yol) {
            $parcalar = array_map('trim', explode('>', $yol));
            $ustId = null;
            foreach ($parcalar as $ad) {
                $slug = link_yap($ad);
                $mevcut = Database::fetch("SELECT id FROM categories WHERE slug = ? AND (parent_id = ? OR (parent_id IS NULL AND ? IS NULL))", [$slug, $ustId, $ustId]);

                if ($mevcut) {
                    $ustId = $mevcut['id'];
                } else {
                    Database::query("INSERT INTO categories (name, slug, parent_id, status) VALUES (?,?,?,1)", [$ad, $slug, $ustId]);
                    $ustId = Database::lastInsertId();
                    $eklenen++;
                }
            }
        }

        mesaj('kategoriler', "$eklenen yeni kategori eklendi veya güncellendi.", 'basari');
        git('/admin/kategoriler.php');
    }
}

$tum_kategoriler = kategorileri_getir_duz();
?>
<div class="admin-header">
    <h1><i class="fas fa-th-list" style="color:var(--admin-primary)"></i> Kategoriler</h1>
    <div style="display:flex;gap:8px">
        <button onclick="document.getElementById('xmlImportModal').classList.add('active')"
            class="admin-btn admin-btn-outline">
            <i class="fas fa-cloud-download-alt"></i> XML'den İmport
        </button>
        <button onclick="document.getElementById('addCatModal').classList.add('active')"
            class="admin-btn admin-btn-primary">
            <i class="fas fa-plus"></i> Yeni Kategori
        </button>
    </div>
</div>
<?php showFlash('admin_cat'); ?>

<!-- Toplu İşlem Barı -->
<form id="bulkForm" method="POST">
    <input type="hidden" name="action" value="bulk_delete">
    <div id="bulkBar"
        style="display:none;padding:10px 16px;background:var(--admin-danger, #e74c3c);color:#fff;border-radius:8px;margin-bottom:12px;align-items:center;gap:12px;justify-content:space-between">
        <span><strong id="selectedCount">0</strong> kategori seçildi</span>
        <button type="submit" class="admin-btn admin-btn-sm"
            style="background:#fff;color:var(--admin-danger, #e74c3c);font-weight:600"
            onclick="return confirm('Seçili kategorileri silmek istediğinize emin misiniz?')">
            <i class="fas fa-trash"></i> Seçilenleri Sil
        </button>
    </div>

    <div class="admin-card" style="padding:0">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px"><input type="checkbox" id="selectAll" title="Tümünü Seç"></th>
                        <th>İkon</th>
                        <th>Kategori</th>
                        <th>Üst Kategori</th>
                        <th>Slug</th>
                        <th>Ürün Sayısı</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tum_kategoriler as $c): ?>
                        <tr>
                            <td><input type="checkbox" class="cat-check" name="ids[]" value="<?= $c['id'] ?>"></td>
                            <td><i class="<?= temiz($c['icon']) ?>" style="font-size:18px;color:var(--admin-primary)"></i>
                            </td>
                            <td>
                                <strong style="padding-left:<?= $c['level'] * 24 ?>px">
                                    <?php if ($c['level'] > 0): ?>
                                        <span
                                            style="color:var(--admin-gray);margin-right:4px"><?= str_repeat('—', $c['level']) ?></span>
                                    <?php endif; ?>
                                    <?= temiz($c['name']) ?>
                                </strong>
                            </td>
                            <td>
                                <?php
                                if ($c['parent_id']) {
                                    $ust = Database::fetch("SELECT name FROM categories WHERE id = ?", [$c['parent_id']]);
                                    echo temiz($ust['name'] ?? '-');
                                } else {
                                    echo '<span class="admin-badge admin-badge-green">Ana Kategori</span>';
                                }
                                ?>
                            </td>
                            <td><code style="font-size:0.75rem"><?= temiz($c['slug']) ?></code></td>
                            <td><?= $c['product_count'] ?></td>
                            <td>
                                <form method="POST" style="display:inline"
                                    onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">
                                    <input type="hidden" name="action" value="delete"><input type="hidden"
                                        name="category_id" value="<?= $c['id'] ?>">
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
    const selectAll = document.getElementById('selectAll');
    const bulkBar = document.getElementById('bulkBar');
    const selectedCount = document.getElementById('selectedCount');
    const checkboxes = () => document.querySelectorAll('.cat-check');

    function updateBulkBar() {
        const checked = document.querySelectorAll('.cat-check:checked').length;
        selectedCount.textContent = checked;
        bulkBar.style.display = checked > 0 ? 'flex' : 'none';
        selectAll.checked = checked > 0 && checked === checkboxes().length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes().forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('cat-check')) updateBulkBar();
    });
</script>

<!-- XML'den Kategori Import Modal -->
<div id="xmlImportModal" class="admin-modal-bg" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3><i class="fas fa-cloud-download-alt" style="color:var(--admin-primary)"></i> XML'den Kategori İmport
            </h3>
            <button class="admin-modal-close"
                onclick="document.getElementById('xmlImportModal').classList.remove('active')">&times;</button>
        </div>
        <form method="POST" class="admin-form" id="xmlImportForm">
            <div class="admin-modal-body">
                <input type="hidden" name="action" value="xml_import_categories">
                <div class="form-group">
                    <label>XML Feed URL'si</label>
                    <input type="url" name="xml_url" class="form-control" placeholder="https://ornek.com/urunler.xml"
                        required>
                </div>
                <div style="padding:12px 16px;background:#f0f7ff;border-radius:8px;border:1px solid #bfdbfe">
                    <p style="font-size:0.8125rem;color:#1e40af;margin:0"><i class="fas fa-info-circle"></i>
                        <strong>Nasıl çalışır?</strong>
                    </p>
                    <ul style="font-size:0.75rem;color:#1e40af;margin:8px 0 0 0;padding-left:16px">
                        <li>XML'deki ürünlerden kategori bilgisi okunur</li>
                        <li><code>Ana Kategori > Alt Kategori</code> formatı desteklenir</li>
                        <li>Mevcut kategoriler atlanır, sadece yeniler eklenir</li>
                        <li>Hiyerarşik yapı otomatik oluşturulur</li>
                    </ul>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-outline"
                    onclick="document.getElementById('xmlImportModal').classList.remove('active')">İptal</button>
                <button type="submit" class="admin-btn admin-btn-primary" id="xmlImportBtn">
                    <i class="fas fa-cloud-download-alt"></i> Kategorileri İmport Et
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('xmlImportForm').addEventListener('submit', function () {
        const btn = document.getElementById('xmlImportBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İmport ediliyor...';
        btn.disabled = true;
        btn.style.opacity = '0.7';
    });
</script>

<!-- Yeni Kategori Modal -->
<div id="addCatModal" class="admin-modal-bg" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3><i class="fas fa-plus" style="color:var(--admin-primary)"></i> Yeni Kategori</h3><button
                class="admin-modal-close"
                onclick="document.getElementById('addCatModal').classList.remove('active')">&times;</button>
        </div>
        <form method="POST" class="admin-form">
            <div class="admin-modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group"><label>Kategori Adı *</label><input type="text" name="name" class="form-control"
                        required></div>
                <div class="form-group"><label>İkon (Font Awesome Sınıfı)</label><input type="text" name="icon"
                        class="form-control" value="fas fa-tag" placeholder="fas fa-tag"></div>
                <div class="form-group"><label>Üst Kategori</label><select name="parent_id" class="form-control">
                        <option value="0">Yok (Ana Kategori)</option>
                        <?php
                        $hierarchicalCats = Product::categoriesFlat();
                        foreach ($hierarchicalCats as $c): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= e($c['display_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select></div>
            </div>
            <div class="admin-modal-footer"><button type="button" class="admin-btn admin-btn-outline"
                    onclick="document.getElementById('addCatModal').classList.remove('active')">İptal</button>
                <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>