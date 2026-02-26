<?php
/**
 * V-Commerce — CartService
 *
 * Sepet iş mantığını merkezileştirir.
 * Oturum tabanlı (misafir) ve veritabanı tabanlı (üye) sepet yönetimi.
 *
 * @package App\Services
 */

namespace App\Services;

class CartService
{
    /**
     * Sepetteki ürünleri getirir.
     * Üye: DB'den, Misafir: Session'dan
     *
     * @return array
     */
    public static function getItems(): array
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            return \Database::fetchAll("
                SELECT c.*, p.name, p.price, p.discount_price, p.image, p.slug, p.stock 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ", [$userId]);
        }
        return $_SESSION['cart'] ?? [];
    }

    /**
     * Sepet toplam tutarını hesaplar.
     *
     * @return float
     */
    public static function getTotal(): float
    {
        $items = self::getItems();
        $total = 0;
        foreach ($items as $item) {
            $price = ($item['unit_price_override'] ?? 0) > 0
                ? $item['unit_price_override']
                : (($item['discount_price'] ?? 0) ?: $item['price']);
            $total += $price * $item['quantity'];
        }
        return (float) $total;
    }

    /**
     * Sepetteki toplam ürün adedini döner.
     *
     * @return int
     */
    public static function getCount(): int
    {
        $items = self::getItems();
        $count = 0;
        foreach ($items as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Sepete ürün ekler.
     *
     * @param int $productId  Ürün ID
     * @param int $quantity   Adet
     * @return bool
     */
    public static function add(int $productId, int $quantity = 1): bool
    {
        if ($productId <= 0 || $quantity <= 0) {
            return false;
        }

        $product = urun_getir($productId);
        if (!$product) {
            return false;
        }

        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $existing = \Database::fetch(
                "SELECT id FROM cart WHERE user_id = ? AND product_id = ?",
                [$userId, $productId]
            );
            if ($existing) {
                \Database::query(
                    "UPDATE cart SET quantity = quantity + ? WHERE id = ?",
                    [$quantity, $existing['id']]
                );
            } else {
                \Database::query(
                    "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)",
                    [$userId, $productId, $quantity]
                );
            }
        } else {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = [
                    'id' => $productId,
                    'product_id' => $productId,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'discount_price' => $product['discount_price'],
                    'image' => $product['image'],
                    'slug' => $product['slug'],
                    'quantity' => $quantity,
                    'unit_price_override' => 0
                ];
            }
        }

        return true;
    }

    /**
     * Sepetteki ürün miktarını günceller.
     *
     * @param int $cartId  Sepet öğe ID (DB) veya ürün ID (session)
     * @param int $qty     Yeni miktar
     */
    public static function update(int $cartId, int $qty): void
    {
        if ($qty <= 0) {
            self::remove($cartId);
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            \Database::query(
                "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?",
                [$qty, $cartId, $userId]
            );
        } else {
            if (isset($_SESSION['cart'][$cartId])) {
                $_SESSION['cart'][$cartId]['quantity'] = $qty;
            }
        }
    }

    /**
     * Sepetten ürün siler.
     *
     * @param int $cartId  Sepet öğe ID (DB) veya ürün ID (session)
     */
    public static function remove(int $cartId): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            \Database::query(
                "DELETE FROM cart WHERE id = ? AND user_id = ?",
                [$cartId, $userId]
            );
        } else {
            unset($_SESSION['cart'][$cartId]);
        }
    }

    /**
     * Sepeti tamamen boşaltır.
     */
    public static function clear(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            \Database::query("DELETE FROM cart WHERE user_id = ?", [$userId]);
        } else {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Kupon uygular.
     *
     * @param string   $code     Kupon kodu
     * @param int|null $userId   Kullanıcı ID
     * @param float    $subtotal Ara toplam
     * @return array   ['success' => bool, 'message' => string, 'discount' => float, 'campaign' => array]
     */
    public static function applyCoupon(string $code, ?int $userId, float $subtotal): array
    {
        $campaign = \Database::fetch(
            "SELECT * FROM campaigns WHERE code = ? AND status = 1",
            [$code]
        );

        if (!$campaign) {
            return ['success' => false, 'message' => 'Geçersiz kupon kodu.'];
        }

        $now = date('Y-m-d H:i:s');
        if ($campaign['start_date'] && $campaign['start_date'] > $now) {
            return ['success' => false, 'message' => 'Kupon henüz aktif değil.'];
        }
        if ($campaign['end_date'] && $campaign['end_date'] < $now) {
            return ['success' => false, 'message' => 'Kuponun süresi dolmuş.'];
        }

        if ($campaign['min_order_amount'] > 0 && $subtotal < $campaign['min_order_amount']) {
            return [
                'success' => false,
                'message' => 'Bu kuponu kullanmak için minimum sepet tutarı '
                    . para_yaz($campaign['min_order_amount']) . ' olmalıdır.'
            ];
        }

        $discount = 0;
        if ($campaign['discount_percent'] > 0) {
            $discount = round($subtotal * $campaign['discount_percent'] / 100, 2);
        } elseif ($campaign['discount_amount'] > 0) {
            $discount = $campaign['discount_amount'];
        }

        if ($campaign['max_discount'] > 0 && $discount > $campaign['max_discount']) {
            $discount = $campaign['max_discount'];
        }

        return [
            'success' => true,
            'message' => 'Kupon başarıyla uygulandı.',
            'discount' => $discount,
            'campaign' => $campaign
        ];
    }

    /**
     * Sepet verisini topluca hazırlar (Controller'lar için).
     *
     * @return array ['urunler', 'ara_toplam', 'kdv', 'kargo', 'indirim', 'toplam', 'kupon']
     */
    public static function getSummary(): array
    {
        $items = self::getItems();
        $subtotal = self::getTotal();

        $kdvOrani = 0.20;
        $kdv = $subtotal * $kdvOrani;
        $kargoUcreti = $subtotal >= (float) ayar_getir('ucretsiz_kargo_limiti', 2000)
            ? 0
            : (float) ayar_getir('kargo_ucreti', 49.90);
        $indirim = $_SESSION['kupon']['indirim'] ?? 0;
        $toplam = $subtotal + $kdv + $kargoUcreti - $indirim;

        return [
            'urunler' => $items,
            'ara_toplam' => $subtotal,
            'kdv' => $kdv,
            'kargo' => $kargoUcreti,
            'indirim' => $indirim,
            'toplam' => $toplam,
            'kupon' => $_SESSION['kupon'] ?? null
        ];
    }
}
