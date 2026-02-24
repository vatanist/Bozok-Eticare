<?php
/**
 * Bozkurt Core - Ödeme Yöneticisi
 */
require_once 'config/config.php';

// Giriş zorunlu
requireLogin();

$cartItems = getCartItems();
if (empty($cartItems)) {
    git('/sepet.php');
}

$user = aktif_kullanici();
$ara_toplam = getCartTotal();

// Kampanya/Kupon
$indirim = $_SESSION['kupon']['indirim'] ?? 0;
$kampanya_id = $_SESSION['kupon']['id'] ?? null;

// Kargo ve KDV
$kdv_orani = 0.20;
$kdv_tutari = $ara_toplam * $kdv_orani;

$totalDesi = Cargo::calculateTotalDesi($cartItems);
$kargo_ucreti = $ara_toplam >= (float) ayar_getir('ucretsiz_kargo_limiti', 2000) ? 0 : Cargo::getShippingPrice($totalDesi);

$genel_toplam = $ara_toplam + $kdv_tutari + $kargo_ucreti - $indirim;

// Adresler
$adresler = Database::fetchAll("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC", [$user['id']]);

// Sipariş Oluşturma (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siparis_no = generateOrderNumber();
    $odeme_yontemi = $_POST['odeme_yontemi'] ?? 'kapida_odeme';

    // Form Verileri
    $ad = temiz($_POST['ad']);
    $soyad = temiz($_POST['soyad']);
    $telefon = temiz($_POST['telefon']);
    $adres = temiz($_POST['adres']);
    $sehir = temiz($_POST['sehir']);
    $ilce = temiz($_POST['ilce']);
    $notlar = temiz($_POST['notlar']);

    if (empty($ad) || empty($adres) || empty($sehir) || empty($telefon)) {
        mesaj('odeme', 'Lütfen tüm zorunlu alanları doldurun.', 'error');
    } else {
        // Siparişi Kaydet
        Database::query(
            "INSERT INTO orders (user_id, order_number, subtotal, shipping_cost, discount_amount, campaign_id, total, status, payment_method, payment_status,
             shipping_first_name, shipping_last_name, shipping_phone, shipping_address, shipping_city, shipping_district, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'pending', ?, ?, ?, ?, ?, ?, ?)",
            [
                $user['id'],
                $siparis_no,
                $ara_toplam,
                $kargo_ucreti,
                $indirim,
                $kampanya_id,
                $genel_toplam,
                $odeme_yontemi,
                $ad,
                $soyad,
                $telefon,
                $adres,
                $sehir,
                $ilce,
                $notlar
            ]
        );
        $order_id = Database::lastInsertId();

        // Kampanya kullanımını kaydet
        if ($kampanya_id && $indirim > 0) {
            recordCampaignUsage($kampanya_id, $user['id'], $order_id, $indirim);
        }

        // Sipariş Ürünlerini Kaydet
        foreach ($cartItems as $item) {
            $fiyat = $item['unit_price_override'] > 0 ? $item['unit_price_override'] : ($item['discount_price'] ?: $item['price']);
            Database::query(
                "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, total)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $order_id,
                    $item['product_id'],
                    $item['name'],
                    $item['image'],
                    $item['quantity'],
                    $fiyat,
                    ($fiyat * $item['quantity'])
                ]
            );
            // Stok güncelle
            Database::query("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?", [$item['quantity'], $item['product_id']]);
        }

        // Sepeti temizle
        clearCart();
        unset($_SESSION['kupon']);

        // Sepeti temizle
        clearCart();
        unset($_SESSION['kupon']);

        // Ödeme Yönlendirmesi (CMS Mantığı)
        if ($odeme_yontemi !== 'kapida_odeme') {
            // Sipariş durumunu 'Ödeme Bekleniyor' olarak güncelle
            Database::query("UPDATE orders SET status = 'pending_payment' WHERE id = ?", [$order_id]);

            // İlgili ödeme modülüne yönlendir
            git('/modul-isleyici.php?modul=' . $odeme_yontemi . '&sayfa=index&id=' . $order_id);
        }

        mesaj('siparis_onay', 'Siparişiniz başarıyla alındı! Sipariş No: ' . $siparis_no);
        git('/client/orders.php');
    }
}

// Şablon verileri
$veriler = [
    'sayfa_basligi' => 'Siparişi Tamamla',
    'kullanici' => $user,
    'sepet' => $cartItems,
    'ara_toplam' => $ara_toplam,
    'kdv' => $kdv_tutari,
    'kargo' => $kargo_ucreti,
    'indirim' => $indirim,
    'toplam' => $genel_toplam,
    'adresler' => $adresler
];

gorunum('odeme', $veriler);
