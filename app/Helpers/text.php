<?php
/**
 * Bozok E-Ticaret — Metin Yardımcıları
 *
 * Metin temizleme, slug oluşturma, kısaltma, fiyat biçimlendirme.
 *
 * @package App\Helpers
 */

// ── Çekirdek Fonksiyonlar ──

/**
 * Güvenli Metin Çıktısı (XSS Koruması)
 */
function temiz($metin)
{
    return htmlspecialchars($metin ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * URL Dostu Metin Oluşturucu (Slug)
 */
function link_yap($metin)
{
    $tr = [
        'ş' => 's',
        'Ş' => 'S',
        'ı' => 'i',
        'İ' => 'I',
        'ç' => 'c',
        'Ç' => 'C',
        'ü' => 'u',
        'Ü' => 'U',
        'ö' => 'o',
        'Ö' => 'O',
        'ğ' => 'g',
        'Ğ' => 'G'
    ];
    $metin = strtr($metin, $tr);
    $metin = strtolower(trim($metin));
    $metin = preg_replace('/[^a-z0-9\s-]/', '', $metin);
    $metin = preg_replace('/[\s-]+/', '-', $metin);
    return rtrim($metin, '-');
}

/**
 * Fiyatı Para Birimi Formatına Sokar
 */
function para_yaz($miktar)
{
    return number_format((float) $miktar, 2, ',', '.') . ' ₺';
}

/**
 * Uzun metinleri kısaltır.
 */
function kirp($metin, $uzunluk = 100)
{
    if (mb_strlen($metin) > $uzunluk) {
        return mb_substr($metin, 0, $uzunluk) . '...';
    }
    return $metin;
}

/**
 * Sayfalama verisi oluşturur.
 */
function sayfalama($toplam, $limit, $mevcut_sayfa)
{
    $toplam_sayfa = ceil($toplam / $limit);
    $sayfa = max(1, min($toplam_sayfa, $mevcut_sayfa));
    $offset = ($sayfa - 1) * $limit;

    return [
        'toplam' => $toplam,
        'sayfa' => $sayfa,
        'limit' => $limit,
        'toplam_sayfa' => $toplam_sayfa,
        'offset' => $offset
    ];
}

// ── Uyumluluk Alias'ları ──

/** @deprecated v2.0'da kaldırılacak. temiz() kullanın. */
function e($t)
{
    return temiz($t);
}

/** @deprecated v2.0'da kaldırılacak. link_yap() kullanın. */
function slugify($t)
{
    return link_yap($t);
}

/** @deprecated v2.0'da kaldırılacak. para_yaz() kullanın. */
function formatPrice($p)
{
    return para_yaz($p);
}

/** @deprecated v2.0'da kaldırılacak. kirp() kullanın. */
function truncate($m, $u = 100)
{
    return kirp($m, $u);
}

/** @deprecated v2.0'da kaldırılacak. sayfalama() kullanın. */
function paginate($t, $l, $p)
{
    return sayfalama($t, $l, $p);
}
