<?php
/**
 * Bozok E-Ticaret — Dinamik Sitemap
 * Google ve diğer arama motorları için XML sitemap
 */
require_once 'config/config.php';

// Caching: 12 saatte bir yenile
$cacheFile = sys_get_temp_dir() . '/bozok_sitemap.xml';
$cacheTime = 12 * 3600;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    header('Content-Type: application/xml; charset=UTF-8');
    readfile($cacheFile);
    exit;
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$siteUrl = $scheme . '://' . $host . BASE_URL;

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$statik_sayfalar = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/urunler', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/ara', 'priority' => '0.5', 'changefreq' => 'weekly'],
];

foreach ($statik_sayfalar as $sayfa) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($siteUrl . $sayfa['loc']) . "</loc>\n";
    $xml .= "    <changefreq>{$sayfa['changefreq']}</changefreq>\n";
    $xml .= "    <priority>{$sayfa['priority']}</priority>\n";
    $xml .= "  </url>\n";
}

try {
    $kategoriler = Database::fetchAll("SELECT slug, updated_at FROM categories WHERE status = 1 ORDER BY id ASC");
    foreach ($kategoriler as $kategori) {
        $lastmod = date('Y-m-d', strtotime($kategori['updated_at'] ?? 'now'));
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($siteUrl . '/kategori/' . rawurlencode($kategori['slug'])) . "</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.8</priority>\n";
        $xml .= "  </url>\n";
    }
} catch (Throwable $hata) {
    error_log('Sitemap kategori hatası: ' . $hata->getMessage());
}

try {
    $urunler = Database::fetchAll("SELECT slug, updated_at FROM products WHERE status = 1 ORDER BY updated_at DESC LIMIT 5000");
    foreach ($urunler as $urun) {
        $lastmod = date('Y-m-d', strtotime($urun['updated_at'] ?? 'now'));
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($siteUrl . '/urun/' . rawurlencode($urun['slug'])) . "</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.7</priority>\n";
        $xml .= "  </url>\n";
    }
} catch (Throwable $hata) {
    error_log('Sitemap ürün hatası: ' . $hata->getMessage());
}

// ===================== BAŞLANGIÇ: CMS SAYFALARI SITEMAP =====================
try {
    $cms_sayfalari = Database::fetchAll("SELECT slug, updated_at FROM cms_pages WHERE durum = 'yayinda' ORDER BY siralama ASC, id DESC");
    foreach ($cms_sayfalari as $sayfa) {
        $lastmod = date('Y-m-d', strtotime($sayfa['updated_at'] ?? 'now'));
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($siteUrl . '/sayfa/' . rawurlencode($sayfa['slug'])) . "</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>monthly</changefreq>\n";
        $xml .= "    <priority>0.6</priority>\n";
        $xml .= "  </url>\n";
    }
} catch (Throwable $hata) {
    error_log('Sitemap cms hatası: ' . $hata->getMessage());
}
// ===================== BİTİŞ: CMS SAYFALARI SITEMAP =====================

$xml .= '</urlset>';

file_put_contents($cacheFile, $xml);
header('Content-Type: application/xml; charset=UTF-8');
echo $xml;
