<!doctype html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= isset($sayfa_basligi) ? $sayfa_basligi . ' - ' : '' ?>V-Commerce Shoptimizer
    </title>

    <!-- Shoptimizer CSS Assets -->
    <link rel="stylesheet" href="<?= tema_linki('style.css') ?>">
    <link rel="stylesheet" href="<?= tema_linki('assets/css/main/main.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <?php hook_calistir('ust_basi'); ?>
</head>

<body class="shoptimizer-port theme-shoptimizer">

    <div id="page" class="hfeed site">

        <!-- Top Bar -->
        <div class="shoptimizer-top-bar"
            style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 0; font-size: 13px;">
            <div class="col-full"
                style="display: flex; justify-content: space-between; max-width: 1200px; margin: 0 auto; padding: 0 15px;">
                <div class="top-bar-left">
                    <i class="fas fa-truck"></i> Ücretsiz Kargo - 1000 TL ve Üzeri
                </div>
                <div class="top-bar-right" style="display: flex; gap: 20px;">
                    <a href="<?= BASE_URL ?>/client/index.php" style="color: inherit; text-decoration: none;"><i
                            class="fas fa-user"></i> Hesabım</a>
                    <a href="<?= BASE_URL ?>/sayfa/iletisim" style="color: inherit; text-decoration: none;"><i
                            class="fas fa-headset"></i> Yardım</a>
                </div>
            </div>
        </div>

        <header id="masthead" class="site-header"
            style="background: #fff; padding: 25px 0; border-bottom: 1px solid #f1f5f9;">
            <div class="main-header col-full"
                style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; padding: 0 15px;">

                <!-- Logo -->
                <div class="site-branding">
                    <a href="<?= BASE_URL ?>"
                        style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                        <div
                            style="background: var(--primary); color: #fff; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 20px;">
                            V</div>
                        <span style="font-size: 24px; font-weight: 800; color: #1e293b; letter-spacing: -1px;">SHOP<span
                                style="color: var(--primary);">TIMIZER</span></span>
                    </a>
                </div>

                <!-- Arama -->
                <div class="product-search" style="flex: 1; margin: 0 50px; position: relative;">
                    <form action="<?= BASE_URL ?>/search.php" method="GET">
                        <input type="text" name="q" placeholder="Ürün, kategori veya marka ara..."
                            style="width: 100%; padding: 12px 25px; border: 2px solid #f1f5f9; border-radius: 30px; outline: none; transition: 0.3s;"
                            onfocus="this.style.borderColor='var(--primary)'">
                        <button type="submit"
                            style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); border: none; background: none; color: var(--gray);"><i
                                class="fas fa-search"></i></button>
                    </form>
                </div>

                <!-- Header Actions -->
                <div class="header-actions" style="display: flex; gap: 25px; align-items: center;">
                    <div class="header-cart">
                        <a href="<?= BASE_URL ?>/sepet.php"
                            style="position: relative; color: var(--dark); font-size: 22px;">
                            <i class="fas fa-shopping-basket"></i>
                            <span
                                style="position: absolute; top: -8px; right: -12px; background: var(--primary); color: #fff; font-size: 11px; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                <?= getCartCount() ?>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="col-full-nav" style="background: #fff; padding: 15px 0; border-bottom: 2px solid #f8fafc;">
            <div class="col-full" style="max-width: 1200px; margin: 0 auto; padding: 0 15px; display: flex; gap: 30px;">
                <a href="<?= BASE_URL ?>"
                    style="font-weight: 700; color: var(--dark); text-decoration: none;">Anasayfa</a>
                <a href="<?= BASE_URL ?>/urunler.php"
                    style="font-weight: 700; color: var(--dark); text-decoration: none;">Tüm Ürünler</a>
                <!-- Kategoriler buraya dinamik gelebilir -->
            </div>
        </div>

        <div id="content" class="site-content" style="padding-top: 40px;">
            <div class="col-full" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">