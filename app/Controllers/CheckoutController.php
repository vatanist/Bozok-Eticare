<?php
/**
 * Bozok E-Ticaret — CheckoutController
 *
 * Ödeme sayfası görünümü ve sipariş oluşturma.
 * odeme.php'deki iş mantığını barındırır.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Services\CartService;

class CheckoutController extends BaseController
{
    /**
     * Ödeme sayfası (GET)
     */
    public function index(): void
    {
        $cartItems = CartService::getItems();
        if (empty($cartItems)) {
            $this->redirect('/sepet');
            return;
        }

        $user = aktif_kullanici();
        $araToplam = CartService::getTotal();

        $indirim = $_SESSION['kupon']['indirim'] ?? 0;

        $kdvOrani = 0.20;
        $kdvTutari = $araToplam * $kdvOrani;

        $totalDesi = \Cargo::calculateTotalDesi($cartItems);
        $kargoUcreti = $araToplam >= (float) ayar_getir('ucretsiz_kargo_limiti', 2000)
            ? 0
            : \Cargo::getShippingPrice($totalDesi);

        $genelToplam = $araToplam + $kdvTutari + $kargoUcreti - $indirim;

        $adresler = \Database::fetchAll(
            "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC",
            [$user['id']]
        );

        $this->view('odeme', [
            'sayfa_basligi' => 'Siparişi Tamamla',
            'kullanici' => $user,
            'sepet' => $cartItems,
            'ara_toplam' => $araToplam,
            'kdv' => $kdvTutari,
            'kargo' => $kargoUcreti,
            'indirim' => $indirim,
            'toplam' => $genelToplam,
            'adresler' => $adresler
        ]);
    }

    /**
     * Sipariş oluştur (POST)
     */
    public function process(): void
    {
        $cartItems = CartService::getItems();
        if (empty($cartItems)) {
            $this->redirect('/sepet');
            return;
        }

        $user = aktif_kullanici();
        $araToplam = CartService::getTotal();
        $indirim = $_SESSION['kupon']['indirim'] ?? 0;
        $kampanyaId = $_SESSION['kupon']['id'] ?? null;

        $kdvTutari = $araToplam * 0.20;
        $totalDesi = \Cargo::calculateTotalDesi($cartItems);
        $kargoUcreti = $araToplam >= (float) ayar_getir('ucretsiz_kargo_limiti', 2000)
            ? 0
            : \Cargo::getShippingPrice($totalDesi);
        $genelToplam = $araToplam + $kdvTutari + $kargoUcreti - $indirim;

        // Form verileri
        $ad = temiz($this->postInput('ad', ''));
        $soyad = temiz($this->postInput('soyad', ''));
        $telefon = temiz($this->postInput('telefon', ''));
        $adres = temiz($this->postInput('adres', ''));
        $sehir = temiz($this->postInput('sehir', ''));
        $ilce = temiz($this->postInput('ilce', ''));
        $notlar = temiz($this->postInput('notlar', ''));
        $odemeYontemi = $this->postInput('odeme_yontemi', 'kapida_odeme');

        if (empty($ad) || empty($adres) || empty($sehir) || empty($telefon)) {
            $this->flash('odeme', 'Lütfen tüm zorunlu alanları doldurun.', 'error');
            $this->redirect('/siparis-tamamla');
            return;
        }

        $siparisNo = generateOrderNumber();

        // Siparişi kaydet
        \Database::query(
            "INSERT INTO orders (user_id, order_number, subtotal, shipping_cost, discount_amount, campaign_id, total, status, payment_method, payment_status,
             shipping_first_name, shipping_last_name, shipping_phone, shipping_address, shipping_city, shipping_district, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'pending', ?, ?, ?, ?, ?, ?, ?)",
            [
                $user['id'],
                $siparisNo,
                $araToplam,
                $kargoUcreti,
                $indirim,
                $kampanyaId,
                $genelToplam,
                $odemeYontemi,
                $ad,
                $soyad,
                $telefon,
                $adres,
                $sehir,
                $ilce,
                $notlar
            ]
        );
        $orderId = \Database::lastInsertId();

        // Kampanya kullanımı
        if ($kampanyaId && $indirim > 0) {
            recordCampaignUsage($kampanyaId, $user['id'], $orderId, $indirim);
        }

        // Sipariş ürünleri
        foreach ($cartItems as $item) {
            $fiyat = ($item['unit_price_override'] ?? 0) > 0
                ? $item['unit_price_override']
                : ($item['discount_price'] ?: $item['price']);
            \Database::query(
                "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, total)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$orderId, $item['product_id'], $item['name'], $item['image'], $item['quantity'], $fiyat, ($fiyat * $item['quantity'])]
            );
            \Database::query("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?", [$item['quantity'], $item['product_id']]);
        }

        // Sepeti temizle
        CartService::clear();
        unset($_SESSION['kupon']);

        // Ödeme yönlendirmesi
        if ($odemeYontemi !== 'kapida_odeme') {
            \Database::query("UPDATE orders SET status = 'pending_payment' WHERE id = ?", [$orderId]);
            $this->redirect('/modul-isleyici.php?modul=' . $odemeYontemi . '&sayfa=index&id=' . $orderId);
            return;
        }

        $this->flash('siparis_onay', 'Siparişiniz başarıyla alındı! Sipariş No: ' . $siparisNo);
        $this->redirect('/hesabim/siparisler');
    }
}
