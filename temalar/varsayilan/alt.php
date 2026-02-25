</div><!-- container sonu -->
</main>

<?php hook_calistir('footer_basi'); ?>

<footer class="site-footer">
    <div class="footer-newsletter">
        <div class="container newsletter-inner">
            <div class="newsletter-text">
                <h3>Bülteimize Katılın</h3>
                <p>İndirimlerden ve yeni ürünlerden ilk siz haberdar olun!</p>
            </div>
            <form class="newsletter-form"
                onsubmit="event.preventDefault(); alert('Bültene kaydınız alındı!'); this.reset();">
                <input type="email" placeholder="E-posta adresiniz..." required>
                <button type="submit">Abone Ol</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="logo" style="margin-bottom:20px;">
                    <div class="logo-icon" style="width:30px; height:30px; font-size:14px;">V</div>
                    <span style="font-size:1.1rem;">Bozok E-Ticaret</span>
                </div>
                <p>Türkiye'nin en yeni nesil e-ticaret deneyimi. Bozkurt Core altyapısı ile hızlı, güvenli ve modern
                    alışveriş. Premium tasarım ve mükemmel kullanıcı deneyimi.</p>
                <div class="footer-social">
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter-x"></i></a>
                    <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Kurumsal</h4>
                <ul>
                    <li><a href="<?= url('hakkimizda') ?>">Hakkımızda</a></li>
                    <li><a href="<?= url('iletisim') ?>">İletişim</a></li>
                    <li><a href="<?= url('gizlilik-politikasi') ?>">Gizlilik Politikası</a></li>
                    <li><a href="<?= url('mesafeli-satis-sozlesmesi') ?>">Satış Sözleşmesi</a></li>
                    <li><a href="<?= url('iptal-iade') ?>">İptal ve İade</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Hızlı Erişim</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/">Ana Sayfa</a></li>
                    <li><a href="<?= url('ara') ?>">Ürünleri Ara</a></li>
                    <li><a href="<?= url('hesabim/profil') ?>">Profil Ayarları</a></li>
                    <li><a href="<?= url('hesabim/siparisler') ?>">Siparişlerim</a></li>
                    <li><a href="<?= url('hesabim/favoriler') ?>">Favorilerim</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>İletişim Bilgileri</h4>
                <div class="contact-item"><i class="fas fa-map-marker-alt"></i> İstanbul, Türkiye</div>
                <div class="contact-item"><i class="fas fa-phone"></i> 0850 000 00 00</div>
                <div class="contact-item"><i class="fas fa-envelope"></i> destek@vcommerce.com</div>
                <div class="payment-methods"
                    style="margin-top:20px; display:flex; gap:10px; font-size:1.5rem; color:var(--gray-light);">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-paypal"></i>
                    <i class="fab fa-cc-stripe"></i>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Bozok E-Ticaret. Tüm hakları saklıdır. <br><small>Bozkurt Core Premium Experience
                    v1.1</small></p>
        </div>
    </div>
</footer>

<script src="<?= tema_linki('assets/js/main.js') ?>"></script>

<!-- Modül Footer Kancası -->
<?php hook_calistir('footer_sonu'); ?>
<?php hook_calistir('body_sonu'); ?>

</body>

</html>