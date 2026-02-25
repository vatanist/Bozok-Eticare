<?php
/**
 * Shoptimizer Port - Alt Bilgi
 */
?>
</div><!-- .col-full -->
</div><!-- #content -->

<?php hook_calistir('footer_basi'); ?>

<footer id="colophon" class="site-footer"
    style="background: #fff; padding: 60px 0; border-top: 1px solid #f1f5f9; margin-top: 50px;">
    <div class="col-full" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-bottom: 40px;">
            <!-- Kurumsal -->
            <div>
                <h4 style="font-weight: 800; margin-bottom: 20px; color: var(--dark);">Kurumsal</h4>
                <ul
                    style="list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; font-size: 14px; color: var(--gray);">
                    <li><a href="#" style="color: inherit; text-decoration: none;">Hakkımızda</a></li>
                    <li><a href="#" style="color: inherit; text-decoration: none;">İletişim</a></li>
                    <li><a href="#" style="color: inherit; text-decoration: none;">KVKK</a></li>
                </ul>
            </div>
            <!-- Müşteri Hizmetleri -->
            <div>
                <h4 style="font-weight: 800; margin-bottom: 20px; color: var(--dark);">Yardım</h4>
                <ul
                    style="list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; font-size: 14px; color: var(--gray);">
                    <li><a href="#" style="color: inherit; text-decoration: none;">Sıkça Sorulan Sorular</a></li>
                    <li><a href="#" style="color: inherit; text-decoration: none;">Kargo Takip</a></li>
                    <li><a href="#" style="color: inherit; text-decoration: none;">İade Şartları</a></li>
                </ul>
            </div>
            <!-- İletişim -->
            <div>
                <h4 style="font-weight: 800; margin-bottom: 20px; color: var(--dark);">Bize Ulaşın</h4>
                <div style="font-size: 14px; color: var(--gray);">
                    <p><i class="fas fa-phone-alt"></i> 0850 000 00 00</p>
                    <p><i class="fas fa-envelope"></i> destek@vcommerce.com</p>
                </div>
            </div>
        </div>

        <div
            style="border-top: 1px solid #f1f5f9; padding-top: 30px; display: flex; justify-content: space-between; align-items: center; color: var(--gray); font-size: 13px;">
            <p>&copy;
                <?= date('Y') ?> Bozok E-Ticaret Svs Tema. Tüm Hakları Saklıdır.
            </p>
            <div style="display: flex; gap: 15px; font-size: 18px;">
                <i class="fab fa-facebook-f"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-twitter"></i>
            </div>
        </div>
    </div>
</footer>

</div><!-- #page -->

<?php hook_calistir('footer_sonu'); ?>

<?php require_once __DIR__ . '/../../includes/cerez-banner.php'; ?>
<?php hook_calistir('body_sonu'); ?>

</body>

</html>