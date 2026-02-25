<?php
/**
 * Admin Eklentiler & Modüller Sayfası
 */
requireAdmin();

// Tüm modülleri klasörlerden tara
$modul_yolu = $bozkurt['modul_yolu'];
$tum_moduller = [];

if (is_dir($modul_yolu)) {
    $kategoriler = array_diff(scandir($modul_yolu), array('..', '.', 'loader.php'));
    foreach ($kategoriler as $kategori) {
        $kat_yolu = $modul_yolu . '/' . $kategori;
        if (is_dir($kat_yolu)) {
            $modul_klasorleri = array_diff(scandir($kat_yolu), array('..', '.'));
            foreach ($modul_klasorleri as $m_kod) {
                $init = $kat_yolu . '/' . $m_kod . '/init.php';
                if (file_exists($init)) {
                    $meta = dosya_bilgisi_oku($init, [
                        'name' => 'Module Name',
                        'version' => 'Version',
                        'description' => 'Description',
                        'author' => 'Author'
                    ]);

                    // Veritabanındaki durumunu kontrol et
                    $db_status = Database::fetch("SELECT status FROM extensions WHERE type = 'module' AND code = ?", [$m_kod]);

                    $tum_moduller[] = [
                        'category' => $kategori,
                        'code' => $m_kod,
                        'name' => $meta['name'] ?: $m_kod,
                        'version' => $meta['version'] ?: '1.0.0',
                        'description' => $meta['description'] ?: 'Açıklama belirtilmemiş.',
                        'author' => $meta['author'] ?: 'Bilinmiyor',
                        'active' => ($db_status && $db_status['status'] == 1)
                    ];
                }
            }
        }
    }
}

// Görünüm verileri
$veriler = [
    'sayfa_basligi' => 'Eklentiler & Modüller',
    'moduller' => $tum_moduller
];

// Admin şablonunu çağır (Öneri: admin/gorunum fonksiyonu da olmalı ama şimdilik doğrudan)
gorunum_admin('admin-eklentiler', $veriler);
