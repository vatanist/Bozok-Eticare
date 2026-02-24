<?php
/**
 * Bozkurt Core - Sepet Yöneticisi
 */
require_once 'config/config.php';

// Sepet işlemleri (POST ve GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem = $_POST['islem'] ?? '';

    if ($islem === 'guncelle') {
        $sepet_id = intval($_POST['sepet_id']);
        $adet = intval($_POST['adet']);
        updateCartQuantity($sepet_id, $adet);
        mesaj('sepet', 'Sepet güncellendi.');
        git('/sepet.php');
    }

    if ($islem === 'kupon') {
        $kod = trim($_POST['kupon_kodu'] ?? '');
        $toplam = getCartTotal();
        $sonuc = applyCoupon($kod, $_SESSION['user_id'] ?? null, $toplam);
        if ($sonuc['success']) {
            $_SESSION['kupon'] = [
                'id' => $sonuc['campaign']['id'],
                'kod' => $sonuc['campaign']['code'],
                'indirim' => $sonuc['discount']
            ];
            mesaj('sepet', $sonuc['message']);
        } else {
            mesaj('sepet', $sonuc['message'], 'error');
        }
        git('/sepet.php');
    }
}

// GET üzerinden işlemler (Silme, Ekleme)
$islem = $_GET['islem'] ?? '';
if ($islem === 'sil') {
    $id = intval($_GET['id']);
    removeFromCart($id);
    mesaj('sepet', 'Ürün sepetten kaldırıldı.');
    git('/sepet.php');
}

if ($islem === 'ekle') {
    $urun_id = intval($_GET['ekle'] ?? $_POST['urun_id'] ?? 0);
    $adet = intval($_GET['adet'] ?? $_POST['adet'] ?? 1);

    if ($urun_id > 0) {
        $urun = urun_getir($urun_id);
        if ($urun) {
            // Basitçe sepete ekle (Bu fonksiyonu functions.php'ye eklememiştim, hemen ekleyelim veya burada yapalım)
            // Mevcut getCartItems modeline göre ekleme yapıyoruz
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                $var_mi = Database::fetch("SELECT id FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $urun_id]);
                if ($var_mi) {
                    Database::query("UPDATE cart SET quantity = quantity + ? WHERE id = ?", [$adet, $var_mi['id']]);
                } else {
                    Database::query("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)", [$userId, $urun_id, $adet]);
                }
            } else {
                if (!isset($_SESSION['cart']))
                    $_SESSION['cart'] = [];
                if (isset($_SESSION['cart'][$urun_id])) {
                    $_SESSION['cart'][$urun_id]['quantity'] += $adet;
                } else {
                    $_SESSION['cart'][$urun_id] = [
                        'id' => $urun_id,
                        'product_id' => $urun_id,
                        'name' => $urun['name'],
                        'price' => $urun['price'],
                        'discount_price' => $urun['discount_price'],
                        'image' => $urun['image'],
                        'slug' => $urun['slug'],
                        'quantity' => $adet,
                        'unit_price_override' => 0
                    ];
                }
            }
            mesaj('sepet', 'Ürün sepete eklendi.');
        }
    }
    git('/sepet.php');
}

// Verileri hazırla
$sepet_urunleri = getCartItems();
$ara_toplam = getCartTotal();
$kdv_orani = 0.20;
$kdv_tutari = $ara_toplam * $kdv_orani;
$kargo_ucreti = $ara_toplam >= (float) ayar_getir('ucretsiz_kargo_limiti', 2000) ? 0 : (float) ayar_getir('kargo_ucreti', 49.90);
$indirim = $_SESSION['kupon']['indirim'] ?? 0;
$genel_toplam = $ara_toplam + $kdv_tutari + $kargo_ucreti - $indirim;

$veriler = [
    'sayfa_basligi' => 'Sepetim',
    'urunler' => $sepet_urunleri,
    'ara_toplam' => $ara_toplam,
    'kdv' => $kdv_tutari,
    'kargo' => $kargo_ucreti,
    'indirim' => $indirim,
    'toplam' => $genel_toplam,
    'kupon' => $_SESSION['kupon'] ?? null
];

// Görünümü yükle
gorunum('sepet', $veriler);
