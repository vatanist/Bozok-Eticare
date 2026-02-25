<?php
/**
 * Admin Temalar Sayfası
 */
requireAdmin();

$tema_yolu = $bozkurt['tema_yolu'];
$tum_temalar = [];

// ===================== BAŞLANGIÇ: TEMA METADATA VE SÖZLEŞME DOĞRULAMA =====================
if (is_dir($tema_yolu)) {
    $klasorler = array_diff(scandir($tema_yolu), array('..', '.'));
    foreach ($klasorler as $t_kod) {
        $tema_klasoru = $tema_yolu . '/' . $t_kod;
        if (!is_dir($tema_klasoru)) {
            continue;
        }

        $aktif_tema = function_exists('tema_adi_cozumle') ? tema_adi_cozumle($bozkurt['tema_adi']) : $bozkurt['tema_adi'];

        if (class_exists('TemaSozlesmesi')) {
            $meta = TemaSozlesmesi::temaMetadataOku($tema_klasoru);
            $dogrulama = TemaSozlesmesi::temaSozlesmesiniDogrula($tema_klasoru);
        } else {
            $meta = [
                'name' => $t_kod,
                'version' => '1.0.0',
                'author' => 'Bilinmiyor',
                'description' => 'Açıklama belirtilmemiş.',
                'kaynak' => 'yok',
            ];
            $dogrulama = ['gecerli' => true, 'hatalar' => []];
        }

        $tum_temalar[] = [
            'code' => $t_kod,
            'name' => $meta['name'],
            'version' => $meta['version'],
            'author' => $meta['author'],
            'description' => $meta['description'],
            'metadata_kaynagi' => $meta['kaynak'],
            'dogrulama_hatalari' => $dogrulama['hatalar'] ?? [],
            'dogrulama_uyarilari' => $dogrulama['uyarilar'] ?? [],
            'gecerli' => $dogrulama['gecerli'] ?? false,
            'screenshot' => file_exists($tema_klasoru . '/screenshot.png') ? BASE_URL . '/temalar/' . $t_kod . '/screenshot.png' : BASE_URL . '/assets/images/no-theme.png',
            'active' => ($aktif_tema == $t_kod)
        ];
    }
}
// ===================== BİTİŞ: TEMA METADATA VE SÖZLEŞME DOĞRULAMA =====================

$veriler = [
    'sayfa_basligi' => 'Temalar',
    'temalar' => $tum_temalar
];

gorunum_admin('admin-temalar', $veriler);
