<?php
/**
 * Bozok E-Ticaret - Modül Dosya İşleyicisi (Router Bridge)
 * Modüllerin izole dosyalarını (callback, success vb.) dış dünyaya açar.
 * Örn: /modul-isleyici.php?modul=paytr&sayfa=callback
 *
 * ÖNEMLİ: Bu dosya CSRF kontrolünden MUAF.
 * PayTR gibi ödeme sağlayıcılar direkt POST atar — CSRF token göndermezler.
 * Güvenlik, her modülün kendi hash/signature doğrulamasıyla sağlanır.
 */

// CSRF'siz bootstrap — sadece config yükle
require_once 'config/config.php';

// Ödeme callback log (debug modunda)
if (function_exists('isDebug') && isDebug()) {
    error_log(sprintf(
        'Bozok E-Ticaret ModulHandler: %s %s?%s [IP: %s]',
        $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        $_SERVER['SCRIPT_NAME'] ?? '',
        $_SERVER['QUERY_STRING'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ));
}

// ── GİRDİ DOĞRULAMA — Whitelist Tabanlı ──────────────────────

// 1. Modül kodu: sadece alfanumerik + alt çizgi (DB sorgusu için)
$modul_kod = $_GET['modul'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_]{1,50}$/', $modul_kod)) {
    http_response_code(400);
    die('Geçersiz modül kodu.');
}

// 2. Sayfa adı: sadece alfanumerik + tire + alt çizgi (dosya yolu için)
$sayfa = $_GET['sayfa'] ?? 'index';
if (!preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $sayfa)) {
    http_response_code(400);
    die('Geçersiz sayfa adı.');
}

// 3. Modülü veritabanından doğrula (prepared statement — SQL injection yok)
$modul = Database::fetch(
    "SELECT code, category FROM extensions WHERE code = ? AND (type = 'payment' OR type = 'module') LIMIT 1",
    [$modul_kod]
);

if (!$modul) {
    http_response_code(404);
    die('Modül bulunamadı.');
}

// 4. category ve code DB'den geliyor — ek güvenlik olarak whitelist kontrolü
if (
    !preg_match('/^[a-zA-Z0-9_-]+$/', $modul['category']) ||
    !preg_match('/^[a-zA-Z0-9_-]+$/', $modul['code'])
) {
    http_response_code(500);
    error_log('Bozok E-Ticaret: Veritabanında geçersiz modül verisi: ' . $modul['code']);
    die('Sistem hatası.');
}

// 5. Dosya yolu oluştur ve çift doğrulama yap
$dosya_yolu = ROOT_PATH . 'moduller' . DIRECTORY_SEPARATOR
    . $modul['category'] . DIRECTORY_SEPARATOR
    . $modul['code'] . DIRECTORY_SEPARATOR
    . $sayfa . '.php';

// 6. realpath ile path traversal'ı kesin engelle
// Dosya, moduller/ dizini dışına çıkamaz
$modullerDir = realpath(ROOT_PATH . 'moduller');
$gercekYol = realpath($dosya_yolu);

if ($gercekYol === false || !str_starts_with($gercekYol, $modullerDir)) {
    http_response_code(404);
    die('Modül sayfası bulunamadı.');
}

// 7. Güvenli — dosyayı yükle
require_once $gercekYol;
