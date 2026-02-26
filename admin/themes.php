<?php
/**
 * Admin Temalar Sayfası
 */
requireAdmin();

$tema_yolu = $bozkurt['tema_yolu'];
$tum_temalar = [];

if (is_dir($tema_yolu)) {
    $klasorler = array_diff(scandir($tema_yolu), array('..', '.'));
    foreach ($klasorler as $t_kod) {
        $style_css = $tema_yolu . '/' . $t_kod . '/style.css';
        if (file_exists($style_css)) {
            $meta = dosya_bilgisi_oku($style_css, [
                'name' => 'Theme Name',
                'version' => 'Version',
                'author' => 'Author',
                'description' => 'Description'
            ]);

            $tum_temalar[] = [
                'code' => $t_kod,
                'name' => $meta['name'] ?: $t_kod,
                'version' => $meta['version'] ?: '1.0.0',
                'author' => $meta['author'] ?: 'Bilinmiyor',
                'description' => $meta['description'] ?: 'Açıklama belirtilmemiş.',
                'screenshot' => file_exists($tema_yolu . '/' . $t_kod . '/screenshot.png') ? BASE_URL . '/temalar/' . $t_kod . '/screenshot.png' : BASE_URL . '/assets/images/no-theme.png',
                'active' => ($bozkurt['tema_adi'] == $t_kod)
            ];
        }
    }
}

$veriler = [
    'sayfa_basligi' => 'Temalar',
    'temalar' => $tum_temalar
];

gorunum('admin-temalar', $veriler);
