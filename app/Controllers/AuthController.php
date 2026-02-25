<?php
/**
 * Bozok E-Ticaret — AuthController
 *
 * Giriş, kayıt, çıkış işlemleri.
 * client/login.php, register.php, logout.php'den taşınmıştır.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

class AuthController extends BaseController
{
    /**
     * Giriş formu (GET)
     */
    public function loginForm(): void
    {
        if (giris_yapilmis_mi()) {
            $this->redirect('/hesabim');
            return;
        }
        require_once ROOT_PATH . 'client/login.php';
    }

    /**
     * Giriş işlemi (POST)
     */
    public function login(): void
    {
        // login.php zaten POST'u handle ediyor
        require_once ROOT_PATH . 'client/login.php';
    }

    /**
     * Kayıt formu (GET)
     */
    public function registerForm(): void
    {
        if (giris_yapilmis_mi()) {
            $this->redirect('/hesabim');
            return;
        }
        require_once ROOT_PATH . 'client/register.php';
    }

    /**
     * Kayıt işlemi (POST)
     */
    public function register(): void
    {
        require_once ROOT_PATH . 'client/register.php';
    }

    /**
     * Çıkış
     */
    public function logout(): void
    {
        require_once ROOT_PATH . 'client/logout.php';
    }
}
