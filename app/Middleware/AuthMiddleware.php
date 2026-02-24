<?php

namespace App\Middleware;

/**
 * Auth Middleware
 * 
 * Admin ve müşteri oturum kontrolünü merkezi olarak yönetir.
 * Her korumalı sayfa bu middleware'i çağırır.
 * 
 * Kullanım:
 *   AuthMiddleware::requireAdmin();     // Admin girişi zorunlu
 *   AuthMiddleware::requireCustomer();  // Müşteri girişi zorunlu
 *   AuthMiddleware::currentUser();      // Mevcut kullanıcı bilgisi
 *   AuthMiddleware::isAdmin();          // Admin mi?
 *   AuthMiddleware::isLoggedIn();       // Herhangi bir giriş var mı?
 *
 * @package App\Middleware
 */
class AuthMiddleware
{
    /**
     * Admin girişi zorunlu — yoksa login'e yönlendir
     */
    public static function requireAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['admin_id'])) {
            $loginUrl = self::getBaseUrl() . '/admin/login.php';

            if (self::isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Oturum süresi dolmuş. Lütfen giriş yapın.', 'redirect' => $loginUrl]);
                exit;
            }

            header('Location: ' . $loginUrl);
            exit;
        }

        // Session timeout kontrolü (2 saat)
        $timeout = 7200;
        if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity']) > $timeout) {
            self::logout();
            $loginUrl = self::getBaseUrl() . '/admin/login.php?expired=1';
            header('Location: ' . $loginUrl);
            exit;
        }

        $_SESSION['admin_last_activity'] = time();
    }

    /**
     * Müşteri girişi zorunlu
     */
    public static function requireCustomer(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['customer_id'])) {
            $returnUrl = urlencode($_SERVER['REQUEST_URI'] ?? '/');
            header('Location: ' . self::getBaseUrl() . '/giris?redirect=' . $returnUrl);
            exit;
        }
    }

    /**
     * Admin mi?
     */
    public static function isAdmin(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    /**
     * Müşteri girişi var mı?
     */
    public static function isCustomer(): bool
    {
        return !empty($_SESSION['customer_id']);
    }

    /**
     * Herhangi bir giriş var mı?
     */
    public static function isLoggedIn(): bool
    {
        return self::isAdmin() || self::isCustomer();
    }

    /**
     * Mevcut admin kullanıcı bilgisi
     */
    public static function currentAdmin(): ?array
    {
        if (empty($_SESSION['admin_id'])) {
            return null;
        }

        // Sadece gerektiğinde DB'den çek, session'da cache'le
        if (!isset($_SESSION['_admin_cache']) || $_SESSION['_admin_cache']['id'] != $_SESSION['admin_id']) {
            if (class_exists('Database')) {
                $user = \Database::fetch(
                    "SELECT id, username, email, role FROM users WHERE id = ? AND role = 'admin' LIMIT 1",
                    [$_SESSION['admin_id']]
                );
                $_SESSION['_admin_cache'] = $user ?: null;
            }
        }

        return $_SESSION['_admin_cache'] ?? null;
    }

    /**
     * Mevcut müşteri bilgisi
     */
    public static function currentCustomer(): ?array
    {
        if (empty($_SESSION['customer_id'])) {
            return null;
        }

        if (!isset($_SESSION['_customer_cache']) || $_SESSION['_customer_cache']['id'] != $_SESSION['customer_id']) {
            if (class_exists('Database')) {
                $customer = \Database::fetch(
                    "SELECT id, name, email, phone FROM customers WHERE id = ? AND status = 1 LIMIT 1",
                    [$_SESSION['customer_id']]
                );
                $_SESSION['_customer_cache'] = $customer ?: null;
            }
        }

        return $_SESSION['_customer_cache'] ?? null;
    }

    /**
     * Güvenli çıkış
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
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
     * BASE_URL al
     */
    private static function getBaseUrl(): string
    {
        return defined('BASE_URL') ? BASE_URL : '';
    }
}
