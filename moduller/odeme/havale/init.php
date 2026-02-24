<?php
/**
 * Module Name: Havale / EFT Ödeme Yöntemi
 * Description: Banka hesap bilgilerinizi müşterilere göstererek havale ile ödeme almanızı sağlar. Profesyonel izolasyonlu mimari.
 * Version: 1.2.0
 * Author: BankInfo Global
 */

// Sınıfları Yükle (WP Style)
require_once __DIR__ . '/includes/Banka_Manager.php';

hook_ekle('odeme_yontemleri', function ($yontemler) {
    $yontemler[] = [
        'kod' => 'havale',
        'baslik' => 'Havale / EFT',
        'ikon' => 'fas fa-university',
        'aciklama' => 'Banka hesabımıza doğrudan transfer yaparak siparişinizi tamamlayın.'
    ];
    return $yontemler;
});
