<?php gorunum('ust', ['sayfa_basligi' => 'Fiyat Alarmları']); ?>

<div class="price-alerts-page" style="padding:40px 0;">
    <div class="client-layout" style="display:grid; grid-template-columns: 280px 1fr; gap:40px;">

        <!-- Sidebar -->
        <div class="client-sidebar-container">
            <?php gorunum('hesap-sidebar', ['aktif_sayfa' => 'price_alerts', 'kullanici' => $kullanici]); ?>
        </div>

        <!-- İçerik -->
        <div class="client-main-content">
            <div style="margin-bottom:30px;">
                <h1 style="font-weight:800; margin-bottom:10px;">Fiyat Alarmları</h1>
                <p style="color:var(--gray);">Takip ettiğin ürünlerin fiyatı düştüğünde seni anında haberdar ediyoruz.
                </p>
            </div>

            <?php mesaj_goster('price_alert'); ?>

            <?php if (empty($alarmlar)): ?>
                <div
                    style="text-align:center; padding:80px 20px; background:#fff; border:1px solid #e5e7eb; border-radius:16px;">
                    <div
                        style="width:80px; height:80px; background:var(--warning)15; color:var(--warning); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:2rem;">
                        <i class="far fa-bell"></i>
                    </div>
                    <h3 style="font-weight:800; margin-bottom:10px;">Henüz fiyat alarmı oluşturmadınız</h3>
                    <p style="color:var(--gray); margin-bottom:25px;">Ürün detay sayfalarından alarm kurarak indirimleri
                        takip edebilirsiniz.</p>
                    <a href="<?= BASE_URL ?>/urunler.php" class="buton"
                        style="background:var(--primary); color:#fff; padding:12px 30px; border-radius:30px; text-decoration:none; font-weight:700;">Ürünlere
                        Göz At</a>
                </div>
            <?php else: ?>
                <div style="display:grid; gap:20px;">
                    <?php foreach ($alarmlar as $a):
                        $guncel_fiyat = $a['discount_price'] ?: $a['price'];
                        $indirim_var = $guncel_fiyat < $a['original_price'];
                        $indirim_orani = $indirim_var ? round(($a['original_price'] - $guncel_fiyat) / $a['original_price'] * 100) : 0;
                        ?>
                        <div style="background:#fff; border:1px solid <?= $indirim_var ? 'var(--success-light)' : '#e5e7eb' ?>; border-radius:16px; padding:20px; display:flex; align-items:center; gap:25px; transition:0.3s; <?= $indirim_var ? 'background:#f0fdf4;' : '' ?>"
                            onmouseover="this.style.transform='translateY(-2px)'">
                            <a href="<?= BASE_URL ?>/urun-detay.php?slug=<?= $a['slug'] ?>" style="flex-shrink:0;">
                                <img src="<?= resim_linki($a['image']) ?>"
                                    style="width:80px; height:80px; object-fit:contain; background:#fff; border-radius:12px; border:1px solid #f3f4f6;">
                            </a>
                            <div style="flex:1;">
                                <a href="<?= BASE_URL ?>/urun-detay.php?slug=<?= $a['slug'] ?>"
                                    style="text-decoration:none; color:var(--dark); font-weight:700; display:block; margin-bottom:8px;">
                                    <?= temiz($a['name']) ?>
                                </a>
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <div>
                                        <small style="display:block; color:var(--gray); font-size:0.75rem;">Hedef Fiyat</small>
                                        <span
                                            style="font-weight:600; color:var(--gray); text-decoration:line-through; font-size:0.85rem;">
                                            <?= para_yaz($a['original_price']) ?>
                                        </span>
                                    </div>
                                    <div style="font-size:1.2rem; color:var(--gray-light);"><i
                                            class="fas fa-long-arrow-alt-right"></i></div>
                                    <div>
                                        <small style="display:block; color:var(--gray); font-size:0.75rem;">Güncel Fiyat</small>
                                        <span
                                            style="font-weight:800; color:<?= $indirim_var ? 'var(--success)' : 'var(--dark)' ?>; font-size:1.1rem;">
                                            <?= para_yaz($guncel_fiyat) ?>
                                        </span>
                                    </div>
                                    <?php if ($indirim_var): ?>
                                        <div
                                            style="background:var(--success); color:#fff; padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:800; box-shadow:0 4px 10px rgba(16,185,129,0.3);">
                                            <i class="fas fa-caret-down"></i> %
                                            <?= $indirim_orani ?> İndirim!
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div
                                style="display:flex; align-items:center; gap:15px; border-left:1px solid #f3f4f6; padding-left:25px;">
                                <?php if ($indirim_var): ?>
                                    <a href="<?= BASE_URL ?>/urun-detay.php?slug=<?= $a['slug'] ?>" class="buton"
                                        style="background:var(--primary); color:#fff; white-space:nowrap; padding:10px 20px; border-radius:10px; text-decoration:none; font-size:0.85rem; font-weight:700;">Hemen
                                        Al</a>
                                <?php endif; ?>
                                <form action="<?= BASE_URL ?>/client/price-alerts.php" method="POST"
                                    onsubmit="return confirm('Bu fiyat alarmını kaldırmak istediğinize emin misiniz?')">
                                    <input type="hidden" name="remove_id" value="<?= $a['id'] ?>">
                                    <button type="submit"
                                        style="background:none; border:none; color:var(--gray); cursor:pointer; font-size:1rem;"
                                        title="Alarmı Kaldır"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php gorunum('alt'); ?>
