<?php gorunum('ust'); ?>

<!-- Hero Slider Area -->
<section class="shoptimizer-hero-section" style="margin-bottom: 50px;">
    <?php
    // Slider varsa çalıştır, yoksa Shoptimizer tarzı statik banner göster
    if (hook_ekli_mi('anasayfa_slider')) {
        hook_calistir('anasayfa_slider');
    } else {
        ?>
        <div
            style="background: #f8fafc; border-radius: 30px; padding: 60px; display: flex; align-items: center; justify-content: space-between; overflow: hidden; position: relative;">
            <div style="max-width: 50%; z-index: 2;">
                <span
                    style="color: var(--primary); font-weight: 800; letter-spacing: 2px; text-transform: uppercase; font-size: 14px;">Yeni
                    Sezon Koleksiyonu</span>
                <h1 style="font-size: 4rem; font-weight: 900; line-height: 1; color: var(--dark); margin: 20px 0;">Hız ve
                    Dönüşüm <br>Bir Arada.</h1>
                <p style="color: var(--gray); font-size: 1.1rem; margin-bottom: 30px;">Bozok E-Ticaret için özel olarak
                    uyarlanmış Svs Tema ile satışlarınızı katlayın.</p>
                <a href="<?= url('urunler') ?>" class="buton"
                    style="background: var(--dark); color: #fff; padding: 18px 45px; border-radius: 40px; font-weight: 800; text-decoration: none; display: inline-block;">Hemen
                    Alışverişe Başla <i class="fas fa-arrow-right"></i></a>
            </div>
            <div
                style="position: absolute; right: -50px; bottom: -50px; width: 600px; height: 600px; background: var(--primary); border-radius: 50%; opacity: 0.1;">
            </div>
        </div>
    <?php } ?>
</section>

<!-- Avantaj Kutuları -->
<div
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 60px;">
    <div
        style="background: #fff; padding: 25px; border-radius: 20px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 20px;">
        <i class="fas fa-truck" style="font-size: 24px; color: var(--primary);"></i>
        <div>
            <strong style="display: block; color: var(--dark);">Hızlı Kargo</strong>
            <span style="color: var(--gray); font-size: 13px;">Aynı gün kargo imkanı</span>
        </div>
    </div>
    <div
        style="background: #fff; padding: 25px; border-radius: 20px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 20px;">
        <i class="fas fa-shield-alt" style="font-size: 24px; color: var(--primary);"></i>
        <div>
            <strong style="display: block; color: var(--dark);">Güvenli Ödeme</strong>
            <span style="color: var(--gray); font-size: 13px;">SSL ve PayTR altyapısı</span>
        </div>
    </div>
    <div
        style="background: #fff; padding: 25px; border-radius: 20px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 20px;">
        <i class="fas fa-undo" style="font-size: 24px; color: var(--primary);"></i>
        <div>
            <strong style="display: block; color: var(--dark);">Kolay İade</strong>
            <span style="color: var(--gray); font-size: 13px;">14 gün içinde değişim</span>
        </div>
    </div>
</div>

<!-- Öne Çıkan Ürünler -->
<div style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h2 style="font-weight: 900; color: var(--dark); font-size: 2.2rem; margin: 0;">Sizin İçin Seçtiklerimiz</h2>
        <p style="color: var(--gray); margin-top: 5px;">En çok tercih edilen, en kaliteli ürünler.</p>
    </div>
    <a href="<?= url('urunler') ?>" style="color: var(--primary); font-weight: 800; text-decoration: none;">Tümünü
        Gör <i class="fas fa-arrow-right"></i></a>
</div>

<div class="shoptimizer-products-grid"
    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px;">
    <?php foreach ($one_cikanlar as $u): ?>
        <?php gorunum('urun-kart', ['u' => $u]); ?>
    <?php endforeach; ?>
</div>

<?php gorunum('alt'); ?>