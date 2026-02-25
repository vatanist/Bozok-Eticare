<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Yalnızca POST desteklenir.');
}

dogrula_csrf();

$aksiyon = (string) ($_POST['aksiyon'] ?? '');
$hedef = (string) ($_POST['donus'] ?? ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/'));

$girdi = [
    'aksiyon' => $aksiyon,
    'analitik' => !empty($_POST['analitik']),
    'pazarlama' => !empty($_POST['pazarlama']),
    'tercih' => !empty($_POST['tercih']),
];

if (class_exists('CerezYonetimi') && CerezYonetimi::tercihKaydet($girdi)) {
    mesaj('cerez', 'Çerez tercihlerin güncellendi.', 'success');
} else {
    mesaj('cerez', 'Çerez tercihi kaydedilemedi.', 'error');
}

if (!is_string($hedef) || $hedef === '' || str_contains($hedef, "\n") || str_contains($hedef, "\r")) {
    $hedef = BASE_URL . '/';
}

$host = $_SERVER['HTTP_HOST'] ?? '';
if (str_starts_with($hedef, 'http') && $host !== '' && parse_url($hedef, PHP_URL_HOST) !== $host) {
    $hedef = BASE_URL . '/';
}

git($hedef);
