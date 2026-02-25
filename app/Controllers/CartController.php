<?php
/**
 * Bozok E-Ticaret — CartController
 *
 * Sepet görüntüleme, ürün ekleme/güncelleme/silme, kupon uygulama.
 * CartService'i kullanır.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Services\CartService;

class CartController extends BaseController
{
    /**
     * Sepet sayfası görünümü
     */
    public function index(): void
    {
        $summary = CartService::getSummary();
        $summary['sayfa_basligi'] = 'Sepetim';

        $this->view('sepet', $summary);
    }

    /**
     * Sepete ürün ekle (POST)
     */
    public function add(): void
    {
        $urunId = intval($this->postInput('urun_id', $this->input('ekle', 0)));
        $adet = intval($this->postInput('adet', $this->input('adet', 1)));

        if ($urunId > 0 && CartService::add($urunId, $adet)) {
            $this->flash('sepet', 'Ürün sepete eklendi.');
        }
        $this->redirect('/sepet');
    }

    /**
     * Sepet miktarı güncelle (POST)
     */
    public function update(): void
    {
        $sepetId = intval($this->postInput('sepet_id'));
        $adet = intval($this->postInput('adet'));

        CartService::update($sepetId, $adet);
        $this->flash('sepet', 'Sepet güncellendi.');
        $this->redirect('/sepet');
    }

    /**
     * Sepetten ürün sil
     */
    public function remove(): void
    {
        $id = intval($this->input('id'));

        CartService::remove($id);
        $this->flash('sepet', 'Ürün sepetten kaldırıldı.');
        $this->redirect('/sepet');
    }

    /**
     * Kupon uygula (POST)
     */
    public function applyCoupon(): void
    {
        $kod = trim($this->postInput('kupon_kodu', ''));
        $toplam = CartService::getTotal();
        $sonuc = CartService::applyCoupon($kod, $_SESSION['user_id'] ?? null, $toplam);

        if ($sonuc['success']) {
            $_SESSION['kupon'] = [
                'id' => $sonuc['campaign']['id'],
                'kod' => $sonuc['campaign']['code'],
                'indirim' => $sonuc['discount']
            ];
            $this->flash('sepet', $sonuc['message']);
        } else {
            $this->flash('sepet', $sonuc['message'], 'error');
        }
        $this->redirect('/sepet');
    }
}
