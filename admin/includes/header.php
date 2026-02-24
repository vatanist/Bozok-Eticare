<?php
// Admin Header - requires $adminPage variable
require_once __DIR__ . '/../../config/config.php';
requireAdmin();
$adminUser = currentUser();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
                <?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Admin | V-Commerce
        </title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
                rel="stylesheet">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>

<body>
        <aside class="admin-sidebar">
                <div class="admin-logo">
                        <div class="icon">V</div> V-Commerce
                </div>
                <nav class="admin-nav">
                        <span class="admin-nav-section">E-Ticaret</span>
                        <a href="<?= BASE_URL ?>/admin/"
                                class="<?= ($adminPage ?? '') === 'dashboard' ? 'active' : '' ?>"><i
                                        class="fas fa-chart-line"></i> Genel Bakış</a>
                        <a href="<?= BASE_URL ?>/admin/siparisler.php"
                                class="<?= ($adminPage ?? '') === 'orders' ? 'active' : '' ?>"><i
                                        class="fas fa-shopping-bag"></i> Sipariş Yönetimi</a>
                        <a href="<?= BASE_URL ?>/admin/urunler.php"
                                class="<?= ($adminPage ?? '') === 'products' ? 'active' : '' ?>"><i
                                        class="fas fa-box"></i> Ürün Yönetimi</a>
                        <a href="<?= BASE_URL ?>/admin/kategoriler.php"
                                class="<?= ($adminPage ?? '') === 'categories' ? 'active' : '' ?>"><i
                                        class="fas fa-tags"></i> Kategori Yönetimi</a>
                        <a href="<?= BASE_URL ?>/admin/musteriler.php"
                                class="<?= ($adminPage ?? '') === 'customers' ? 'active' : '' ?>"><i
                                        class="fas fa-users"></i> Müşteri Yönetimi</a>
                        <a href="<?= BASE_URL ?>/admin/pazaryeri.php"
                                class="<?= ($adminPage ?? '') === 'marketplace' ? 'active' : '' ?>"><i
                                        class="fas fa-store"></i> Pazaryeri Yönetimi</a>

                        <span class="admin-nav-section">Pazarlama & Analitik</span>
                        <a href="<?= BASE_URL ?>/admin/affiliate.php"
                                class="<?= ($adminPage ?? '') === 'marketing' && $pageTitle == 'Affiliate Yönetimi' ? 'active' : '' ?>"><i
                                        class="fas fa-handshake"></i> Satış Ortaklığı</a>
                        <a href="<?= BASE_URL ?>/admin/banner.php"
                                class="<?= ($adminPage ?? '') === 'marketing' && $pageTitle == 'Banner Yönetimi' ? 'active' : '' ?>"><i
                                        class="fas fa-image"></i> Banner Yönetimi</a>
                        <a href="<?= BASE_URL ?>/admin/istatistikler.php"
                                class="<?= ($adminPage ?? '') === 'analytics' ? 'active' : '' ?>"><i
                                        class="fas fa-chart-line"></i> Ziyaretçi Analitiği</a>

                        <span class="admin-nav-section">Pazarlama</span>
                        <a href="<?= BASE_URL ?>/admin/kampanyalar.php"
                                class="<?= ($adminPage ?? '') === 'campaigns' ? 'active' : '' ?>"><i
                                        class="fas fa-percent"></i> Kampanya & Kupon</a>
                        <a href="<?= BASE_URL ?>/admin/mansetler.php"
                                class="<?= ($adminPage ?? '') === 'sliders' ? 'active' : '' ?>"><i
                                        class="fas fa-images"></i> Slider (Manşet)</a>

                        <span class="admin-nav-section">İçerik</span>
                        <a href="<?= BASE_URL ?>/admin/sayfalar.php"
                                class="<?= ($adminPage ?? '') === 'pages' ? 'active' : '' ?>"><i
                                        class="fas fa-file-alt"></i> Bilgi Sayfaları</a>

                        <span class="admin-nav-section">Sistem & Modüller</span>
                        <a href="<?= BASE_URL ?>/admin/secenekler.php"
                                class="<?= ($adminPage ?? '') === 'options' ? 'active' : '' ?>"><i
                                        class="fas fa-layer-group"></i> Seçenek (Varyasyon)</a>
                        <a href="<?= BASE_URL ?>/admin/moduller.php"
                                class="<?= ($adminPage ?? '') === 'extensions' ? 'active' : '' ?>"><i
                                        class="fas fa-puzzle-piece"></i> Modül Yönetimi</a>
                        <a href="<?= BASE_URL ?>/admin/kargo-ayarlari.php"
                                class="<?= ($adminPage ?? '') === 'shipping' ? 'active' : '' ?>"><i
                                        class="fas fa-truck"></i> Kargo Ayarları</a>
                        <a href="<?= BASE_URL ?>/admin/ayarlar.php"
                                class="<?= ($adminPage ?? '') === 'settings' ? 'active' : '' ?>"><i
                                        class="fas fa-cog"></i> Site Ayarları</a>
                        <a href="<?= BASE_URL ?>/admin/kullanicilar.php"
                                class="<?= ($adminPage ?? '') === 'users' ? 'active' : '' ?>"><i
                                        class="fas fa-user-shield"></i> Yönetici Hesapları</a>
                </nav>
                <div class="admin-nav-footer">
                        <a href="<?= BASE_URL ?>/" target="_blank"><i class="fas fa-external-link-alt"></i> Siteyi
                                Aç</a>
                        <a href="<?= BASE_URL ?>/admin/logout.php" style="margin-top:8px;color:#f87171"><i
                                        class="fas fa-sign-out-alt"></i> Güvenli Çıkış</a>
                </div>
        </aside>
        <div class="admin-content">