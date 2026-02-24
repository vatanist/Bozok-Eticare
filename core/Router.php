<?php
/**
 * V-Commerce - Merkezi Router Sınıfı
 *
 * Güvenlik:
 *   - URI uzunluk limiti (2048 → 414)
 *   - Parametre uzunluk limiti (200 karakter)
 *   - Slug whitelist pattern, id sadece rakam
 *   - BASE_URL güvenli stripping
 *   - SEO-uyumlu 404 + REST-uyumlu 405
 *
 * Mimari:
 *   - Statik route'lar parametrelilerden ÖNCE eşleşir (öncelik sistemi)
 *   - 405 Method Not Allowed + Allow header (REST uyumlu)
 *   - Middleware hook desteği (route bazlı + grup bazlı)
 *
 * @package VCommerce
 * @version 2.1.0
 */
class Router
{
    private static $routes = [];
    private static $prefix = '';
    private static $insertionOrder = 0;
    private static $groupMiddleware = [];

    /** @var int Maksimum URI uzunluğu */
    private const MAX_URI_LENGTH = 2048;

    /** @var int Maksimum parametre uzunluğu */
    private const MAX_PARAM_LENGTH = 200;

    // ─── HTTP Metotları ──────────────────────────────────────────

    /**
     * GET rotası ekler
     * @param string $path  Route yolu
     * @param callable $callback  Handler
     * @param array $middleware  Opsiyonel middleware listesi (class adları)
     */
    public static function get($path, $callback, array $middleware = [])
    {
        self::addRoute('GET', $path, $callback, $middleware);
    }

    public static function post($path, $callback, array $middleware = [])
    {
        self::addRoute('POST', $path, $callback, $middleware);
    }

    public static function put($path, $callback, array $middleware = [])
    {
        self::addRoute('PUT', $path, $callback, $middleware);
    }

    public static function delete($path, $callback, array $middleware = [])
    {
        self::addRoute('DELETE', $path, $callback, $middleware);
    }

    /**
     * Tüm HTTP metotlarını kabul eden route
     */
    public static function any($path, $callback, array $middleware = [])
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
            self::addRoute($method, $path, $callback, $middleware);
        }
    }

    // ─── Gruplama + Middleware ────────────────────────────────────

    /**
     * Route grubuna önek ve opsiyonel middleware ekler
     *
     * Kullanım:
     *   Router::group('/hesabim', function() { ... }, ['AuthMiddleware']);
     *   Router::group('/api/v1', function() { ... });
     */
    public static function group($prefix, $callback, array $middleware = [])
    {
        $oldPrefix = self::$prefix;
        $oldMiddleware = self::$groupMiddleware;

        self::$prefix .= $prefix;
        self::$groupMiddleware = array_merge(self::$groupMiddleware, $middleware);

        $callback();

        self::$prefix = $oldPrefix;
        self::$groupMiddleware = $oldMiddleware;
    }

    // ─── Dahili Route Ekleme ─────────────────────────────────────

    /**
     * Rotayı listeye ekler
     *
     * Öncelik sistemi:
     *   priority 0 = statik route (parametre içermiyor, örn: /urun/arsiv)
     *   priority 1 = parametreli route (parametre içeriyor, örn: /urun/{slug})
     *
     * dispatch() sırasında düşük priority önce kontrol edilir.
     * Bu sayede /urun/arsiv her zaman /urun/{slug}'dan ÖNCE eşleşir.
     */
    private static function addRoute($method, $path, $callback, array $middleware = [])
    {
        $path = self::$prefix . $path;
        $path = rtrim($path, '/');
        if (empty($path))
            $path = '/';

        // Statik mi parametreli mi?
        $isParametric = (strpos($path, '{') !== false);

        // Grup middleware + route-level middleware birleştir
        $allMiddleware = array_merge(self::$groupMiddleware, $middleware);

        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'pattern' => self::preparePattern($path),
            'priority' => $isParametric ? 1 : 0,
            'order' => self::$insertionOrder++,
            'middleware' => $allMiddleware,
        ];
    }

    /**
     * Rota desenini regex'e dönüştürür
     *
     * Özel pattern'ler:
     *   {id}   → [0-9]+           (sadece rakam)
     *   {slug} → [a-z0-9][a-z0-9-]*[a-z0-9] (URL-safe)
     *   {*}    → [^/]+            (genel parametre)
     */
    private static function preparePattern($path)
    {
        // {id} → sadece rakam
        $pattern = preg_replace('/\{id\}/', '(?P<id>[0-9]+)', $path);

        // {slug} → URL-safe slug
        $pattern = preg_replace('/\{slug\}/', '(?P<slug>[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)', $pattern);

        // Diğer parametreler → genel
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);

        return '#^' . $pattern . '$#u';
    }

    // ─── Dispatch ────────────────────────────────────────────────

    /**
     * Gelen isteği rotaya yönlendirir
     */
    public static function dispatch()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // ── URI Uzunluk Limiti ──
        if (strlen($uri) > self::MAX_URI_LENGTH) {
            http_response_code(414);
            die('URI çok uzun.');
        }

        $url = parse_url($uri, PHP_URL_PATH) ?: '/';

        // ── BASE_URL güvenli stripping ──
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        if ($baseUrl && $baseUrl !== '/' && strpos($url, $baseUrl) === 0) {
            $afterBase = substr($url, strlen($baseUrl));
            if ($afterBase === '' || $afterBase === false || $afterBase[0] === '/') {
                $url = $afterBase ?: '/';
            }
        }

        $url = rtrim($url, '/');
        if (empty($url))
            $url = '/';

        // ── Route'ları önceliğe göre sırala (STABİL SORT) ──
        // priority 0 (statik) → priority 1 (parametreli)
        // Aynı priority'de insertion order korunur (deterministik)
        $sorted = self::$routes;
        usort($sorted, function ($a, $b) {
            $cmp = $a['priority'] <=> $b['priority'];
            return $cmp !== 0 ? $cmp : $a['order'] <=> $b['order'];
        });

        // ── Route Eşleştirme ──
        $allowedMethods = []; // 405 için biriktir

        foreach ($sorted as $route) {
            if (preg_match($route['pattern'], $url, $matches)) {
                // URL eşleşti — metot kontrolü
                if ($route['method'] !== $method) {
                    $allowedMethods[] = $route['method'];
                    continue;
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Parametre uzunluk kontrolü
                foreach ($params as $key => $value) {
                    if (strlen($value) > self::MAX_PARAM_LENGTH) {
                        http_response_code(400);
                        die('Geçersiz parametre.');
                    }
                    $params[$key] = urldecode($value);
                }

                // ── Middleware Zincirini Çalıştır ──
                if (!empty($route['middleware'])) {
                    foreach ($route['middleware'] as $mw) {
                        $result = self::runMiddleware($mw);
                        if ($result === false) {
                            return; // Middleware engelledi (redirect/403/401 zaten gönderildi)
                        }
                    }
                }

                // ViewNotFoundException → 404'e dönüştür
                try {
                    return self::callCallback($route['callback'], $params);
                } catch (\App\Exceptions\ViewNotFoundException $e) {
                    error_log('V-Commerce: ' . $e->getMessage());
                    self::notFound();
                }
            }
        }

        // ── URL eşleşti ama metot yanlış → 405 ──
        if (!empty($allowedMethods)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($allowedMethods)));
            header('X-Robots-Tag: noindex');
            die('405 - Bu URL için ' . implode('/', array_unique($allowedMethods)) . ' metodu kullanın.');
        }

        // ── Hiçbir route eşleşmedi → 404 ──
        self::notFound();
    }

    // ─── Middleware Runner ────────────────────────────────────────

    /**
     * Middleware'i çalıştırır
     *
     * Desteklenen formatlar:
     *   'AuthMiddleware'                → App\Middleware\AuthMiddleware::handle()
     *   'CsrfMiddleware'               → App\Middleware\CsrfMiddleware::handle()
     *   'RateLimitMiddleware:login'     → handle('login') — parametre desteği
     *
     * @return bool|null  false = engellendi, true/null = devam et
     */
    private static function runMiddleware($middleware)
    {
        // Parametre ayrıştırma (Middleware:param formatı)
        $param = null;
        if (strpos($middleware, ':') !== false) {
            [$middleware, $param] = explode(':', $middleware, 2);
        }

        // Tam namespace ile dene
        $fqcn = 'App\\Middleware\\' . $middleware;

        if (class_exists($fqcn) && method_exists($fqcn, 'handle')) {
            return $param !== null
                ? $fqcn::handle($param)
                : $fqcn::handle();
        }

        // Doğrudan sınıf adı ile dene (namespace'siz)
        if (class_exists($middleware) && method_exists($middleware, 'handle')) {
            return $param !== null
                ? $middleware::handle($param)
                : $middleware::handle();
        }

        // Middleware bulunamadı → FAIL-CLOSED (güvenlik önceliği)
        // Production'da middleware typo olursa route korumasız kalmaz
        error_log("V-Commerce Router: Middleware bulunamadı: {$middleware} — istek engellendi (fail-closed)");

        // Debug modunda detaylı bilgi göster
        if (function_exists('isDebug') && isDebug()) {
            http_response_code(500);
            die("Middleware yüklenemedi: {$middleware}");
        }

        // Production: sessizce engelle, 403 dön
        http_response_code(403);
        return false;
    }

    // ─── 404 Handler ─────────────────────────────────────────────

    /**
     * 404 yanıtı — SEO-uyumlu
     */
    public static function notFound(): void
    {
        http_response_code(404);
        header('X-Robots-Tag: noindex');

        if (defined('ROOT_PATH')) {
            $custom404 = ROOT_PATH . 'temalar' . DIRECTORY_SEPARATOR . '404.php';
            if (file_exists($custom404)) {
                require_once $custom404;
                exit;
            }
        }

        echo '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8">'
            . '<meta name="robots" content="noindex,nofollow">'
            . '<title>404 - Sayfa Bulunamadı</title>'
            . '<style>body{font-family:Inter,system-ui,sans-serif;display:flex;justify-content:center;'
            . 'align-items:center;min-height:100vh;margin:0;background:#f8f9fa;color:#333}'
            . '.box{text-align:center;padding:50px;background:#fff;border-radius:16px;'
            . 'box-shadow:0 4px 24px rgba(0,0,0,.06);max-width:480px}'
            . '.code{font-size:5em;font-weight:800;color:#e2e8f0;margin:0;line-height:1}'
            . 'h1{font-size:1.3em;margin:10px 0;color:#1e293b}'
            . 'p{color:#64748b;margin:8px 0 20px}'
            . 'a{display:inline-block;padding:12px 28px;background:#3b82f6;color:#fff;'
            . 'text-decoration:none;border-radius:8px;font-weight:500;transition:background .2s}'
            . 'a:hover{background:#2563eb}</style></head>'
            . '<body><div class="box"><div class="code">404</div>'
            . '<h1>Sayfa Bulunamadı</h1>'
            . '<p>Aradığınız sayfa taşınmış, silinmiş veya hiç var olmamış olabilir.</p>'
            . '<a href="' . (defined('BASE_URL') ? BASE_URL : '') . '/">Ana Sayfaya Dön</a>'
            . '</div></body></html>';
        exit;
    }

    // ─── Callback Runner ─────────────────────────────────────────

    /**
     * Callback'i çalıştırır
     *
     * Desteklenen formatlar:
     *   Closure/callable  → doğrudan çağır
     *   'Controller@method' → new App\Controllers\Controller()->method($params)
     */
    private static function callCallback($callback, $params)
    {
        // Closure veya callable
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        // 'CartController@index' formatı → instance-based çözümleme
        if (is_string($callback) && strpos($callback, '@') !== false) {
            [$class, $method] = explode('@', $callback, 2);

            // Namespace çözümleme: App\Controllers\ önce dene
            $fqcn = 'App\\Controllers\\' . $class;
            if (!class_exists($fqcn)) {
                // Namespace'siz dene (geriye uyumluluk)
                $fqcn = $class;
            }

            // Sınıf bulunamadı → fail-closed
            if (!class_exists($fqcn)) {
                error_log("V-Commerce Router: Controller bulunamadı: {$class}");
                if (function_exists('isDebug') && isDebug()) {
                    http_response_code(500);
                    die("Controller bulunamadı: {$class}");
                }
                http_response_code(500);
                return null;
            }

            // Metot kontrolü
            if (!method_exists($fqcn, $method)) {
                error_log("V-Commerce Router: Metot bulunamadı: {$class}@{$method}");
                if (function_exists('isDebug') && isDebug()) {
                    http_response_code(500);
                    die("Metot bulunamadı: {$class}@{$method}");
                }
                http_response_code(500);
                return null;
            }

            // Instance oluştur ve metodu çağır
            $controller = new $fqcn();
            return call_user_func_array([$controller, $method], $params);
        }

        return null;
    }
}
