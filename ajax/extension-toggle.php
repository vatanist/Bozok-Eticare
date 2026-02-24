<?php
/**
 * CMS Eklenti & Tema Değiştirici (AJAX)
 */
require_once '../config/config.php';
requireAdmin();

$type = temiz($_POST['type'] ?? ''); // module | theme
$code = temiz($_POST['code'] ?? '');
$status = intval($_POST['status'] ?? 0); // 0 | 1 (sadece modüller için)

if (!$code) {
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek.']);
    exit;
}

if ($type === 'module') {
    // Modül durumunu güncelle (Veya yoksa ekle)
    $check = Database::fetch("SELECT id FROM extensions WHERE type = 'module' AND code = ?", [$code]);
    if ($check) {
        Database::query("UPDATE extensions SET status = ? WHERE code = ?", [$status, $code]);
    } else {
        // Eğer veritabanında hiç yoksa (yeni klasör eklendiğinde)
        // Kategori bilgisini klasörden bulmamız lazım, şimdilik 'genel' diyelim veya init'ten oku
        Database::query("INSERT INTO extensions (type, category, code, status) VALUES ('module', 'genel', ?, ?)", [$code, $status]);
    }
    echo json_encode(['success' => true]);
} elseif ($type === 'theme') {
    // Aktif temayı değiştir
    // Settings tablosunda 'active_theme' anahtarını güncelle
    Database::query("UPDATE settings SET value = ? WHERE name = 'active_theme'", [$code]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Geçersiz tip.']);
}
