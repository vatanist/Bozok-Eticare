<?php

/**
 * API Master Controller
 * Bozok E-Ticaret Enterprise RESTful Interface
 */
class ApiController
{
    /**
     * JSON yanıt döner ve scripti sonlandırır.
     */
    public static function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Hata yanıtı döner.
     */
    public static function error($message, $statusCode = 400, $errors = [])
    {
        return self::json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Başarı yanıtı döner.
     */
    public static function success($message, $data = [], $statusCode = 200)
    {
        return self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * API Token yetkilendirmesi yapar.
     */
    public static function authenticate()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            // Mock: Sabit token veya DB kontrolü
            if ($token === 'VCOMMERCE_ENTERPRISE_TOKEN_2026') {
                return true;
            }
        }

        return self::error('Yetkisiz erişim. Geçersiz API Token.', 401);
    }
}
