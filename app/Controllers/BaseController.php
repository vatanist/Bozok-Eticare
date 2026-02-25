<?php
/**
 * Bozok E-Ticaret — BaseController
 *
 * Tüm Controller'ların ortak üst sınıfı.
 * Kısa yardımcı metotlar sağlar — framework yazmıyoruz,
 * sadece tekrarlayan kod'u merkezileştiriyoruz.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

class BaseController
{
    /**
     * Tema şablonunu yükler (gorunum() wrapper)
     *
     * @param string $template  Şablon adı (örn: 'sepet', 'urun-detay')
     * @param array  $data      Şablona gönderilecek veriler
     */
    protected function view(string $template, array $data = []): void
    {
        gorunum($template, $data);
    }

    /**
     * Yönlendirme yapar (git() wrapper)
     *
     * @param string $url  Hedef URL
     */
    protected function redirect(string $url): void
    {
        git($url);
    }

    /**
     * JSON yanıt döner (API endpoint'leri için)
     *
     * @param mixed $data    Yanıt verisi
     * @param int   $status  HTTP durum kodu
     * @param string $message  Opsiyonel mesaj
     */
    protected function json($data, int $status = 200, string $message = ''): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        $response = ['success' => ($status >= 200 && $status < 300)];
        if ($message) {
            $response['message'] = $message;
        }
        $response['data'] = $data;

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Request input'unu güvenli şekilde alır (GET veya POST)
     *
     * @param string $key      Anahtar
     * @param mixed  $default  Varsayılan değer
     * @return mixed
     */
    protected function input(string $key, $default = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $_POST[$key] ?? $_GET[$key] ?? $default;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * POST input'unu güvenli şekilde alır
     */
    protected function postInput(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * İstek metodu kontrolü
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * AJAX isteği mi kontrol eder
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Flash mesaj oluşturur
     */
    protected function flash(string $key, string $message, string $type = 'success'): void
    {
        mesaj($key, $message, $type);
    }
}
