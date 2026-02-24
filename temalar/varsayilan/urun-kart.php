<?php
/**
 * Ürün Kartı Bileşeni - $urun değişkeni gerektirir
 */
$fiyat = $urun['discount_price'] ?: $urun['price'];
$indirim_var = $urun['discount_price'] && $urun['discount_price'] < $urun['price'];
$indirim_orani = $indirim_var ? round((($urun['price'] - $urun['discount_price']) / $urun['price']) * 100) : 0;
$resim_url = resim_linki($urun['image']);

// Boyut bazlı ek bilgi (opsiyonel)
$boyut_metni = "";
if (isset($urun['width']) && isset($urun['height'])) {
    $m2 = round(($urun['width'] * $urun['height']) / 10000, 2);
    $boyut_metni = "{$urun['width']}x{$urun['height']} cm ({$m2} m²)";
}
?>
<div class="product-card"
    style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position:relative; display:flex; flex-direction:column; height:100%;"
    onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.1)';"
    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">

    <!-- Resim ve Rozetler -->
    <div
        style="position:relative; aspect-ratio:1/1; overflow:hidden; background:#f9f9f9; display:flex; align-items:center; justify-content:center;">
        <a href="<?= BASE_URL ?>/urun/<?= $urun['slug'] ?>" style="display:block; width:100%; height:100%;">
            <img src="<?= $resim_url ?>" alt="<?= temiz($urun['name']) ?>"
                style="width:100%; height:100%; object-fit:contain; transition:0.5s;"
                onmouseover="this.style.transform='scale(1.1)';" onmouseout="this.style.transform='scale(1)';"
                loading="lazy">
        </a>

        <div style="position:absolute; top:12px; left:12px; display:flex; flex-direction:column; gap:6px;">
            <?php if ($indirim_var): ?>
                <span
                    style="background:var(--danger); color:#fff; padding:4px 10px; border-radius:20px; font-size:0.7rem; font-weight:800; box-shadow:0 4px 10px rgba(220, 38, 38, 0.3);">%<?= $indirim_orani ?>
                    İndirim</span>
            <?php endif; ?>
            <?php if (strtotime($urun['created_at'] ?? 'now') > strtotime('-15 days')): ?>
                <span
                    style="background:var(--accent); color:#fff; padding:4px 10px; border-radius:20px; font-size:0.7rem; font-weight:800; box-shadow:0 4px 10px rgba(5, 150, 105, 0.3);">Yeni
                    Ürün</span>
            <?php endif; ?>
        </div>

        <!-- Hızlı İşlem Butonları (Resim Üstünde Hoverda Belirir - Desktop İçin Stil Dosyasında Yönetilir, Burada Temel Yapı) -->
        <div class="card-overlay-actions"
            style="position:absolute; bottom:15px; right:15px; display:flex; flex-direction:column; gap:8px; opacity:0; transition:0.3s; transform:translateX(10px);">
            <a href="<?= BASE_URL ?>/sepet.php?ekle=<?= $urun['id'] ?>"
                style="width:40px; height:40px; border-radius:12px; background:#fff; color:var(--primary); display:flex; align-items:center; justify-content:center; box-shadow:0 5px 15px rgba(0,0,0,0.1); border:1px solid #f1f1f1;"
                title="Sepete Ekle"><i class="fas fa-shopping-basket"></i></a>
            <button onclick="favoriEkle(<?= $urun['id'] ?>)"
                style="width:40px; height:40px; border-radius:12px; background:#fff; color:var(--danger); display:flex; align-items:center; justify-content:center; box-shadow:0 5px 15px rgba(0,0,0,0.1); border:1px solid #f1f1f1;"
                title="Favoriye Ekle"><i class="far fa-heart"></i></button>
        </div>
    </div>

    <!-- Ürün Bilgileri -->
    <div style="padding:20px; flex:1; display:flex; flex-direction:column;">
        <?php if (!empty($urun['kategori_adi'])): ?>
            <small
                style="color:var(--gray); text-transform:uppercase; font-size:0.65rem; font-weight:700; letter-spacing:0.05em; margin-bottom:5px; display:block;"><?= temiz($urun['kategori_adi']) ?></small>
        <?php endif; ?>

        <h3 style="font-size:0.95rem; font-weight:700; margin:0 0 10px; height:42px; overflow:hidden; line-height:1.4;">
            <a href="<?= BASE_URL ?>/urun/<?= $urun['slug'] ?>"
                style="color:var(--dark); text-decoration:none; transition:0.2s;"
                onmouseover="this.style.color='var(--primary)';"
                onmouseout="this.style.color='var(--dark)';"><?= temiz($urun['name']) ?></a>
        </h3>

        <?php if ($boyut_metni): ?>
            <div
                style="font-size:0.75rem; color:var(--gray-light); margin-bottom:10px; display:flex; align-items:center; gap:5px;">
                <i class="fas fa-ruler-combined"></i> <?= $boyut_metni ?>
            </div>
        <?php endif; ?>

        <div style="margin-top:auto; display:flex; align-items:flex-end; gap:10px;">
            <div style="display:flex; flex-direction:column;">
                <?php if ($indirim_var): ?>
                    <span
                        style="color:var(--gray-light); text-decoration:line-through; font-size:0.8rem; font-weight:500;"><?= para_yaz($urun['price']) ?></span>
                <?php endif; ?>
                <span
                    style="font-size:1.3rem; font-weight:900; color:var(--primary); line-height:1;"><?= para_yaz($fiyat) ?></span>
            </div>

            <a href="<?= BASE_URL ?>/sepet.php?ekle=<?= $urun['id'] ?>"
                style="margin-left:auto; background:var(--primary); color:#fff; width:45px; height:45px; border-radius:14px; display:flex; align-items:center; justify-content:center; box-shadow:0 8px 16px rgba(26, 86, 219, 0.2); text-decoration:none; transition:0.3s;"
                onmouseover="this.style.background='var(--primary-dark)'; this.style.transform='scale(1.05)';"
                onmouseout="this.style.background='var(--primary)'; this.style.transform='scale(1)';">
                <i class="fas fa-cart-arrow-down"></i>
            </a>
        </div>
    </div>
</div>

<script>
    // Hover efektini JS ile tetiklemek için (Style dosyasında yoksa)
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            const overlay = card.querySelector('.card-overlay-actions');
            if (overlay) {
                overlay.style.opacity = '1';
                overlay.style.transform = 'translateX(0)';
            }
        });
        card.addEventListener('mouseleave', () => {
            const overlay = card.querySelector('.card-overlay-actions');
            if (overlay) {
                overlay.style.opacity = '0';
                overlay.style.transform = 'translateX(10px)';
            }
        });
    });
</script>