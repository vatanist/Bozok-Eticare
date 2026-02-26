<?php

namespace App\Middleware;

/**
 * CSRF Middleware
 * 
 * Tüm POST/PUT/PATCH/DELETE isteklerinde CSRF token doğrulaması yapar.
 * Token session'da saklanır, her formda hidden input olarak eklenir.
 * 
 * Kullanım:
 *   CsrfMiddleware::init();          // Sayfa başında token oluştur
 *   CsrfMiddleware::field();         // Form içinde hidden input
 *   CsrfMiddleware::verify();        // POST handler'da doğrula
 *   CsrfMiddleware::headerToken();   // AJAX X-CSRF-TOKEN header için
 *
 * @package App\Middleware
 */
class CsrfMiddleware
{
    /** @var string Session anahtarı */
    private const SESSION_KEY = '_csrf_token';

    /** @var string POST/Header alanı adı */
    private const TOKEN_FIELD = '_csrf_token';

    /** @var string AJAX header adı */
    private const HEADER_NAME = 'X-CSRF-TOKEN';

    /** @var int Token geçerlilik süresi (saniye) — 0 = session boyunca */
    private const TOKEN_LIFETIME = 0;

    /**
     * Token oluştur veya mevcut olanı döndür
     */
    public static function init(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
            $_SESSION[self::SESSION_KEY . '_time'] = time();
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Mevcut token'ı döndür
     */
    public static function getToken(): string
    {
        return self::init();
    }

    /**
     * HTML hidden input döndür
     */
    public static function field(): string
    {
        $token = self::init();
        return '<input type="hidden" name="' . self::TOKEN_FIELD . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Meta tag döndür (AJAX istekleri için)
     */
    public static function metaTag(): string
    {
        $token = self::init();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * CSRF doğrulaması yap
     * 
     * @param bool $throwException true ise exception fırlat, false ise bool döndür
     * @return bool
     * @throws \RuntimeException doğrulama başarısızsa
     */
    public static function verify(bool $throwException = true): bool
    {
        // GET ve HEAD isteklerini atla
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        $token = self::getSubmittedToken();

        if (empty($token) || !self::isValidToken($token)) {
            if ($throwException) {
                http_response_code(403);
                if (self::isAjax()) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['error' => 'CSRF token geçersiz. Sayfayı yenileyip tekrar deneyin.']);
                    exit;
                }
                die(self::errorPage());
            }
            return false;
        }

        return true;
    }

    /**
     * Token'ı yenile (login/logout sonrası güvenlik için)
     */
    public static function regenerate(): string
    {
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SESSION_KEY . '_time']);
        return self::init();
    }

    /**
     * Gönderilen token'ı al (POST veya Header)
     */
    private static function getSubmittedToken(): ?string
    {
        // 1. POST body
        if (!empty($_POST[self::TOKEN_FIELD])) {
            return $_POST[self::TOKEN_FIELD];
        }

        // 2. AJAX header
        $headers = self::getRequestHeaders();
        if (!empty($headers[self::HEADER_NAME])) {
            return $headers[self::HEADER_NAME];
        }

        return null;
    }

    /**
     * Token geçerli mi?
     */
    private static function isValidToken(string $token): bool
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * AJAX isteği mi?
     */
    private static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Request header'larını al (Apache + nginx uyumlu)
     */
    private static function getRequestHeaders(): array
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[$name] = $value;
            }
        } else {
            // nginx fallback
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    $name = str_replace('_', '-', substr($key, 5));
                    $name = ucwords(strtolower($name), '-');
                    $headers[$name] = $value;
                }
            }
        }

        return $headers;
    }

    /**
     * CSRF hata sayfası
     */
    private static function errorPage(): string
    {
        return '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>Güvenlik Hatası</title>'
            . '<style>body{font-family:Inter,sans-serif;display:flex;justify-content:center;align-items:center;'
            . 'min-height:100vh;margin:0;background:#f8f9fa;color:#333}'
            . '.box{text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 2px 20px rgba(0,0,0,.08)}'
            . 'h1{font-size:1.5em;margin:0 0 10px;color:#e74c3c}p{color:#666}'
            . 'a{display:inline-block;margin-top:15px;padding:10px 24px;background:#3b82f6;color:#fff;'
            . 'text-decoration:none;border-radius:6px}</style></head>'
            . '<body><div class="box"><h1>🔒 Güvenlik Doğrulaması Başarısız</h1>'
            . '<p>CSRF token geçersiz veya süresi dolmuş.</p>'
            . '<a href="javascript:history.back()">← Geri Dön</a></div></body></html>';
    }
}
