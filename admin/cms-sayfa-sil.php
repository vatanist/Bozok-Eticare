<?php
/**
 * Admin - Kurumsal CMS Sayfa Silme
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

if (class_exists('Auth') && !Auth::can('manage_cms')) {
    http_response_code(403);
    die('Bu alan için CMS yönetim yetkisi gereklidir.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    git('/admin/cms-sayfalar.php');
}

dogrula_csrf();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    mesaj('cms', 'Geçersiz sayfa kimliği.', 'error');
    git('/admin/cms-sayfalar.php');
}

$sayfa = Database::fetch("SELECT id, title FROM cms_pages WHERE id = ?", [$id]);
if (!$sayfa) {
    mesaj('cms', 'Sayfa bulunamadı.', 'error');
    git('/admin/cms-sayfalar.php');
}

Database::query("DELETE FROM cms_pages WHERE id = ?", [$id]);
mesaj('cms', '"' . temiz($sayfa['title']) . '" sayfası silindi.', 'success');
git('/admin/cms-sayfalar.php');
