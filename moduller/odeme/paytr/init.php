<?php
/**
 * Module Name: PayTR Sanal POS iFrame API
 * Description: PayTR gateway entegrasyonu ile güvenli ödeme alma modülü. Profesyonel izolasyonlu mimari.
 * Version: 3.1.0
 * Author: PayTR A.Ş. & Bozkurt Core
 */

// Sınıfları Yükle (WP Style)
require_once __DIR__ . '/includes/Paytr_Gateway.php';

// Ödeme yöntemini listeye ekle
hook_ekle('odeme_yontemleri', function ($yontemler) {
    $yontemler[] = [
        'kod' => 'paytr',
        'baslik' => 'Kredi Kartı / Banka Kartı (PayTR)',
        'ikon' => 'fas fa-credit-card',
        'aciklama' => 'Güvenli PayTR altyapısı ile 12 taksit imkanı.'
    ];
    return $yontemler;
});

// Modüle özel CSS/JS yükleme (Asset Enqueue)
hook_ekle('footer_sonu', function () {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page === 'odeme.php' || strpos($_SERVER['REQUEST_URI'], 'modul=paytr') !== false) {
        // Modül içindeki asset linkini kullanıyoruz
        $css_link = modul_linki('odeme', 'paytr', 'assets/css/modul.css');
        echo '<link rel="stylesheet" href="' . $css_link . '">';
    }
});
