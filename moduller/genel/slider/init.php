<?php
/**
 * Module Name: Bozkurt Premium Slider
 * Description: Shoptimizer ve diğer temalar için gelişmiş, kanca tabanlı slider modülü.
 * Version: 1.1.0
 * Author: Bozkurt Core Team
 */

hook_ekle('anasayfa_slider', function() {
    // Slider şablonunu yükle
    include __DIR__ . '/templates/main-slider.php';
});
