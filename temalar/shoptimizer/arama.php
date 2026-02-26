<?php gorunum('ust', ['sayfa_basligi' => '"' . temiz($sorgu) . '" için Arama Sonuçları']); ?>

<div class="shoptimizer-search-header"
    style="background: #fff; border-bottom: 2px solid #f1f5f9; padding: 40px 0; margin-bottom: 50px;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h1 style="font-size: 2.2rem; font-weight: 900; color: var(--dark); margin-bottom: 10px;">Arama Sonuçları</h1>
        <p style="color: var(--gray); font-size: 1.1rem;">"<strong>
                <?= temiz($sorgu) ?>
            </strong>" için
            <?= count($urunler) ?> sonuç bulundu.
        </p>
    </div>
</div>

<div class="shoptimizer-search-results">
    <?php if (!empty($urunler)): ?>
        <div class="shoptimizer-products-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px;">
            <?php foreach ($urunler as $u): ?>
                <?php gorunum('urun-kart', ['u' => $u]); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 100px 0; background: #f8fafc; border-radius: 30px;">
            <i class="fas fa-search" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h2 style="font-weight: 800; color: #64748b;">Aradığınız ürün bulunamadı.</h2>
            <p style="color: var(--gray); margin-bottom: 30px;">Farklı anahtar kelimelerle tekrar aramayı deneyebilir veya
                ürünlerimize göz atabilirsiniz.</p>
            <a href="<?= BASE_URL ?>/urunler.php" class="buton"
                style="background: var(--dark); color: #fff; padding: 15px 40px; border-radius: 30px; font-weight: 800; text-decoration: none; display: inline-block;">Tüm
                Ürünleri Gör</a>
        </div>
    <?php endif; ?>
</div>

<?php gorunum('alt'); ?>