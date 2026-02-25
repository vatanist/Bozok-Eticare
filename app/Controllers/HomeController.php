<?php
/**
 * Bozok E-Ticaret — HomeController
 *
 * Anasayfa: öne çıkan ürünler, yeni ürünler, kategoriler.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

class HomeController extends BaseController
{
    /**
     * Anasayfa görünümü
     */
    public function index(): void
    {
        $veriler = [
            'sayfa_basligi' => ayar_getir('site_title', 'Bozok E-Ticaret'),
            'one_cikanlar' => one_cikan_urunler(8),
            'en_yeniler' => en_yeni_urunler(8),
            'kategoriler' => kategorileri_getir()
        ];

        $this->view('anasayfa', $veriler);
    }
}
