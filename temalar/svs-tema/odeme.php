<?php gorunum('ust', ['sayfa_basligi' => 'Güvenli Ödeme']); ?>

<div class="shoptimizer-checkout-page" style="margin-top: 20px;">
    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 40px;">
        <div
            style="background: var(--primary); color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-lock"></i></div>
        <h1 style="font-size: 2.5rem; font-weight: 900; color: var(--dark); margin: 0; letter-spacing: -2px;">Güvenli
            Ödeme</h1>
    </div>

    <form action="<?= BASE_URL ?>/siparis-tamamla" method="POST" id="checkoutForm">
        <?= csrf_kod() ?>
        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 60px; align-items: start;">

            <!-- Sol: Adres ve Ödeme -->
            <div class="checkout-left">

                <!-- Adres Seçimi -->
                <section style="margin-bottom: 50px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h3
                            style="font-weight: 900; color: var(--dark); font-size: 1.5rem; margin: 0; letter-spacing: -1px;">
                            1. Teslimat Adresi</h3>
                        <a href="<?= BASE_URL ?>/hesap/adres-ekle.php"
                            style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 14px;">+
                            Yeni Adres Ekle</a>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <?php foreach ($adresler as $a): ?>
                            <label style="cursor: pointer; position: relative;">
                                <input type="radio" name="adres_id" value="<?= $a['id'] ?>" style="display: none;" required
                                    <?= $a['varsayilan'] ? 'checked' : '' ?>>
                                <div class="address-box"
                                    style="background: #fff; border: 2px solid #f1f5f9; border-radius: 20px; padding: 25px; transition: 0.3s; height: 100%;">
                                    <strong
                                        style="display: block; font-size: 1.1rem; color: var(--dark); margin-bottom: 10px;">
                                        <?= temiz($a['baslik']) ?>
                                    </strong>
                                    <p style="margin: 0; color: var(--gray); font-size: 14px; line-height: 1.5;">
                                        <?= temiz($a['adres']) ?>
                                    </p>
                                    <p style="margin: 10px 0 0 0; color: var(--dark); font-weight: 600; font-size: 14px;">
                                        <?= temiz($a['ilce']) ?> /
                                        <?= temiz($a['il']) ?>
                                    </p>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Ödeme Yöntemi -->
                <section>
                    <h3
                        style="font-weight: 900; color: var(--dark); font-size: 1.5rem; margin-bottom: 25px; letter-spacing: -1px;">
                        2. Ödeme Yöntemi</h3>
                    <div style="display: grid; gap: 15px;">
                        <?php hook_calistir('odeme_yontemleri_listesi'); ?>
                    </div>
                </section>

            </div>

            <!-- Sağ: Sipariş Özeti -->
            <div class="checkout-right">
                <div
                    style="background: #fff; border-radius: 24px; border: 1px solid #f1f5f9; padding: 30px; position: sticky; top: 30px;">
                    <h3
                        style="font-weight: 900; color: var(--dark); font-size: 1.4rem; margin-bottom: 25px; letter-spacing: -1px;">
                        Siparişiniz</h3>

                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: 25px; padding-right: 10px;">
                        <?php foreach (getCartItems() as $item):
                            $fiyat = $item['discount_price'] ?: $item['price'];
                        ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <div style="display: flex; gap: 15px; align-items: center;">
                                    <div style="position: relative;">
                                        <img src="<?= resim_linki($item['image']) ?>" style="width: 50px; height: 50px; border-radius: 10px; object-fit: cover;">
                                        <span style="position: absolute; top: -10px; right: -10px; background: var(--gray); color: #fff; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;"><?= $item['quantity'] ?></span>
                                    </div>
                                    <span style="font-size: 14px; font-weight: 700; color: var(--dark); max-width: 180px;"><?= temiz($item['name']) ?></span>
                                </div>
                                <span style="font-weight: 800; font-size: 14px; color: var(--dark);"><?= para_yaz($fiyat * $item['quantity']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>


                    <div
                        style="border-top: 1px solid #f1f5f9; padding-top: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; font-size: 15px; color: var(--gray); font-weight: 600;">
                        <span>Kargo</span>
                        <span style="color: var(--primary);">Ücretsiz</span>
                    </div>

                    <div
                        style="border-top: 2px solid #f8fafc; padding-top: 25px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-weight: 900; font-size: 1.2rem; color: var(--dark);">Toplam</span>
                        <span style="font-weight: 900; font-size: 2.2rem; color: var(--primary);">
                            <?= para_yaz(getCartTotal()) ?>
                        </span>
                    </div>

                    <button type="submit" class="buton"
                        style="background: var(--primary); color: #fff; width: 100%; display: block; border-radius: 40px; padding: 22px; font-weight: 900; font-size: 1.3rem; border: none; cursor: pointer; text-align: center; transition: 0.3s;"
                        onmouseover="this.style.background='var(--primary-dark)'"
                        onmouseout="this.style.background='var(--primary)'">
                        ÖDEMEYİ TAMAMLA <i class="fas fa-shield-alt" style="margin-left: 10px;"></i>
                    </button>

                    <div
                        style="margin-top: 25px; text-align: center; font-size: 12px; color: var(--gray); line-height: 1.5;">
                        <p><i class="fas fa-shield-alt"></i> 256-bit SSL şifreleme ile verileriniz %100 güvende.</p>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    // Adres seçimi görsel efekti
    document.querySelectorAll('input[name="adres_id"]').forEach(input => {
        input.addEventListener('change', function () {
            document.querySelectorAll('.address-box').forEach(box => {
                box.style.borderColor = '#f1f5f9';
                box.style.background = '#fff';
            });
            if (this.checked) {
                const box = this.nextElementSibling;
                box.style.borderColor = 'var(--primary)';
                box.style.background = '#f0fdf4';
            }
        });
    });
</script>

<?php gorunum('alt'); ?>