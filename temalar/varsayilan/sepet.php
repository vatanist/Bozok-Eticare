<?php gorunum('ust', ['sayfa_basligi' => 'Sepetim']); ?>

<div class="cart-page">
    <h1 style="font-weight:800; margin-bottom:30px; display:flex; align-items:center; gap:15px;">
        <i class="fas fa-shopping-basket" style="color:var(--primary);"></i> Sepetim
    </h1>

    <?php if (empty($urunler)): ?>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:80px; text-align:center;">
            <i class="fas fa-cart-plus fa-4x" style="color:var(--gray-light); margin-bottom:20px;"></i>
            <h2 style="color:var(--dark-600);">Sepetiniz şu an boş.</h2>
            <p style="color:var(--gray);">Hemen ürünlerimizi incelemeye başlayabilir ve sepetinizi doldurabilirsiniz.</p>
            <a href="<?= BASE_URL ?>/urunler.php" class="buton"
                style="display:inline-block; margin-top:20px; background:var(--primary); color:#fff; padding:12px 30px; border-radius:30px;">Ürünleri
                İncele</a>
        </div>
    <?php else: ?>
        <div class="cart-layout" style="display:grid; grid-template-columns: 1fr 350px; gap:30px;">

            <!-- Ürün Listesi -->
            <div class="cart-items">
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr
                                style="background:var(--gray-50); text-align:left; font-size:0.85rem; text-transform:uppercase; color:var(--gray);">
                                <th style="padding:15px 20px;">Ürün Bilgisi</th>
                                <th style="padding:15px 20px;">Fiyat</th>
                                <th style="padding:15px 20px;">Adet</th>
                                <th style="padding:15px 20px;">Toplam</th>
                                <th style="padding:15px 20px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($urunler as $u):
                                $f = $u['unit_price_override'] > 0 ? $u['unit_price_override'] : ($u['discount_price'] ?: $u['price']);
                                ?>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td style="padding:20px;">
                                        <div style="display:flex; gap:15px; align-items:center;">
                                            <img src="<?= resim_linki($u['image']) ?>"
                                                style="width:80px; height:80px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;">
                                            <div>
                                                <h4 style="margin:0; font-size:1rem;"><a
                                                        href="<?= BASE_URL ?>/urun-detay.php?slug=<?= $u['slug'] ?>"
                                                        style="color:var(--dark);">
                                                        <?= temiz($u['name']) ?>
                                                    </a></h4>
                                                <small style="color:var(--gray-light);">Ürün Kodu: V-
                                                    <?= $u['product_id'] ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding:20px; font-weight:700; color:var(--dark-600);">
                                        <?= para_yaz($f) ?>
                                    </td>
                                    <td style="padding:20px;">
                                        <form action="<?= BASE_URL ?>/sepet.php" method="POST"
                                            style="display:flex; align-items:center; border:1px solid #e5e7eb; border-radius:30px; padding:3px 10px; width:fit-content;">
                                            <input type="hidden" name="islem" value="guncelle">
                                            <input type="hidden" name="sepet_id" value="<?= $u['id'] ?>">
                                            <input type="number" name="adet" value="<?= $u['quantity'] ?>" min="1"
                                                max="<?= $u['stock'] ?>"
                                                style="width:40px; text-align:center; border:none; padding:5px; outline:none;"
                                                onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td style="padding:20px; font-weight:800; color:var(--primary);">
                                        <?= para_yaz($f * $u['quantity']) ?>
                                    </td>
                                    <td style="padding:20px;">
                                        <a href="<?= BASE_URL ?>/sepet.php?islem=sil&id=<?= $u['id'] ?>"
                                            style="color:var(--danger); font-size:1.1rem;"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:20px; display:flex; justify-content:space-between;">
                    <a href="<?= BASE_URL ?>/urunler.php" style="color:var(--gray); font-weight:600;"><i
                            class="fas fa-arrow-left"></i> Alışverişe Devam Et</a>
                </div>
            </div>

            <!-- Sipariş Özeti -->
            <div class="cart-summary">
                <div
                    style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:25px; position:sticky; top:100px;">
                    <h3
                        style="font-weight:800; margin-bottom:20px; font-size:1.2rem; border-bottom:1px solid #e5e7eb; padding-bottom:15px;">
                        Sipariş Özeti</h3>

                    <div style="display:flex; justify-content:space-between; margin-bottom:12px; color:var(--gray);">
                        <span>Ara Toplam</span>
                        <span>
                            <?= para_yaz($ara_toplam) ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px; color:var(--gray);">
                        <span>KDV (%20)</span>
                        <span>
                            <?= para_yaz($kdv) ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px; color:var(--gray);">
                        <span>Kargo Ücreti</span>
                        <span>
                            <?= $kargo > 0 ? para_yaz($kargo) : '<span style="color:var(--success); font-weight:700;">Ücretsiz</span>' ?>
                        </span>
                    </div>

                    <?php if ($indirim > 0): ?>
                        <div
                            style="display:flex; justify-content:space-between; margin-bottom:12px; color:var(--success); font-weight:600;">
                            <span>İndirim (
                                <?= $kupon['kod'] ?>)
                            </span>
                            <span>-
                                <?= para_yaz($indirim) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div
                        style="margin:20px 0; border-top:2px solid var(--gray-50); padding-top:20px; display:flex; justify-content:space-between; align-items:center;">
                        <strong style="font-size:1.1rem;">Genel Toplam</strong>
                        <strong style="font-size:1.5rem; color:var(--primary); font-weight:900;">
                            <?= para_yaz($toplam) ?>
                        </strong>
                    </div>

                    <!-- Kupon Girişi -->
                    <div style="margin-top:20px;">
                        <form action="<?= BASE_URL ?>/sepet.php" method="POST" style="display:flex; gap:10px;">
                            <input type="hidden" name="islem" value="kupon">
                            <input type="text" name="kupon_kodu" placeholder="İndirim Kodu"
                                style="flex:1; padding:10px 15px; border:1px solid #e5e7eb; border-radius:8px; font-size:0.9rem; outline:none;"
                                required>
                            <button type="submit"
                                style="background:var(--dark); color:#fff; border:none; padding:10px 15px; border-radius:8px; cursor:pointer; font-weight:700;">Uygula</button>
                        </form>
                    </div>

                    <a href="<?= BASE_URL ?>/odeme.php" class="buton"
                        style="display:block; width:100%; background:var(--primary); color:#fff; text-align:center; padding:15px; border-radius:30px; font-weight:800; font-size:1.1rem; margin-top:25px; box-shadow:0 4px 15px rgba(26,86,219,0.3);">Ödemeye
                        Geç <i class="fas fa-chevron-right"></i></a>

                    <div style="margin-top:20px; text-align:center;">
                        <img src="https://www.tbb.org.tr/dosya/banka_kart_logolari.png"
                            style="height:25px; filter:grayscale(1); opacity:0.6;">
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php gorunum('alt'); ?>
