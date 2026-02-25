</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-col">
            <h4>Bozok E-Ticaret</h4>
            <p>Elektronik ürünlerde güvenilir alışveriş deneyimi. En yeni teknoloji ürünlerini en uygun fiyatlarla
                sizlere sunuyoruz.</p>
            <div class="footer-social">
                <a href="<?= e(getSetting('instagram', '#')) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="<?= e(getSetting('facebook', '#')) ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                <a href="<?= e(getSetting('twitter', '#')) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://wa.me/<?= e(getSetting('whatsapp', '')) ?>" target="_blank"><i
                        class="fab fa-whatsapp"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Hızlı Linkler</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>/">Ana Sayfa</a></li>
                <li><a href="<?= BASE_URL ?>/products.php">Tüm Ürünler</a></li>
                <li><a href="<?= BASE_URL ?>/products.php?featured=1">Öne Çıkanlar</a></li>
                <li><a href="<?= BASE_URL ?>/cart.php">Sepetim</a></li>
                <li><a href="<?= BASE_URL ?>/client/login.php">Hesabım</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Kategoriler</h4>
            <ul>
                <?php $footerCats = getCategories();
                $i = 0;
                foreach ($footerCats as $cat):
                    if ($i++ >= 6)
                        break; ?>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=<?= e($cat['slug']) ?>">
                            <?= e($cat['name']) ?>
                        </a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Kurumsal</h4>
            <ul>
                <?php
                try {
                    $footerPages = Database::fetchAll(
                        "SELECT title, slug FROM pages WHERE show_in_footer = 1 AND status = 1 ORDER BY sort_order, id LIMIT 8"
                    );
                    foreach ($footerPages as $fp):
                        ?>
                        <li><a href="<?= BASE_URL ?>/<?= e($fp['slug']) ?>"><?= e($fp['title']) ?></a></li>
                    <?php endforeach;
                } catch (Exception $e) { /* Tablo henüz yoksa sessizce geç */
                } ?>
            </ul>
        </div>
        <div class="footer-col">
            <h4>İletişim</h4>
            <div class="contact-item"><i class="fas fa-map-marker-alt"></i>
                <?= e(getSetting('site_address', 'İstanbul, Türkiye')) ?>
            </div>
            <div class="contact-item"><i class="fas fa-phone"></i>
                <?= e(getSetting('site_phone', '+90 555 000 00 00')) ?>
            </div>
            <div class="contact-item"><i class="fas fa-envelope"></i>
                <?= e(getSetting('site_email', 'info@vcommerce.com')) ?>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            &copy;
            <?= date('Y') ?> Bozok E-Ticaret. Tüm hakları saklıdır.
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>

<?php require_once __DIR__ . '/cerez-banner.php'; ?>

</body>

</html>
