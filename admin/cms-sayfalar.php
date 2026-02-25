<?php
/**
 * Admin - Kurumsal CMS Sayfa Listesi
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

// ===================== BAŞLANGIÇ: CMS YETKİ KONTROLÜ =====================
if (class_exists('Auth') && !Auth::can('manage_cms')) {
    http_response_code(403);
    die('Bu alan için CMS yönetim yetkisi gereklidir.');
}
// ===================== BİTİŞ: CMS YETKİ KONTROLÜ =====================

$adminPage = 'pages';
$pageTitle = 'Kurumsal CMS Sayfaları';

$sayfalar = Database::fetchAll("SELECT id, title, slug, durum, siralama, updated_at FROM cms_pages ORDER BY siralama ASC, id DESC");

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
    <div>
        <h1><i class="fas fa-file-alt" style="color:var(--admin-primary)"></i> Kurumsal CMS Sayfaları</h1>
        <small style="color:var(--admin-gray)">Taslak/Yayında sayfaları yönetin.</small>
    </div>
    <a href="<?= BASE_URL ?>/admin/cms-sayfa-form.php" class="admin-btn admin-btn-primary">Yeni Sayfa</a>
</div>

<?php mesaj_goster('cms'); ?>

<div class="admin-card" style="padding:0;">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
            <tr>
                <th>Başlık</th>
                <th>Slug</th>
                <th>Durum</th>
                <th>Sıra</th>
                <th>Güncelleme</th>
                <th style="text-align:right; width:220px;">İşlem</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($sayfalar)): ?>
                <tr><td colspan="6" style="padding:30px; text-align:center; color:var(--admin-gray);">Henüz CMS sayfası yok.</td></tr>
            <?php endif; ?>

            <?php foreach ($sayfalar as $sayfa): ?>
                <tr>
                    <td><strong><?= temiz($sayfa['title']) ?></strong></td>
                    <td><code><?= temiz($sayfa['slug']) ?></code></td>
                    <td>
                        <span class="badge badge-<?= $sayfa['durum'] === 'yayinda' ? 'success' : 'secondary' ?>">
                            <?= $sayfa['durum'] === 'yayinda' ? 'Yayında' : 'Taslak' ?>
                        </span>
                    </td>
                    <td><?= (int) $sayfa['siralama'] ?></td>
                    <td><?= !empty($sayfa['updated_at']) ? date('d.m.Y H:i', strtotime($sayfa['updated_at'])) : '-' ?></td>
                    <td style="text-align:right;">
                        <a class="admin-btn admin-btn-sm admin-btn-secondary" href="<?= BASE_URL ?>/admin/cms-sayfa-onizleme.php?id=<?= (int) $sayfa['id'] ?>" target="_blank">Önizleme</a>
                        <a class="admin-btn admin-btn-sm admin-btn-primary" href="<?= BASE_URL ?>/admin/cms-sayfa-form.php?id=<?= (int) $sayfa['id'] ?>">Düzenle</a>
                        <form method="post" action="<?= BASE_URL ?>/admin/cms-sayfa-sil.php" style="display:inline;" onsubmit="return confirm('Sayfa silinsin mi?');">
                            <?= csrf_kod() ?>
                            <input type="hidden" name="id" value="<?= (int) $sayfa['id'] ?>">
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-secondary" style="background:#ef4444;color:#fff;">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
