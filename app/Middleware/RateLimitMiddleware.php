<?php

namespace App\Middleware;

/**
 * Rate Limit Middleware
 * 
 * Brute-force saldırılarına karşı istek sayısını sınırlar.
 * Dosya tabanlı — veritabanı veya Redis gerektirmez.
 * Shared hosting uyumlu.
 * 
 * Korunan endpointler:
 *   - Admin login (5 deneme / 15 dk)
 *   - Müşteri login (10 deneme / 15 dk)
 *   - Şifre sıfırlama (3 deneme / 30 dk)
 *   - Ödeme endpoint (10 deneme / 5 dk)
 *   - API (60 istek / 1 dk)
 * 
 * Kullanım:
 *   RateLimitMiddleware::check('admin_login');
 *   RateLimitMiddleware::check('password_reset');
 *   RateLimitMiddleware::check('payment');
 *
 * @package App\Middleware
 */
class RateLimitMiddleware
{
    /**
     * Rate limit kuralları
     * [max_attempts, window_seconds, block_seconds]
     */
    private const RULES = [
        'admin_login' => [5, 900, 1800],  // 5 deneme / 15dk → 30dk blok
        'customer_login' => [10, 900, 900],   // 10 deneme / 15dk → 15dk blok
        'password_reset' => [3, 1800, 3600],  // 3 deneme / 30dk → 1sa blok
        'payment' => [10, 300, 600],   // 10 deneme / 5dk → 10dk blok
        'api' => [60, 60, 120],   // 60 istek / 1dk → 2dk blok
    ];

    /** @var string Rate limit verilerinin saklanacağı dizin */
    private static $storageDir = null;

    /**
     * Rate limit kontrolü yap
     * 
     * @param string $action Kural adı (admin_login, payment vb.)
     * @param string|null $identifier IP veya kullanıcı ID (null = otomatik IP)
     * @return bool true = izin verildi, false = bloklandı
     */
    public static function check(string $action, ?string $identifier = null): bool
    {
        if (!isset(self::RULES[$action])) {
            return true; // Tanımsız kural — geç
        }

        [$maxAttempts, $windowSeconds, $blockSeconds] = self::RULES[$action];
        $identifier = $identifier ?? self::getClientIP();
        $key = $action . '_' . md5($identifier);

        $data = self::loadData($key);

        // Bloklanmış mı?
        if (!empty($data['blocked_until']) && time() < $data['blocked_until']) {
            $remaining = $data['blocked_until'] - time();
            self::respondBlocked($remaining, $action);
            return false;
        }

        // Eski kayıtları temizle
        $data['attempts'] = array_filter(
            $data['attempts'] ?? [],
            fn($time) => $time > (time() - $windowSeconds)
        );

        // Yeni deneme ekle
        $data['attempts'][] = time();

        // Limit aşıldı mı?
        if (count($data['attempts']) > $maxAttempts) {
            $data['blocked_until'] = time() + $blockSeconds;
            self::saveData($key, $data);

            error_log("V-Commerce RateLimit: $action blocked for IP $identifier");

            $remaining = $blockSeconds;
            self::respondBlocked($remaining, $action);
            return false;
        }

        self::saveData($key, $data);
        return true;
    }

    /**
     * Başarılı işlem sonrası sayacı sıfırla
     */
    public static function reset(string $action, ?string $identifier = null): void
    {
        $identifier = $identifier ?? self::getClientIP();
        $key = $action . '_' . md5($identifier);
        $file = self::getFilePath($key);

        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Kalan deneme sayısını döndür
     */
    public static function remainingAttempts(string $action, ?string $identifier = null): int
    {
        if (!isset(self::RULES[$action])) {
            return PHP_INT_MAX;
        }

        [$maxAttempts, $windowSeconds] = self::RULES[$action];
        $identifier = $identifier ?? self::getClientIP();
        $key = $action . '_' . md5($identifier);

        $data = self::loadData($key);
        $attempts = array_filter(
            $data['attempts'] ?? [],
            fn($time) => $time > (time() - $windowSeconds)
        );

        return max(0, $maxAttempts - count($attempts));
    }

    /**
     * Bloklanmış istek yanıtı
     */
    private static function respondBlocked(int $remainingSeconds, string $action): void
    {
        http_response_code(429);

        $minutes = ceil($remainingSeconds / 60);

        if (self::isAjax()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Retry-After: ' . $remainingSeconds);
            echo json_encode([
                'error' => "Çok fazla deneme. $minutes dakika sonra tekrar deneyin.",
                'retry_after' => $remainingSeconds
            ]);
            exit;
        }

        header('Retry-After: ' . $remainingSeconds);
        die('<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>Çok Fazla İstek</title>'
            . '<style>body{font-family:Inter,sans-serif;display:flex;justify-content:center;align-items:center;'
            . 'min-height:100vh;margin:0;background:#f8f9fa;color:#333}'
            . '.box{text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 2px 20px rgba(0,0,0,.08)}'
            . 'h1{font-size:1.5em;margin:0 0 10px;color:#f59e0b}p{color:#666}'
            . '.timer{font-size:2em;color:#e74c3c;margin:15px 0}</style></head>'
            . "<body><div class='box'><h1>⏱️ Çok Fazla Deneme</h1>"
            . "<p>Güvenlik nedeniyle geçici olarak bloklandınız.</p>"
            . "<div class='timer'>$minutes dakika</div>"
            . "<p>sonra tekrar deneyebilirsiniz.</p></div></body></html>");
    }

    // ── Dosya Tabanlı Depolama ─────────────────────────────────

    private static function getStorageDir(): string
    {
        if (self::$storageDir === null) {
            $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'ratelimit';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            self::$storageDir = $dir;
        }
        return self::$storageDir;
    }

    private static function getFilePath(string $key): string
    {
        return self::getStorageDir() . DIRECTORY_SEPARATOR . $key . '.json';
    }

    private static function loadData(string $key): array
    {
        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return ['attempts' => [], 'blocked_until' => 0];
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return ['attempts' => [], 'blocked_until' => 0];
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : ['attempts' => [], 'blocked_until' => 0];
    }

    private static function saveData(string $key, array $data): void
    {
        $file = self::getFilePath($key);
        @file_put_contents($file, json_encode($data), LOCK_EX);
    }

    // ── Yardımcılar ────────────────────────────────────────────

    private static function getClientIP(): string
    {
        // Proxy arkasındaysa gerçek IP
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
