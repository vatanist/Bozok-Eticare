<?php
/**
 * V-Commerce — Dinamik Sitemap
 * Google ve diğer arama motorları için XML sitemap
 */
require_once 'config/config.php';

// Caching: 12 saatte bir yenile
$cacheFile = sys_get_temp_dir() . '/vcommerce_sitemap.xml';
$cacheTime = 12 * 3600;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    header('Content-Type: application/xml; charset=UTF-8');
    readfile($cacheFile);
    exit;
}

// Site base URL — admin ayarından veya otomatik algılama
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$siteUrl = $scheme . '://' . $host . BASE_URL;

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Statik sayfalar
$staticPages = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/products.php', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/search.php', 'priority' => '0.5', 'changefreq' => 'weekly'],
];

foreach ($staticPages as $page) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($siteUrl . $page['loc']) . "</loc>\n";
    $xml .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
    $xml .= "    <priority>{$page['priority']}</priority>\n";
    $xml .= "  </url>\n";
}

// Kategoriler
try {
    $categories = Database::fetchAll("SELECT slug, updated_at FROM categories WHERE status = 1 ORDER BY id ASC");
    foreach ($categories as $cat) {
        $lastmod = date('Y-m-d', strtotime($cat['updated_at'] ?? 'now'));
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($siteUrl . '/products.php?category=' . rawurlencode($cat['slug'])) . "</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.8</priority>\n";
        $xml .= "  </url>\n";
    }
} catch (Exception $e) {
}

// Ürünler
try {
    $products = Database::fetchAll(
        "SELECT slug, updated_at FROM products WHERE status = 1 ORDER BY updated_at DESC LIMIT 5000"
    );
    foreach ($products as $p) {
        $lastmod = date('Y-m-d', strtotime($p['updated_at'] ?? 'now'));
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($siteUrl . '/product-detail.php?slug=' . rawurlencode($p['slug'])) . "</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.7</priority>\n";
        $xml .= "  </url>\n";
    }
} catch (Exception $e) {
}

$xml .= '</urlset>';

// Cache'e yaz
file_put_contents($cacheFile, $xml);

header('Content-Type: application/xml; charset=UTF-8');
echo $xml;
