<?php
/**
 * CMS Modül Yükleyici (Module Loader) v2
 * Profesyonel Aktif/Pasif Kontrollü Yükleyici
 */

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    die("Doğrudan erişim engellendi.");
}

if (!isset($bozkurt) || !is_array($bozkurt)) {
    return;
}

$modul_yolu = $bozkurt['modul_yolu'];

// Veritabanından aktif modülleri çek
$aktif_moduller = Database::fetchAll("SELECT * FROM extensions WHERE type = 'module' AND status = 1");

foreach ($aktif_moduller as $m) {
    // Modül dizini: moduller/{kategori}/{kod}/
    $modul_dir = $modul_yolu . '/' . $m['category'] . '/' . $m['code'];
    $init_dosyasi = $modul_dir . '/init.php';

    if (file_exists($init_dosyasi)) {
        // Modül bilgilerini oku (Opsiyonel: Global bir modül listesinde tutulabilir)
        $m['metadata'] = dosya_bilgisi_oku($init_dosyasi, [
            'name' => 'Module Name',
            'version' => 'Version',
            'description' => 'Description',
            'author' => 'Author'
        ]);

        // Modülü başlat
        require_once $init_dosyasi;
    }
}
