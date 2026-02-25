<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <?php
    $siteName = getSetting('site_name', 'Bozok E-Ticaret');
    $siteDesc = getSetting('site_description', 'Modern E-Ticaret Platformu');
    $fullTitle = isset($pageTitle) ? e($pageTitle) . ' - ' . e($siteName) : e($siteName);
    $metaDesc = isset($pageDesc) ? e($pageDesc) : e($siteDesc);
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $siteBase = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL;
    $canonical = isset($pageCanonical) ? $pageCanonical : ($siteBase . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
    $ogImage = isset($pageOgImage) ? $pageOgImage : ($siteBase . '/assets/images/og-default.png');
    ?>
    <title><?= $fullTitle ?></title>
    <meta name="description" content="<?= $metaDesc ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="<?= isset($pageOgType) ? e($pageOgType) : 'website' ?>">
    <meta property="og:title" content="<?= $fullTitle ?>">
    <meta property="og:description" content="<?= $metaDesc ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:locale" content="tr_TR">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $fullTitle ?>">
    <meta name="twitter:description" content="<?= $metaDesc ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/layout.css">
    <?php
    $allowedThemes = ['default', 'green', 'purple', 'red'];
    $activeTheme = getSetting('site_theme', 'default');
    if (!in_array($activeTheme, $allowedThemes))
        $activeTheme = 'default';
    if ($activeTheme !== 'default'):
        ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/themes/<?= $activeTheme ?>.css">
    <?php endif; ?>
    <?php if (isset($pageExtraHead))
        echo $pageExtraHead; ?>
</head>

<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-left">
                <a href="tel:<?= e(getSetting('site_phone')) ?>"><i class="fas fa-phone"></i>
                    <?= e(getSetting('site_phone', '+90 555 000 00 00')) ?>
                </a>
                <a href="mailto:<?= e(getSetting('site_email')) ?>"><i class="fas fa-envelope"></i>
                    <?= e(getSetting('site_email', 'info@vcommerce.com')) ?>
                </a>
            </div>
            <div class="top-bar-right">
                <span><i class="fas fa-truck"></i>
                    <?= formatPrice(floatval(getSetting('free_shipping_limit', 2000))) ?> üzeri ücretsiz kargo
                </span>
                <div class="social-links">
                    <a href="<?= e(getSetting('instagram', '#')) ?>" target="_blank"><i
                            class="fab fa-instagram"></i></a>
                    <a href="<?= e(getSetting('facebook', '#')) ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                    <a href="<?= e(getSetting('twitter', '#')) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="main-header">
        <div class="header-inner">
            <a href="<?= BASE_URL ?>/" class="logo">
                <div class="logo-icon">V</div>
                Bozok E-Ticaret
            </a>

            <div class="search-bar">
                <form action="<?= BASE_URL ?>/search.php" method="GET">
                    <input type="text" name="q" placeholder="Ürün, kategori veya marka ara..."
                        value="<?= e($_GET['q'] ?? '') ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="header-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/client/" class="header-action">
                        <i class="fas fa-user"></i>
                        <span>Hesabım</span>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/client/login.php" class="header-action">
                        <i class="fas fa-user"></i>
                        <span>Giriş Yap</span>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/cart.php" class="header-action">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sepet</span>
                    <?php $cartCount = getCartCount(); ?>
                    <span class="badge cart-badge-count" style="<?= $cartCount == 0 ? 'display:none' : '' ?>">
                        <?= $cartCount ?>
                    </span>
                </a>
            </div>

            <button class="mobile-toggle"><i class="fas fa-bars"></i></button>
        </div>

        <!-- Navigation -->
        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="<?= BASE_URL ?>/"
                        class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['page']) ? 'active' : '' ?>"><i
                            class="fas fa-home"></i> Ana Sayfa</a></li>
                <li><a href="<?= BASE_URL ?>/products.php"><i class="fas fa-box-open"></i> Tüm Ürünler</a></li>
                <li><a href="<?= BASE_URL ?>/products.php?featured=1"><i class="fas fa-star"></i> Öne Çıkanlar</a></li>
                <li><a href="<?= BASE_URL ?>/products.php?sort=newest"><i class="fas fa-bolt"></i> Yeni Ürünler</a></li>
            </ul>
        </nav>
    </header>

    <main>
