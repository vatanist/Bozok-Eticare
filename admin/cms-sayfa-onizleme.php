<?php
/**
 * Admin - Kurumsal CMS Sayfa Önizleme
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

if (class_exists('Auth') && !Auth::can('manage_cms')) {
    http_response_code(403);
    die('Bu alan için CMS yönetim yetkisi gereklidir.');
}

function cms_onizleme_icerik_temizle(string $icerik): string
{
    $izinli = '<p><a><strong><em><b><i><u><ul><ol><li><h1><h2><h3><h4><blockquote><br><hr><table><thead><tbody><tr><th><td><img>';
    return strip_tags($icerik, $izinli);
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    die('Sayfa bulunamadı.');
}

$sayfa = Database::fetch("SELECT * FROM cms_pages WHERE id = ?", [$id]);
if (!$sayfa) {
    http_response_code(404);
    die('Sayfa bulunamadı.');
}

$sayfa['icerik_guvenli'] = cms_onizleme_icerik_temizle((string) ($sayfa['icerik'] ?? ''));

$veriler = [
    'sayfa_basligi' => !empty($sayfa['meta_title']) ? $sayfa['meta_title'] : $sayfa['title'],
    'sayfa' => $sayfa,
    'meta_desc' => !empty($sayfa['meta_description']) ? $sayfa['meta_description'] : mb_substr(strip_tags($sayfa['icerik_guvenli']), 0, 160),
    'canonical_url' => !empty($sayfa['canonical_url']) ? $sayfa['canonical_url'] : url('sayfa/' . $sayfa['slug']),
];

gorunum($sayfa['sablon'] ?: 'sayfa', $veriler);
