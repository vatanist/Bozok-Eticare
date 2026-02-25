<?php
/**
 * Legacy Bridge — Kurumsal CMS sayfa router köprüsü
 */
require_once __DIR__ . '/config/config.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '' || preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
    http_response_code(404);
    gorunum('hata/404', ['sayfa_basligi' => 'Sayfa Bulunamadı']);
    exit;
}

$controller = new \App\Controllers\PageController();
$controller->show($slug);
