<?php
/**
 * Bozok E-Ticaret — Veri Erişim Yardımcıları
 *
 * Ürün, kategori, istatistik, ayar, sepet ve kampanya fonksiyonları.
 *
 * @package App\Helpers
 */

// ── Ürün & Kategori ──

/**
 * Öne çıkan ürünleri getirir.
 */
function one_cikan_urunler($limit = 8)
{
    return Database::fetchAll(
        "SELECT p.*, c.name as kategori_adi FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.is_featured = 1 AND p.status = 1 
         ORDER BY p.created_at DESC LIMIT ?",
        [$limit]
    );
}

/**
 * En yeni ürünleri getirir.
 */
function en_yeni_urunler($limit = 8)
{
    return Database::fetchAll(
        "SELECT p.*, c.name as kategori_adi FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.status = 1 
         ORDER BY p.created_at DESC LIMIT ?",
        [$limit]
    );
}

/**
 * Tüm aktif kategorileri getirir.
 */
function kategorileri_getir()
{
    return Database::fetchAll("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC");
}

/**
 * Tek bir ürün bilgisini getirir.
 */
function urun_getir($id)
{
    return Database::fetch(
        "SELECT p.*, c.name as kategori_adi FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.id = ?",
        [$id]
    );
}

function urun_getir_slug($slug)
{
    return Database::fetch(
        "SELECT p.*, c.name as kategori_adi FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.slug = ?",
        [$slug]
    );
}

/**
 * Tüm kategorileri hiyerarşik (düz liste) olarak getirir.
 * Menü ve Select kutuları için idealdir.
 */
function kategorileri_getir_duz($parent_id = null, $level = 0)
{
    $list = [];
    $sql = "SELECT c.*, (SELECT COUNT(id) FROM products WHERE category_id = c.id) as product_count 
            FROM categories c 
            WHERE " . ($parent_id === null ? "parent_id IS NULL" : "parent_id = ?") . " 
            ORDER BY sort_order ASC, name ASC";

    $params = $parent_id === null ? [] : [$parent_id];
    $categories = Database::fetchAll($sql, $params);

    foreach ($categories as $c) {
        $c['level'] = $level;
        $list[] = $c;
        $children = kategorileri_getir_duz($c['id'], $level + 1);
        $list = array_merge($list, $children);
    }
    return $list;
}

// ── Ayarlar ──

/**
 * Veritabanından bir ayar değerini getirir. (Settings::get sarmalayıcı)
 */
function ayar_getir($anahtar, $varsayilan = '', $grup = 'general')
{
    return Settings::get($anahtar, $grup, $varsayilan);
}

/**
 * Veritabanına ayar kaydeder. (Settings::set sarmalayıcı)
 */
function setSetting($anahtar, $deger, $grup = 'general')
{
    return Settings::set($anahtar, $deger, $grup);
}

// ── İstatistikler ──

/**
 * Yönetim paneli için temel istatistikleri getirir.
 */
function istatistikleri_getir()
{
    return [
        'toplam_siparis' => Database::fetch("SELECT COUNT(id) as c FROM orders")['c'] ?? 0,
        'toplam_gelir' => Database::fetch("SELECT SUM(total) as s FROM orders WHERE status != 'cancelled'")['s'] ?? 0,
        'toplam_urun' => Database::fetch("SELECT COUNT(id) as c FROM products")['c'] ?? 0,
        'toplam_kullanici' => Database::fetch("SELECT COUNT(id) as c FROM users")['c'] ?? 0
    ];
}

/**
 * Premium Dashboard için detaylı kurumsal istatistikleri getirir.
 */
function istatistikleri_getir_detayli()
{
    $stats = [];

    $stats['ciro'] = Database::fetch("SELECT SUM(total) as s FROM orders WHERE status != 'cancelled'")['s'] ?? 0;
    $stats['bekleyen_siparis'] = Database::fetch("SELECT COUNT(id) as c FROM orders WHERE status = 'pending'")['c'] ?? 0;
    $stats['gonderilecek_siparis'] = Database::fetch("SELECT COUNT(id) as c FROM orders WHERE status = 'processing'")['c'] ?? 0;
    $stats['toplam_urun'] = Database::fetch("SELECT COUNT(id) as c FROM products")['c'] ?? 0;
    $stats['toplam_kategori'] = Database::fetch("SELECT COUNT(id) as c FROM categories")['c'] ?? 0;
    $stats['toplam_kullanici'] = Database::fetch("SELECT COUNT(id) as c FROM users")['c'] ?? 0;

    $stats['net_kar'] = $stats['ciro'] * 0.25;

    $aylik_veriler = Database::fetchAll("
        SELECT 
            DATE_FORMAT(created_at, '%M') as ay,
            SUM(total) as toplam
        FROM orders 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY MONTH(created_at)
        ORDER BY created_at ASC
    ");

    $stats['grafik_aylar'] = [];
    $stats['grafik_veriler'] = [];

    if (empty($aylik_veriler)) {
        $stats['grafik_aylar'] = ['Eyl', 'Eki', 'Kas', 'Ara', 'Oca', 'Şub'];
        $stats['grafik_veriler'] = [45000, 52000, 48000, 75000, 62000, 85000];
    } else {
        foreach ($aylik_veriler as $v) {
            $stats['grafik_aylar'][] = $v['ay'];
            $stats['grafik_veriler'][] = $v['toplam'];
        }
    }

    return $stats;
}

// ── Sepet & Kampanya ──

function getCartItems()
{
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        return Database::fetchAll("
            SELECT c.*, p.name, p.price, p.discount_price, p.image, p.slug, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ", [$userId]);
    } else {
        return $_SESSION['cart'] ?? [];
    }
}

/**
 * Sepet öğelerini getirir (GetCartItems alias)
 * @deprecated v2.0'da kaldırılacak. CartService::getItems() kullanın.
 */
function getCart()
{
    return getCartItems();
}

function getCartTotal()
{
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $price = $item['unit_price_override'] > 0 ? $item['unit_price_override'] : ($item['discount_price'] ?: $item['price']);
        $total += $price * $item['quantity'];
    }
    return $total;
}

function getCartCount()
{
    $items = getCartItems();
    $count = 0;
    foreach ($items as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

function updateCartQuantity($cartId, $qty)
{
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        Database::query("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", [$qty, $cartId, $userId]);
    } else {
        if (isset($_SESSION['cart'][$cartId])) {
            $_SESSION['cart'][$cartId]['quantity'] = $qty;
        }
    }
}

function removeFromCart($cartId)
{
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        Database::query("DELETE FROM cart WHERE id = ? AND user_id = ?", [$cartId, $userId]);
    } else {
        unset($_SESSION['cart'][$cartId]);
    }
}

function clearCart()
{
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        Database::query("DELETE FROM cart WHERE user_id = ?", [$userId]);
    } else {
        $_SESSION['cart'] = [];
    }
}

function applyCoupon($code, $userId, $subtotal)
{
    $campaign = Database::fetch("SELECT * FROM campaigns WHERE code = ? AND status = 1", [$code]);
    if (!$campaign)
        return ['success' => false, 'message' => 'Geçersiz kupon kodu.'];

    $now = date('Y-m-d H:i:s');
    if ($campaign['start_date'] && $campaign['start_date'] > $now)
        return ['success' => false, 'message' => 'Kupon henüz aktif değil.'];
    if ($campaign['end_date'] && $campaign['end_date'] < $now)
        return ['success' => false, 'message' => 'Kuponun süresi dolmuş.'];

    if ($campaign['min_order_amount'] > 0 && $subtotal < $campaign['min_order_amount']) {
        return ['success' => false, 'message' => 'Bu kuponu kullanmak için minimum sepet tutarı ' . para_yaz($campaign['min_order_amount']) . ' olmalıdır.'];
    }

    $discount = 0;
    if ($campaign['discount_percent'] > 0) {
        $discount = round($subtotal * $campaign['discount_percent'] / 100, 2);
    } elseif ($campaign['discount_amount'] > 0) {
        $discount = $campaign['discount_amount'];
    }

    if ($campaign['max_discount'] > 0 && $discount > $campaign['max_discount'])
        $discount = $campaign['max_discount'];

    return [
        'success' => true,
        'message' => 'Kupon başarıyla uygulandı.',
        'discount' => $discount,
        'campaign' => $campaign
    ];
}

function getActiveCampaignsForUser($userId)
{
    return Database::fetchAll("SELECT * FROM campaigns WHERE status = 1 AND (user_id IS NULL OR user_id = ?) ORDER BY created_at DESC", [$userId]);
}

function generateOrderNumber()
{
    return 'ORD-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('Ymd');
}

function recordCampaignUsage($campaignId, $userId, $orderId, $discount)
{
    return Database::query("INSERT INTO campaign_usage (campaign_id, user_id, order_id, discount_amount) VALUES (?, ?, ?, ?)", [$campaignId, $userId, $orderId, $discount]);
}

// ── Uyumluluk Alias'ları ──

/** @deprecated v2.0'da kaldırılacak. kategorileri_getir() kullanın. */
function getCategories()
{
    return kategorileri_getir();
}

/** @deprecated v2.0'da kaldırılacak. kategorileri_getir_duz() kullanın. */
function getAllCategoriesFlat()
{
    return kategorileri_getir_duz();
}

/** @deprecated v2.0'da kaldırılacak. istatistikleri_getir() kullanın. */
function getStats()
{
    return istatistikleri_getir();
}

/** @deprecated v2.0'da kaldırılacak. ayar_getir() kullanın. */
function getSetting($a, $v = '')
{
    return ayar_getir($a, $v);
}
