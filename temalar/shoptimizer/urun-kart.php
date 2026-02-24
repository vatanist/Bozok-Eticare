<?php
/**
 * Shoptimizer Port - Ürün Kartı
 */
$fiyat = $u['discount_price'] ?: $u['price'];
$indirim = $u['discount_price'] ? round((($u['price'] - $u['discount_price']) / $u['price']) * 100) : 0;
?>
<div class="shoptimizer-product-card"
    style="background: #fff; border-radius: 20px; overflow: hidden; transition: 0.4s; position: relative; border: 1px solid transparent;"
    onmouseover="this.style.borderColor='#f1f5f9'; this.style.transform='translateY(-10px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.05)';"
    onmouseout="this.style.borderColor='transparent'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">

    <!-- Ürün Görseli -->
    <a href="<?= BASE_URL ?>/urun-detay.php?id=<?= $u['id'] ?>"
        style="display: block; aspect-ratio: 1/1.2; background: #f8fafc; overflow: hidden; position: relative;">
        <img src="<?= resim_linki($u['image']) ?>" alt="<?= temiz($u['name']) ?>"
            style="width: 100%; height: 100%; object-fit: cover; transition: 0.6s;"
            onmouseover="this.style.transform='scale(1.1)';" onmouseout="this.style.transform='scale(1)';"
            onerror="this.src='https://placehold.co/400x500/f8fafc/64748b?text=Urun+Resmi';">

        <?php if ($indirim > 0): ?>
            <div
                style="position: absolute; top: 15px; left: 15px; background: #ef4444; color: #fff; padding: 5px 12px; border-radius: 30px; font-weight: 800; font-size: 12px;">
                %-
                <?= $indirim ?> İndirim
            </div>
        <?php endif; ?>
    </a>

    <!-- Ürün Bilgileri -->
    <div style="padding: 20px; text-align: center;">
        <div
            style="font-size: 13px; color: var(--gray); margin-bottom: 5px; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">
            Elektronik</div>
        <h3
            style="margin: 0 0 10px; font-weight: 800; color: var(--dark); font-size: 1rem; height: 2.4rem; overflow: hidden; line-height: 1.2;">
            <a href="<?= BASE_URL ?>/urun-detay.php?id=<?= $u['id'] ?>" style="color: inherit; text-decoration: none;">
                <?= temiz($u['name']) ?>
            </a>
        </h3>

        <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 20px;">
            <?php if ($u['discount_price']): ?>
                <span style="color: var(--gray); text-decoration: line-through; font-size: 0.9rem; font-weight: 500;">
                    <?= para_yaz($u['price']) ?>
                </span>
            <?php endif; ?>
            <span style="color: var(--primary); font-weight: 900; font-size: 1.3rem;">
                <?= para_yaz($fiyat) ?>
            </span>
        </div>

        <a href="<?= BASE_URL ?>/cart.php?action=add&id=<?= $u['id'] ?>" class="buton"
            style="background: var(--primary-light); color: var(--primary); width: 100%; display: block; padding: 12px; border-radius: 12px; font-weight: 800; text-decoration: none; transition: 0.3s;"
            onmouseover="this.style.background='var(--primary)'; this.style.color='#fff';"
            onmouseout="this.style.background='var(--primary-light)'; this.style.color='var(--primary)';">
            <i class="fas fa-shopping-basket"></i> Sepete Ekle
        </a>
    </div>
</div>