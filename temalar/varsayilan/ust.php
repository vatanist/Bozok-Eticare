<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= temiz($sayfa_basligi ?? 'V-Commerce - Premium E-Ticaret') ?></title>

    <!-- Tasarım ve İkonlar -->
    <link rel="stylesheet" href="<?= tema_linki('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modül Head Kancası -->
    <?php hook_calistir('head_sonu'); ?>
</head>

<body>
    <!-- Modül Body Başlangıç Kancası -->
    <?php hook_calistir('body_basi'); ?>

    <!-- Üst Bilgi Çubuğu (Top Bar) -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-left">
                <span><i class="fas fa-shipping-fast"></i> 1000 TL Üzeri Ücretsiz Kargo</span>
            </div>
            <div class="top-bar-right">
                <a href="<?= BASE_URL ?>/yardim">Yardım</a>
                <a href="<?= BASE_URL ?>/iletisim">İletişim</a>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Ana Header -->
    <header class="main-header">
        <div class="header-inner">
            <a href="<?= BASE_URL ?>" class="logo">
                <div class="logo-icon">V</div>
                <span>V-Commerce</span>
            </a>

            <div class="search-bar">
                <form action="<?= BASE_URL ?>/search.php" method="GET">
                    <input type="text" name="q" value="<?= temiz($sorgu ?? '') ?>"
                        placeholder="Ürün veya kategori ara...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="header-actions">
                <?php if (giris_yapilmis_mi()): ?>
                    <a href="<?= BASE_URL ?>/client/profile.php" class="header-action">
                        <i class="far fa-user"></i>
                        <span>Hesabım</span>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/client/login.php" class="header-action">
                        <i class="far fa-user"></i>
                        <span>Giriş Yap</span>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/sepet.php" class="header-action">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Sepetim</span>
                    <?php $sepet_sayi = getCartCount(); ?>
                    <span class="badge" id="cart-badge"
                        style="<?= $sepet_sayi > 0 ? '' : 'display:none;' ?>"><?= $sepet_sayi ?></span>
                </a>
            </div>
        </div>
    </header>

    <!-- Navigasyon -->
    <nav class="main-nav">
        <ul class="nav-list">
            <li><a href="<?= BASE_URL ?>" class="active">Anasayfa</a></li>
            <?php foreach (kategorileri_getir() as $k): ?>
                <li><a href="<?= BASE_URL ?>/kategori/<?= $k['slug'] ?>"><?= temiz($k['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <main class="container" style="margin-top:20px; min-height:60vh;">
        <?php mesaj_goster('genel'); ?>