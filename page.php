<?php
/**
 * Bozkurt Core - Statik Sayfa Görüntüleme
 */
require_once __DIR__ . '/config/config.php';

$slug = temiz($_GET['slug'] ?? '');

// Geçersiz slug
if (!$slug || !preg_match('/^[a-z0-9\-]+$/', $slug)) {
    http_response_code(404);
    gorunum('hata/404', ['sayfa_basligi' => 'Sayfa Bulunamadı']);
    exit;
}

// Sayfayı Çek
$sayfa = Database::fetch(
    "SELECT * FROM pages WHERE slug = ? AND status = 1",
    [$slug]
);

if (!$sayfa) {
    http_response_code(404);
    gorunum('hata/404', ['sayfa_basligi' => 'Sayfa Bulunamadı']);
    exit;
}

// SEO ve Veriler
$veriler = [
    'sayfa_basligi' => !empty($sayfa['meta_title']) ? $sayfa['meta_title'] : $sayfa['title'],
    'sayfa' => $sayfa,
    'meta_description' => !empty($sayfa['meta_description']) ? $sayfa['meta_description'] : mb_substr(strip_tags($sayfa['content']), 0, 160)
];

gorunum('sayfa', $veriler);
