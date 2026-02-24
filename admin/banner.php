<?php
/**
 * Admin — Banner & Reklam Yönetimi
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Banner Yönetimi';
$adminPage = 'marketing';

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dogrula_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_banner') {
        $title = trim($_POST['title']);
        $link = trim($_POST['link']);
        $pos = $_POST['position'];

        $img = resim_yukle($_FILES['image'], 'banners');
        if ($img['success']) {
            Database::query(
                "INSERT INTO banners (title, image, link, position, status) VALUES (?, ?, ?, ?, 1)",
                [$title, $img['filename'], $link, $pos]
            );
            mesaj('banner', 'Banner başarıyla eklendi.', 'basari');
        } else {
            mesaj('banner', 'Resim yükleme hatası: ' . $img['error'], 'hata');
        }
    } elseif ($action === 'delete_banner') {
        $id = intval($_POST['id']);
        Database::query("DELETE FROM banners WHERE id = ?", [$id]);
        mesaj('banner', 'Banner kaldırıldı.', 'basari');
    }
    git('/admin/banner.php');
}

$banners = Database::fetchAll("SELECT * FROM banners ORDER BY id DESC");

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <h1><i class="fas fa-image" style="color:var(--admin-primary)"></i> Banner Yönetimi</h1>
    <button class="admin-btn admin-btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
        <i class="fas fa-plus"></i> Yeni Banner Ekle
    </button>
</div>

<?php mesaj_goster('banner'); ?>

<div class="admin-card" style="padding:0">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:150px">Görsel</th>
                    <th>Başlık / Link</th>
                    <th>Pozisyon</th>
                    <th>İstatistik</th>
                    <th>Durum</th>
                    <th style="width:50px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($banners)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding:40px">Henüz banner eklenmemiş.</td>
                    </tr>
                <?php else:
                    foreach ($banners as $b): ?>
                        <tr>
                            <td>
                                <img src="<?= BASE_URL ?>/data/uploads/<?= e($b['image']) ?>"
                                    style="width:120px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #eee">
                            </td>
                            <td>
                                <strong>
                                    <?= e($b['title']) ?>
                                </strong><br>
                                <small class="text-muted">
                                    <?= e($b['link']) ?>
                                </small>
                            </td>
                            <td><span class="admin-badge admin-badge-blue">
                                    <?= e($b['position']) ?>
                                </span></td>
                            <td>
                                <small><i class="fas fa-eye"></i>
                                    <?= $b['view_count'] ?> Görüntülenme
                                </small><br>
                                <small><i class="fas fa-mouse-pointer"></i>
                                    <?= $b['click_count'] ?> Tıklama
                                </small>
                            </td>
                            <td>
                                <span class="admin-badge admin-badge-<?= $b['status'] ? 'green' : 'gray' ?>">
                                    <?= $b['status'] ? 'Aktif' : 'Pasif' ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Silmek istediğinize emin misiniz?')"
                                    style="display:inline">
                                    <?= csrf_kod() ?>
                                    <input type="hidden" name="action" value="delete_banner">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button class="admin-btn admin-btn-sm admin-btn-danger"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Ekleme Modal -->
<div id="addModal" class="admin-modal"
    style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:9999">
    <div class="admin-card" style="width:500px">
        <h3>Yeni Reklam Bannerı Ekle</h3>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_kod() ?>
            <input type="hidden" name="action" value="add_banner">
            <div class="admin-form-group">
                <label>Banner Başlığı</label>
                <input type="text" name="title" class="admin-input" required>
            </div>
            <div class="admin-form-group">
                <label>Hedef Link (URL)</label>
                <input type="text" name="link" class="admin-input" placeholder="https://...">
            </div>
            <div class="admin-form-group">
                <label>Pozisyon</label>
                <select name="position" class="admin-input">
                    <option value="homepage_main">Anasayfa Ana Slider</option>
                    <option value="homepage_side">Anasayfa Yan Banner</option>
                    <option value="category_top">Kategori Üstü</option>
                    <option value="product_popup">Ürün Popup</option>
                </select>
            </div>
            <div class="admin-form-group">
                <label>Görsel Dosyası</label>
                <input type="file" name="image" class="admin-input" required accept="image/*">
            </div>
            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="submit" class="admin-btn admin-btn-primary" style="flex:1">Yükle</button>
                <button type="button" class="admin-btn admin-btn-outline" style="flex:1"
                    onclick="document.getElementById('addModal').style.display='none'">İptal</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/header.php'; ?>