<?php
/**
 * V-Commerce — AccountController
 *
 * Müşteri paneli: hesabım, siparişler, adresler, favoriler, fiyat alarmları.
 * client/ altındaki dosyaları Controller'a taşır.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

class AccountController extends BaseController
{
    /**
     * Hesabım dashboard
     */
    public function dashboard(): void
    {
        require_once ROOT_PATH . 'client/index.php';
    }

    /**
     * Siparişler listesi
     */
    public function orders(): void
    {
        require_once ROOT_PATH . 'client/orders.php';
    }

    /**
     * Sipariş detay
     *
     * @param string $id  Sipariş ID
     */
    public function orderDetail(string $id): void
    {
        $_GET['id'] = $id;
        require_once ROOT_PATH . 'client/order-detail.php';
    }

    /**
     * Profil (GET + POST)
     */
    public function profile(): void
    {
        require_once ROOT_PATH . 'client/profile.php';
    }

    /**
     * Adresler
     */
    public function addresses(): void
    {
        require_once ROOT_PATH . 'client/addresses.php';
    }

    /**
     * Favoriler
     */
    public function wishlist(): void
    {
        require_once ROOT_PATH . 'client/wishlist.php';
    }

    /**
     * Fiyat alarmları
     */
    public function priceAlerts(): void
    {
        require_once ROOT_PATH . 'client/price-alerts.php';
    }

    /**
     * Baskı yükleme
     */
    public function printUpload(): void
    {
        require_once ROOT_PATH . 'client/print-upload.php';
    }
}
