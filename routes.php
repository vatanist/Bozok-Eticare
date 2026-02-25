<?php
/**
 * Bozok E-Ticaret - Route Tanımları
 *
 * Bu dosya tüm frontend route'larını tanımlar.
 * index.php tarafından include edilir.
 *
 * Mimari Kararlar:
 *   - ROUTER_MODE=shadow: Eski .php dosyaları + yeni route'lar paralel (test)
 *   - ROUTER_MODE=full: Eski URL'ler 301 ile yeni route'lara yönlenir
 *   - ROUTER_MODE=legacy: Router devre dışı (rollback)
 *
 * 301 vs 302 Ayrımı:
 *   - 301 (Permanent): Eski URL → yeni URL (SEO link juice aktarılır)
 *   - 302 (Temporary): Geçici yönlendirme (login redirect vb.)
 *
 * Redirect Zamanlaması:
 *   - 301 redirect'ler ROUTE OLARAK tanımlıdır (dispatch içinde çalışır)
 *   - Eski .php dosyaları fiziksel olarak mevcut olduğu sürece Apache doğrudan servis eder
 *   - Dosyalar rename/silinince Apache index.php'ye yönlendirir → Router 301 çalışır
 *   - Shadow mode'da performans maliyeti SIFIR (double match yok)
 *
 * @package BozokETicaret
 * @version 2.0.0
 */

// ═══════════════════════════════════════════════
// ROUTER_MODE kontrolü
// ═══════════════════════════════════════════════
$routerMode = env('ROUTER_MODE', 'shadow');

if ($routerMode === 'legacy') {
    // Eski sistem — Router hiç çalışmaz, dosyalar doğrudan servis edilir
    return;
}

// ═══════════════════════════════════════════════
// 1. ANA SAYFALAR (Frontend) — Controller Tabanlı
// ═══════════════════════════════════════════════

// Anasayfa
Router::get('/', 'HomeController@index');

// Ürün Detay
Router::get('/urun/{slug}', 'ProductController@show');

// Kategori Sayfası
Router::get('/kategori/{slug}', 'ProductController@category');

// Tüm Ürünler (hâlâ dosya tabanlı — ayrı controller gerektirebilir)
Router::get('/urunler', function () {
    require_once ROOT_PATH . 'urunler.php';
});

// Arama
Router::get('/ara', 'ProductController@search');

// Sepet
Router::get('/sepet', 'CartController@index');
Router::post('/sepet/ekle', 'CartController@add');
Router::post('/sepet/guncelle', 'CartController@update');
Router::get('/sepet/sil', 'CartController@remove');
Router::post('/sepet/kupon', 'CartController@applyCoupon');

// Ödeme
Router::get('/siparis-tamamla', 'CheckoutController@index', ['AuthMiddleware']);
Router::post('/siparis-tamamla', 'CheckoutController@process', ['AuthMiddleware', 'CsrfMiddleware']);

// Sitemap (XML)
Router::get('/sitemap.xml', function () {
    require_once ROOT_PATH . 'sitemap.php';
});


// Çerez tercih kaydı
Router::post('/cerez/tercih', function () {
    require_once ROOT_PATH . 'cerez-tercih.php';
}, ['CsrfMiddleware']);

// ═══════════════════════════════════════════════
// 2. CMS SAYFALAR — PageController
// ═══════════════════════════════════════════════

// Dinamik sayfa: /sayfa/{slug}
Router::get('/sayfa/{slug}', 'PageController@show');

// Bilinen statik sayfalar (kök URL olarak erişilebilir)
$statik_sayfalar = [
    'hakkimizda',
    'iletisim',
    'kvkk',
    'gizlilik-politikasi',
    'mesafeli-satis-sozlesmesi',
    'cerez-politikasi',
    'iptal-iade'
];
foreach ($statik_sayfalar as $slug) {
    Router::get('/' . $slug, function () use ($slug) {
        $ctrl = new \App\Controllers\PageController();
        $ctrl->show($slug);
    });
}

// ═══════════════════════════════════════════════
// 3. MÜŞTERİ PANELİ — AuthController + AccountController
// ═══════════════════════════════════════════════

// Giriş / Kayıt (auth gerekmez)
Router::get('/hesabim/giris', 'AuthController@loginForm');
Router::post('/hesabim/giris', 'AuthController@login', ['RateLimitMiddleware:customer_login', 'CsrfMiddleware']);

Router::get('/kayit', 'AuthController@registerForm');
Router::post('/kayit', 'AuthController@register', ['RateLimitMiddleware:customer_login', 'CsrfMiddleware']);

Router::get('/cikis', 'AuthController@logout');

// Korumalı müşteri sayfaları
Router::group('/hesabim', function () {
    Router::get('', 'AccountController@dashboard');
    Router::get('/siparisler', 'AccountController@orders');
    Router::get('/siparis/{id}', 'AccountController@orderDetail');
    Router::get('/adresler', 'AccountController@addresses');
    Router::get('/profil', 'AccountController@profile');
    Router::post('/profil', 'AccountController@profile');
    Router::get('/favoriler', 'AccountController@wishlist');
    Router::get('/fiyat-alarmlari', 'AccountController@priceAlerts');
    Router::get('/baski-yukle', 'AccountController@printUpload');
    Router::post('/baski-yukle', 'AccountController@printUpload');
}, ['AuthMiddleware']);

// ═══════════════════════════════════════════════
// 4. AJAX ENDPOINTS
// ═══════════════════════════════════════════════

Router::post('/ajax/sepet', function () {
    require_once ROOT_PATH . 'ajax/cart.php';
});

Router::post('/ajax/favori', function () {
    require_once ROOT_PATH . 'ajax/wishlist.php';
}, ['AuthMiddleware']);

Router::post('/ajax/fiyat-alarm', function () {
    require_once ROOT_PATH . 'ajax/price-alert.php';
}, ['AuthMiddleware']);

// ═══════════════════════════════════════════════
// 5. API V1 (RESTful)
// ═══════════════════════════════════════════════

Router::group('/api/v1', function () {
    Router::get('/products', function () {
        ApiController::authenticate();
        $products = Database::fetchAll("SELECT id, name, price, stock, sku FROM products WHERE status = 1 LIMIT 50");
        ApiController::success('Ürünler başarıyla çekildi.', $products);
    });

    Router::get('/product/{id}', function ($id) {
        ApiController::authenticate();
        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product)
            ApiController::error('Ürün bulunamadı.', 404);
        ApiController::success('Ürün detayı getirildi.', $product);
    });

    Router::get('/orders', function () {
        ApiController::authenticate();
        $orders = Database::fetchAll("SELECT * FROM orders WHERE user_id = 1 ORDER BY created_at DESC");
        ApiController::success('Siparişler listelendi.', $orders);
    });

    Router::post('/register-device', function () {
        ApiController::authenticate();
        $data = json_decode(file_get_contents('php://input'), true);
        if (Notification::registerDevice($data['user_id'] ?? 1, $data['token'], $data['platform'] ?? 'android')) {
            ApiController::success('Cihaz API sistemine başarıyla kaydedildi.');
        }
    });

    Router::post('/address', function () {
        require_once ROOT_PATH . 'api/address.php';
    });
});

// ═══════════════════════════════════════════════
// 6. 301 REDIRECT SİSTEMİ (SEO KORUMASI)
//
// Bu route'lar SADECE eski .php dosyaları disk'ten
// silindiğinde/rename edildiğinde devreye girer.
// Shadow mode'da performans maliyeti SIFIR.
// Apache'nin -f kuralı dosya varsa doğrudan servis eder.
// ═══════════════════════════════════════════════

if ($routerMode === 'full') {
    // Eski URL → Yeni URL (301 Permanent — SEO link juice korunur)

    Router::get('/urun-detay.php', function () {
        $slug = $_GET['slug'] ?? '';
        $qs = http_build_query(array_diff_key($_GET, ['slug' => '']));
        $url = BASE_URL . '/urun/' . urlencode($slug) . ($qs ? '?' . $qs : '');
        header('Location: ' . $url, true, 301);
        exit;
    });

    Router::get('/kategori.php', function () {
        $slug = $_GET['slug'] ?? $_GET['category'] ?? '';
        $qs = http_build_query(array_diff_key($_GET, ['slug' => '', 'category' => '']));
        $url = BASE_URL . '/kategori/' . urlencode($slug) . ($qs ? '?' . $qs : '');
        header('Location: ' . $url, true, 301);
        exit;
    });

    Router::get('/urunler.php', function () {
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: ' . BASE_URL . '/urunler' . ($qs ? '?' . $qs : ''), true, 301);
        exit;
    });

    Router::get('/search.php', function () {
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: ' . BASE_URL . '/ara' . ($qs ? '?' . $qs : ''), true, 301);
        exit;
    });

    Router::get('/sepet.php', function () {
        header('Location: ' . BASE_URL . '/sepet', true, 301);
        exit;
    });

    Router::get('/odeme.php', function () {
        header('Location: ' . BASE_URL . '/siparis-tamamla', true, 301);
        exit;
    });

    Router::get('/page.php', function () {
        $slug = $_GET['slug'] ?? '';
        $url = BASE_URL . '/sayfa/' . urlencode($slug);
        header('Location: ' . $url, true, 301);
        exit;
    });

    Router::get('/sitemap.php', function () {
        header('Location: ' . BASE_URL . '/sitemap.xml', true, 301);
        exit;
    });

    // Client eski URL'leri
    Router::get('/client/login.php', function () {
        header('Location: ' . BASE_URL . '/hesabim/giris', true, 301);
        exit;
    });

    Router::get('/client/register.php', function () {
        header('Location: ' . BASE_URL . '/kayit', true, 301);
        exit;
    });

    Router::get('/client/logout.php', function () {
        header('Location: ' . BASE_URL . '/cikis', true, 301);
        exit;
    });

    Router::get('/client/orders.php', function () {
        header('Location: ' . BASE_URL . '/hesabim/siparisler', true, 301);
        exit;
    });

    Router::get('/client/order-detail.php', function () {
        $id = $_GET['id'] ?? '';
        header('Location: ' . BASE_URL . '/hesabim/siparis/' . intval($id), true, 301);
        exit;
    });

    Router::get('/client/profile.php', function () {
        header('Location: ' . BASE_URL . '/hesabim/profil', true, 301);
        exit;
    });

    Router::get('/client/addresses.php', function () {
        header('Location: ' . BASE_URL . '/hesabim/adresler', true, 301);
        exit;
    });

    Router::get('/client/wishlist.php', function () {
        header('Location: ' . BASE_URL . '/hesabim/favoriler', true, 301);
        exit;
    });

    Router::get('/client/price-alerts.php', function () {
        header('Location: ' . BASE_URL . '/hesabim/fiyat-alarmlari', true, 301);
        exit;
    });
}

// ═══════════════════════════════════════════════
// DISPATCH — Router'ı çalıştır
// ═══════════════════════════════════════════════
Router::dispatch();
