<?php
/**
 * V-Commerce - Genel Konfigürasyon
 * v2.0: Bootstrap entegrasyonu, .env desteği
 *
 * Session güvenliği bootstrap/app.php'de yönetilir.
 */

// ── Bootstrap (Composer Autoload + .env) ───────────────────────
$bootstrapPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
if (file_exists($bootstrapPath)) {
    require_once $bootstrapPath;
}

// Hata raporlama — bootstrap'tan sonra (isDebug kontrolü)
if (function_exists('isDebug') && isDebug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Proje kök dizini
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// BASE_URL otomatik algıla (Derinlik algılamalı)
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = dirname($scriptPath);

// Eğer admin klasörü içerisindeysek, base bir üst dizindir.
if (strpos($scriptPath, '/admin/') !== false) {
    $base = dirname($scriptDir);
} else {
    $base = $scriptDir;
}

$base = ($base === '/' || $base === '\\' || $base === '.') ? '' : rtrim($base, '/');
define('BASE_URL', $base);

// Dosya yolları
define('UPLOADS_PATH', ROOT_PATH . 'data' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOADS_URL', BASE_URL . '/data/uploads');

// DB dahil et (autoload yoksa fallback)
if (!class_exists('Database')) {
    require_once ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'db.php';
}

// Functions dahil et (autoload files'tan yüklenmemişse)
if (!function_exists('e')) {
    require_once ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR . 'functions.php';
}

// Modül Motorunu Başlat (CMS Altyapısı)
$loaderPath = ROOT_PATH . 'moduller' . DIRECTORY_SEPARATOR . 'loader.php';
if (file_exists($loaderPath)) {
    require_once $loaderPath;
}

// Core sınıfları (autoload classmap'ten yüklenmediyse fallback)
$coreClasses = [
    'Router',
    'Auth',
    'Settings',
    'Product',
    'Variation',
    'Order',
    'Cargo',
    'Marketplace',
    'Affiliate',
    'Marketing',
    'ApiController',
    'Notification',
    'KurServisi',
    'TemaSozlesmesi',
    'SecenekServisi',
    'ModulSozlesmesi'
];
foreach ($coreClasses as $class) {
    if (!class_exists($class)) {
        $file = ROOT_PATH . 'core' . DIRECTORY_SEPARATOR . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// Adapter sınıfları
$adapterClasses = ['TrendyolAdapter', 'N11Adapter'];
foreach ($adapterClasses as $adapter) {
    if (!class_exists($adapter)) {
        $file = ROOT_PATH . 'core' . DIRECTORY_SEPARATOR . 'adapters' . DIRECTORY_SEPARATOR . $adapter . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// Veritabanı Örneğini İlklendir (Global Erişilebilirlik)
$database = Database::getInstance();
$db = $database->getConnection();

// ===================== BAŞLANGIÇ: AKTİF TEMA YÜKLEME =====================
if (function_exists('aktif_tema_ayarla')) {
    $aktif_tema = function_exists('ayar_getir') ? ayar_getir('active_theme', 'varsayilan') : 'varsayilan';
    aktif_tema_ayarla($aktif_tema);
}
// ===================== BİTİŞ: AKTİF TEMA YÜKLEME =====================

// Global Tracking (Ziyaretçi ve Referans Takibi)
if (function_exists('isAdmin') && !isAdmin()) {
    if (class_exists('Affiliate'))
        Affiliate::trackReferral();
    if (class_exists('Marketing'))
        Marketing::logVisitor();
}
