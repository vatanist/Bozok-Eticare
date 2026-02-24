<?php gorunum('ust', ['sayfa_basligi' => $urun['name']]); ?>

<div class="shoptimizer-product-detail"
    style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin-top: 20px;">

    <!-- Sol: Ürün Galerisi -->
    <div class="product-gallery">
        <div style="background: #fff; border-radius: 24px; border: 1px solid #f1f5f9; overflow: hidden; margin-bottom: 20px; transition: 0.3s;"
            onmouseover="this.style.borderColor='var(--primary)'">
            <img id="mainImage" src="<?= resim_linki($urun['image']) ?>" alt="<?= temiz($urun['name']) ?>"
                style="width: 100%; height: auto; display: block;">
        </div>

        <?php $gallery = json_decode($urun['images'] ?? '[]', true); ?>
        <?php if (!empty($gallery)): ?>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <div class="thumb active" onclick="changeImage('<?= resim_linki($urun['image']) ?>', this)"
                    style="border: 2px solid var(--primary); border-radius: 12px; cursor: pointer; overflow: hidden;">
                    <img src="<?= resim_linki($urun['image']) ?>" style="width: 100%; aspect-ratio: 1; object-fit: cover;">
                </div>
                <?php foreach ($gallery as $img): ?>
                    <div class="thumb" onclick="changeImage('<?= resim_linki($img) ?>', this)"
                        style="border: 2px solid transparent; border-radius: 12px; cursor: pointer; overflow: hidden; transition: 0.3s;">
                        <img src="<?= resim_linki($img) ?>" style="width: 100%; aspect-ratio: 1; object-fit: cover;">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sağ: Ürün Bilgileri -->
    <div class="product-info-wrap">
        <nav style="font-size: 13px; color: var(--gray); margin-bottom: 15px;">
            <a href="<?= BASE_URL ?>" style="color: inherit; text-decoration: none;">Anasayfa</a> /
            <a href="<?= BASE_URL ?>/kategori.php?slug=<?= link_yap($urun['kategori_adi']) ?>"
                style="color: inherit; text-decoration: none;">
                <?= temiz($urun['kategori_adi']) ?>
            </a>
        </nav>

        <h1
            style="font-size: 2.5rem; font-weight: 900; color: var(--dark); margin: 0 0 15px 0; letter-spacing: -1px; line-height: 1.1;">
            <?= temiz($urun['name']) ?>
        </h1>

        <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 30px;">
            <div style="display: flex; align-items: center; gap: 5px;">
                <div style="color: #fbbf24; font-size: 14px;">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <span style="font-size: 13px; color: var(--gray); font-weight: 600;">(4.9 / 5.0)</span>
            </div>
            <div style="width: 1px; height: 15px; background: #e2e8f0;"></div>
            <div style="font-size: 13px; color: var(--gray); font-weight: 600;">SKU:
                <?= temiz($urun['sku'] ?: 'VC-' . $urun['id']) ?>
            </div>
            <div style="color: var(--primary); font-weight: 800; font-size: 13px;"><i class="fas fa-check-circle"></i>
                Stokta Var</div>
        </div>

        <div style="background: #f8fafc; border-radius: 20px; padding: 30px; margin-bottom: 40px;">
            <?php if ($urun['discount_price'] > 0): ?>
                <div style="display: flex; align-items: baseline; gap: 15px; margin-bottom: 5px;">
                    <span style="font-size: 2.8rem; font-weight: 900; color: var(--dark);">
                        <?= para_yaz($urun['discount_price']) ?>
                    </span>
                    <span style="font-size: 1.4rem; color: var(--gray); text-decoration: line-through; font-weight: 500;">
                        <?= para_yaz($urun['price']) ?>
                    </span>
                    <span
                        style="background: #ef4444; color: #fff; padding: 4px 12px; border-radius: 30px; font-weight: 800; font-size: 13px;">%-
                        <?= round((($urun['price'] - $urun['discount_price']) / $urun['price']) * 100) ?> İNDİRİM
                    </span>
                </div>
            <?php else: ?>
                <span style="font-size: 2.8rem; font-weight: 900; color: var(--dark);">
                    <?= para_yaz($urun['price']) ?>
                </span>
            <?php endif; ?>
            <p style="color: var(--gray); font-size: 14px; margin: 15px 0 0 0;">KDV Dahil, Ücretsiz Kargo Avantajıyla.
            </p>
        </div>

        <form action="<?= BASE_URL ?>/sepet.php?islem=ekle" method="POST">
            <input type="hidden" name="urun_id" value="<?= $urun['id'] ?>">

            <div style="display: flex; gap: 15px; align-items: stretch; margin-bottom: 30px;">
                <div
                    style="display: flex; align-items: center; background: #fff; border: 2px solid #f1f5f9; border-radius: 30px; padding: 0 15px; width: 130px;">
                    <button type="button" onclick="qtyChange(-1)"
                        style="background: none; border: none; font-size: 1.5rem; color: var(--gray); cursor: pointer; width: 30px;">-</button>
                    <input type="number" name="adet" id="qtyInput" value="1" min="1"
                        style="width: 40px; text-align: center; border: none; font-weight: 800; font-size: 1.1rem; outline: none;">
                    <button type="button" onclick="qtyChange(1)"
                        style="background: none; border: none; font-size: 1.5rem; color: var(--gray); cursor: pointer; width: 30px;">+</button>
                </div>
                <button type="submit" class="buton"
                    style="flex: 1; background: var(--dark); color: #fff; border-radius: 40px; font-weight: 900; font-size: 1.2rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s;"
                    onmouseover="this.style.background='var(--primary)'"
                    onmouseout="this.style.background='var(--dark)'">
                    <i class="fas fa-shopping-basket"></i> SEPETE EKLE
                </button>
            </div>
        </form>

        <div style="border-top: 1px solid #f1f5f9; padding-top: 30px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div
                    style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; color: var(--dark);">
                    <i class="fas fa-truck-moving" style="color: var(--primary);"></i> 24 Saatte Kargo</div>
                <div
                    style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; color: var(--dark);">
                    <i class="fas fa-shield-check" style="color: var(--primary);"></i> Güvenli Alışveriş</div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Section & Description -->
<div style="margin-top: 80px; border-top: 1px solid #f1f5f9; padding-top: 60px;">
    <div style="max-width: 800px; margin: 0 auto; line-height: 1.8; color: #475569; font-size: 1.1rem;">
        <h3 style="font-weight: 900; color: var(--dark); font-size: 1.8rem; margin-bottom: 30px; text-align: center;">
            Üretici Açıklaması</h3>
        <?= $urun['description'] ?: 'Dostum, bu harika ürün için henüz bir açıklama girilmemiş.' ?>
    </div>
</div>

<script>
    function changeImage(src, thumb) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.thumb').forEach(t => {
            t.style.borderColor = 'transparent';
        });
        thumb.style.borderColor = 'var(--primary)';
    }

    function qtyChange(amt) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value) + amt;
        if (val < 1) val = 1;
        input.value = val;
    }
</script>

<?php gorunum('alt'); ?>