<?php
/**
 * Admin Temalar Sayfası
 */
requireAdmin();

$tema_yolu = $bozkurt['tema_yolu'];
$tum_temalar = [];

// ===================== BAŞLANGIÇ: TEMA METADATA KEŞİF =====================
if (is_dir($tema_yolu)) {
    $klasorler = array_diff(scandir($tema_yolu), array('..', '.'));
    foreach ($klasorler as $t_kod) {
        $tema_klasoru = $tema_yolu . '/' . $t_kod;
        if (!is_dir($tema_klasoru)) {
            continue;
        }

        $meta = [];
        $theme_json = $tema_klasoru . '/theme.json';
        $style_css = $tema_klasoru . '/style.css';

        if (file_exists($theme_json)) {
            $json = json_decode(file_get_contents($theme_json), true);
            if (is_array($json)) {
                $meta = [
                    'name' => $json['name'] ?? '',
                    'version' => $json['version'] ?? '',
                    'author' => $json['author'] ?? '',
                    'description' => $json['description'] ?? ''
                ];
            }
        }

        if (empty($meta) && file_exists($style_css)) {
            $meta = dosya_bilgisi_oku($style_css, [
                'name' => 'Theme Name',
                'version' => 'Version',
                'author' => 'Author',
                'description' => 'Description'
            ]);
        }

        $aktif_tema = function_exists('tema_adi_cozumle') ? tema_adi_cozumle($bozkurt['tema_adi']) : $bozkurt['tema_adi'];

        $tum_temalar[] = [
            'code' => $t_kod,
            'name' => $meta['name'] ?: $t_kod,
            'version' => $meta['version'] ?: '1.0.0',
            'author' => $meta['author'] ?: 'Bilinmiyor',
            'description' => $meta['description'] ?: 'Açıklama belirtilmemiş.',
            'screenshot' => file_exists($tema_klasoru . '/screenshot.png') ? BASE_URL . '/temalar/' . $t_kod . '/screenshot.png' : BASE_URL . '/assets/images/no-theme.png',
            'active' => ($aktif_tema == $t_kod)
        ];
    }
}
// ===================== BİTİŞ: TEMA METADATA KEŞİF =====================

$veriler = [
    'sayfa_basligi' => 'Temalar',
    'temalar' => $tum_temalar
];

gorunum('admin-temalar', $veriler);
