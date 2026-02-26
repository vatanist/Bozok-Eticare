<?php
/**
 * V-Commerce Bootstrap
 * 
 * Tüm uygulamanın tek başlatma noktası.
 * Autoload, .env, session güvenliği, hata yönetimi.
 * 
 * SHARED HOSTING UYUMLU: vendor/ olmadan da çalışır.
 * 
 * @package VCommerce
 * @version 2.0.0
 */

defined('VCOMMERCE_BOOTSTRAPPED') || define('VCOMMERCE_BOOTSTRAPPED', true);

// ── 1. Composer Autoload (opsiyonel) ───────────────────────────
$autoloadPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (file_exists($autoloadPath)) {
    try {
        require_once $autoloadPath;
        define('VCOMMERCE_COMPOSER', true);
    } catch (Throwable $e) {
        // vendor/ bozuk — graceful fail
        define('VCOMMERCE_COMPOSER', false);
        error_log('V-Commerce: vendor/ yüklenemedi — ' . $e->getMessage());
    }
} else {
    define('VCOMMERCE_COMPOSER', false);
}

// ── 2. .env Yükleme ───────────────────────────────────────────
$envPath = dirname(__DIR__);
$envFile = $envPath . DIRECTORY_SEPARATOR . '.env';

if (file_exists($envFile)) {
    if (VCOMMERCE_COMPOSER && class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($envPath);
            $dotenv->load();
            $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();
        } catch (Throwable $e) {
            error_log('V-Commerce: .env yükleme hatası — ' . $e->getMessage());
        }
    } else {
        // Manuel .env parser (Composer yok)
        $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#')
                    continue;
                if (strpos($line, '=') === false)
                    continue;

                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if (strlen($value) > 1 && $value[0] === '"' && substr($value, -1) === '"') {
                    $value = substr($value, 1, -1);
                }

                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// ── 3. Ortam Yardımcı Fonksiyonları ──────────────────────────

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        $lower = strtolower((string) $value);
        switch ($lower) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }

        return $value;
    }
}

if (!function_exists('isDebug')) {
    function isDebug(): bool
    {
        return env('APP_DEBUG', false) === true;
    }
}

if (!function_exists('isProduction')) {
    function isProduction(): bool
    {
        return env('APP_ENV', 'production') === 'production';
    }
}

// ── 4. Session Güvenliği ─────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $sessionOptions = [
        'cookie_httponly' => true,          // JS erişimi engelle
        'cookie_samesite' => 'Lax',         // CSRF koruması
        'use_strict_mode' => true,          // Geçersiz session ID reddet
        'use_only_cookies' => true,          // URL'den session ID alma
        'cookie_lifetime' => 0,             // Tarayıcı kapanınca sil
        'gc_maxlifetime' => 7200,          // 2 saat
    ];

    // HTTPS varsa cookie_secure aktif
    if ($isHttps) {
        $sessionOptions['cookie_secure'] = true;
    }

    session_start($sessionOptions);

    // Session fixation koruması — login sonrası ID yenile
    if (!isset($_SESSION['_initiated'])) {
        session_regenerate_id(true);
        $_SESSION['_initiated'] = true;
    }
}

// ── 5. Hata Yönetimi ────────────────────────────────────────

if (isDebug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        ini_set('error_log', $logDir . DIRECTORY_SEPARATOR . 'php_errors.log');
    }

    // Graceful error page — production'da beyaz ekran yerine
    set_exception_handler(function (Throwable $e) {
        error_log('V-Commerce Fatal: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

        if (!headers_sent()) {
            http_response_code(500);
        }

        $errorPage = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'temalar' . DIRECTORY_SEPARATOR . 'error500.html';
        if (file_exists($errorPage)) {
            readfile($errorPage);
        } else {
            echo '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8">'
                . '<title>Sunucu Hatası</title>'
                . '<style>body{font-family:Inter,sans-serif;display:flex;justify-content:center;align-items:center;'
                . 'min-height:100vh;margin:0;background:#f8f9fa;color:#333}'
                . '.box{text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 2px 20px rgba(0,0,0,.08)}'
                . 'h1{font-size:2em;margin:0 0 10px;color:#e74c3c}p{color:#666}</style></head>'
                . '<body><div class="box"><h1>⚠️ Bir Hata Oluştu</h1>'
                . '<p>Teknik ekibimiz bilgilendirildi. Lütfen daha sonra tekrar deneyin.</p></div></body></html>';
        }
        exit(1);
    });
}

// ── 6. Helper Dosyaları ─────────────────────────────────────
// Çekirdek fonksiyonlardan ayrılmış yardımcı modüller.
// functions.php sadece core fonksiyonları barındırır,
// ürün/sepet/auth/medya fonksiyonları burada yüklenir.

$helperDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR;
$helpers = ['text.php', 'auth.php', 'media.php', 'data.php'];

foreach ($helpers as $helper) {
    $helperFile = $helperDir . $helper;
    if (file_exists($helperFile)) {
        require_once $helperFile;
    }
}

