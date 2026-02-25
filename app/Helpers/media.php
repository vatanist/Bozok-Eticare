<?php
/**
 * Bozok E-Ticaret — Medya Yardımcıları
 *
 * Resim URL çözümleme, güvenli resim yükleme, tema dosya linkleri.
 *
 * @package App\Helpers
 */

// ── Çekirdek Fonksiyonlar ──

function resim_linki($yol)
{
    if (empty($yol)) {
        return rtrim(BASE_URL, '/') . '/assets/images/no-image.png';
    }
    if (str_starts_with($yol, 'http')) {
        return $yol;
    }
    return rtrim(BASE_URL, '/') . '/uploads/' . ltrim($yol, '/');
}

/**
 * Güvenli Resim Yükleme Fonksiyonu
 */
function resim_yukle($file, $folder = 'products')
{
    $targetDir = UPLOADS_PATH . $folder . DIRECTORY_SEPARATOR;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Kontroller
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['success' => false, 'error' => 'Dosya bir resim değil.'];
    }

    if ($file['size'] > 5000000) {
        return ['success' => false, 'error' => 'Dosya çok büyük (Max 5MB).'];
    }

    if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
        return ['success' => false, 'error' => 'Sadece JPG, JPEG, PNG, GIF ve WEBP yüklenebilir.'];
    }

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'filename' => $folder . '/' . $fileName];
    }

    return ['success' => false, 'error' => 'Dosya yükleme hatası.'];
}

// ── Uyumluluk Alias'ları ──

/** @deprecated v2.0'da kaldırılacak. resim_linki() kullanın. */
function getImageUrl($y)
{
    return resim_linki($y);
}

/** @deprecated v2.0'da kaldırılacak. resim_yukle() kullanın. */
function uploadImageSecure($f, $fo = 'products')
{
    return resim_yukle($f, $fo);
}
