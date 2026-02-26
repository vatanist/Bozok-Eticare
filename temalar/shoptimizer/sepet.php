<?php gorunum('ust', ['sayfa_basligi' => 'Sepetim']); ?>

<div class="shoptimizer-cart-page" style="margin-top: 20px;">
    <h1 style="font-size: 2.5rem; font-weight: 900; color: var(--dark); margin-bottom: 40px; letter-spacing: -2px;">
        Sepetiniz</h1>

    <?php 
    $sepet_urunleri = getCartItems();
    if (empty($sepet_urunleri)): 
    ?>
        <div style="text-align: center; padding: 100px 0; background: #f8fafc; border-radius: 30px;">
            <i class="fas fa-shopping-basket" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h2 style="font-weight: 800; color: #64748b;">Sepetiniz şu an boş.</h2>
            <p style="color: var(--gray); margin-bottom: 30px;">Harika ürünlerimizi keşfetmeye ne dersiniz?</p>
            <a href="<?= BASE_URL ?>/urunler.php" class="buton" style="background: var(--dark); color: #fff; padding: 15px 40px; border-radius: 30px; font-weight: 800; text-decoration: none; display: inline-block;">Alışverişe Başla</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 50px; align-items: start;">
            
            <!-- Sepet Listesi -->
            <div class="cart-items">
                <div style="background: #fff; border-radius: 20px; border: 1px solid #f1f5f9; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; text-align: left; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: var(--gray);">
                                <th style="padding: 20px 25px;">Ürün</th>
                                <th style="padding: 20px;">Fiyat</th>
                                <th style="padding: 20px;">Adet</th>
                                <th style="padding: 20px 25px; text-align: right;">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sepet_urunleri as $item): 
                                $fiyat = $item['discount_price'] ?: $item['price'];
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 25px;">
                                        <div style="display: flex; align-items: center; gap: 20px;">
                                            <img src="<?= resim_linki($item['image']) ?>" style="width: 80px; height: 80px; border-radius: 12px; object-fit: cover; border: 1px solid #f1f5f9;">
                                            <div>
                                                <h4 style="margin: 0; font-weight: 800; font-size: 1rem;"><?= temiz($item['name']) ?></h4>
                                                <a href="<?= BASE_URL ?>/sepet.php?islem=sil&id=<?= $item['id'] ?>" style="font-size: 12px; color: #ef4444; text-decoration: none; font-weight: 700; margin-top: 5px; display: inline-block;"><i class="fas fa-trash"></i> Kaldır</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 25px; font-weight: 700; color: var(--dark);"><?= para_yaz($fiyat) ?></td>
                                    <td style="padding: 25px;">
                                        <div style="display: flex; align-items: center; background: #f8fafc; border-radius: 20px; padding: 5px 10px; width: fit-content;">
                                            <a href="<?= BASE_URL ?>/sepet.php?islem=adet&id=<?= $item['id'] ?>&adet=<?= $item['quantity']-1 ?>" style="color: var(--gray); text-decoration: none; font-weight: 800; width: 25px; text-align: center;">-</a>
                                            <span style="width: 30px; text-align: center; font-weight: 800; font-size: 14px;"><?= $item['quantity'] ?></span>
                                            <a href="<?= BASE_URL ?>/sepet.php?islem=adet&id=<?= $item['id'] ?>&adet=<?= $item['quantity']+1 ?>" style="color: var(--gray); text-decoration: none; font-weight: 800; width: 25px; text-align: center;">+</a>
                                        </div>
                                    </td>
                                    <td style="padding: 25px; text-align: right; font-weight: 900; color: var(--primary); font-size: 1.1rem;"><?= para_yaz($fiyat * $item['quantity']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- Sepet Özeti -->
            <div class="cart-summary">
                <div
                    style="background: #fff; border-radius: 24px; border: 1px solid #f1f5f9; padding: 30px; position: sticky; top: 30px;">
                    <h3
                        style="font-weight: 900; color: var(--dark); font-size: 1.4rem; margin-bottom: 25px; letter-spacing: -1px;">
                        Sipariş Özeti</h3>

                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 15px; color: var(--gray); font-weight: 600;">
                        <span>Ara Toplam</span>
                        <span>
                            <?= para_yaz(getCartTotal()) ?>
                        </span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 15px; color: var(--gray); font-weight: 600;">
                        <span>Kargo</span>
                        <span style="color: var(--primary);">Ücretsiz</span>
                    </div>

                    <div
                        style="border-top: 2px solid #f8fafc; padding-top: 25px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-weight: 900; font-size: 1.2rem; color: var(--dark);">Toplam</span>
                        <span style="font-weight: 900; font-size: 2rem; color: var(--primary);">
                            <?= para_yaz(getCartTotal()) ?>
                        </span>
                    </div>

                    <a href="<?= BASE_URL ?>/odeme.php" class="buton"
                        style="background: var(--dark); color: #fff; width: 100%; display: block; border-radius: 40px; padding: 20px; font-weight: 900; font-size: 1.2rem; text-decoration: none; text-align: center; transition: 0.3s;"
                        onmouseover="this.style.background='var(--primary)'"
                        onmouseout="this.style.background='var(--dark)'">
                        ÖDEMEYE GEÇ <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                    </a>

                    <div style="margin-top: 25px; text-align: center;">
                        <img src="https://www.paytr.com/img/logos/troy-odeme-sistemi.png"
                            style="height: 25px; opacity: 0.5;">
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php gorunum('alt'); ?>