<?php gorunum('ust', ['sayfa_basligi' => 'Ödeme']); ?>

<div class="checkout-page">
    <h1 style="font-weight:800; margin-bottom:30px;"><i class="fas fa-lock" style="color:var(--primary);"></i> Ödeme ve
        Teslimat</h1>

    <?php mesaj_goster('odeme'); ?>

    <form action="<?= BASE_URL ?>/siparis-tamamla" method="POST">
        <?= csrf_kod() ?>
        <div class="checkout-layout" style="display:grid; grid-template-columns: 1fr 400px; gap:40px;">

            <!-- Sol Taraf: Formlar -->
            <div class="checkout-forms">

                <!-- Teslimat Bilgileri -->
                <div
                    style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:30px; margin-bottom:30px;">
                    <h3
                        style="font-weight:800; margin-bottom:20px; color:var(--dark); display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> Teslimat Bilgileri
                    </h3>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Adınız
                                *</label>
                            <input type="text" name="ad" value="<?= temiz($kullanici['first_name']) ?>"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                        <div>
                            <label
                                style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Soyadınız
                                *</label>
                            <input type="text" name="soyad" value="<?= temiz($kullanici['last_name']) ?>"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Telefon
                            Numaranız *</label>
                        <input type="tel" name="telefon" value="<?= temiz($kullanici['phone']) ?>"
                            style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                            required>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Açık Adres
                            *</label>
                        <textarea name="adres"
                            style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none; min-height:100px;"
                            required><?= !empty($adresler) ? temiz($adresler[0]['address_line']) : '' ?></textarea>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Şehir
                                *</label>
                            <input type="text" name="sehir"
                                value="<?= !empty($adresler) ? temiz($adresler[0]['city']) : '' ?>"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">İlçe
                                *</label>
                            <input type="text" name="ilce"
                                value="<?= !empty($adresler) ? temiz($adresler[0]['district']) : '' ?>"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                    </div>
                </div>

                <!-- Ödeme Yöntemi -->
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:30px;">
                    <h3
                        style="font-weight:800; margin-bottom:20px; color:var(--dark); display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-credit-card" style="color:var(--primary);"></i> Ödeme Yöntemi
                    </h3>

                    <div style="display:grid; gap:15px;">
                        <?php 
                        // Ödeme yöntemlerini kancadan al (CMS Mantığı)
                        $yontemler = hook_calistir('odeme_yontemleri', [
                            [
                                'kod' => 'kapida_odeme',
                                'baslik' => 'Kapıda Ödeme',
                                'ikon' => 'fas fa-truck',
                                'aciklama' => 'Teslimat sırasında nakit veya kartla ödeme'
                            ]
                        ]);

                        foreach ($yontemler as $index => $yontem): 
                            $is_paytr = ($yontem['kod'] === 'paytr');
                        ?>
                        <label
                            style="display:flex; align-items:center; gap:15px; border:1px solid <?= $index === 0 ? 'var(--primary)' : '#e5e7eb' ?>; background:<?= $index === 0 ? 'var(--primary-light)' : '#fff' ?>; padding:20px; border-radius:12px; cursor:pointer;"
                            onclick="document.querySelectorAll('.payment-label').forEach(el => { el.style.border='1px solid #e5e7eb'; el.style.background='#fff'; }); this.style.border='1px solid var(--primary)'; this.style.background='var(--primary-light)';"
                            class="payment-label">
                            <input type="radio" name="odeme_yontemi" value="<?= $yontem['kod'] ?>" <?= $index === 0 ? 'checked' : '' ?>
                                style="width:20px; height:20px;">
                            <div>
                                <strong style="display:block; color:<?= $index === 0 ? 'var(--primary)' : 'inherit' ?>;"><?= $yontem['baslik'] ?></strong>
                                <small style="color:<?= $index === 0 ? 'var(--primary)' : 'var(--gray)' ?>;"><?= $yontem['aciklama'] ?? '' ?></small>
                            </div>
                            <?php if ($is_paytr): ?>
                                <img src="https://www.paytr.com/img/paytr-logo-svg.svg" style="height:25px; margin-left:auto;">
                            <?php else: ?>
                                <i class="<?= $yontem['ikon'] ?>" style="font-size:1.5rem; margin-left:auto; color:<?= $index === 0 ? 'var(--primary)' : '#cbd5e0' ?>;"></i>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-top:30px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Sipariş
                            Notu</label>
                        <textarea name="notlar" placeholder="Varsa özel notunuzu buraya yazabilirsiniz..."
                            style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none; min-height:80px;"></textarea>
                    </div>
                </div>

            </div>

            <!-- Sağ Taraf: Özet -->
            <div class="checkout-summary">
                <div
                    style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:25px; position:sticky; top:100px;">
                    <h3
                        style="font-weight:800; margin-bottom:20px; font-size:1.2rem; border-bottom:1px solid #e5e7eb; padding-bottom:15px;">
                        Sipariş Özeti</h3>

                    <div class="summary-items" style="max-height:300px; overflow-y:auto; margin-bottom:20px;">
                        <?php foreach ($sepet as $item): ?>
                            <div style="display:flex; gap:10px; margin-bottom:15px; align-items:center;">
                                <img src="<?= resim_linki($item['image']) ?>"
                                    style="width:50px; height:50px; object-fit:cover; border-radius:6px;">
                                <div style="flex:1;">
                                    <div style="font-size:0.85rem; font-weight:700; color:var(--dark); line-height:1.2;">
                                        <?= temiz($item['name']) ?>
                                    </div>
                                    <small style="color:var(--gray);">
                                        <?= $item['quantity'] ?> Adet x
                                        <?= para_yaz($item['discount_price'] ?: $item['price']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div
                        style="display:flex; justify-content:space-between; margin-bottom:12px; color:var(--gray); font-size:0.9rem;">
                        <span>Ara Toplam</span>
                        <span>
                            <?= para_yaz($ara_toplam) ?>
                        </span>
                    </div>
                    <div
                        style="display:flex; justify-content:space-between; margin-bottom:12px; color:var(--gray); font-size:0.9rem;">
                        <span>KDV (%20)</span>
                        <span>
                            <?= para_yaz($kdv) ?>
                        </span>
                    </div>
                    <div
                        style="display:flex; justify-content:space-between; margin-bottom:12px; color:var(--gray); font-size:0.9rem;">
                        <span>Kargo Ücreti</span>
                        <span>
                            <?= $kargo > 0 ? para_yaz($kargo) : '<span style="color:var(--success); font-weight:700;">Ücretsiz</span>' ?>
                        </span>
                    </div>

                    <?php if ($indirim > 0): ?>
                        <div
                            style="display:flex; justify-content:space-between; margin-bottom:12px; color:var(--success); font-weight:600; font-size:0.9rem;">
                            <span>İndirim</span>
                            <span>-
                                <?= para_yaz($indirim) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div
                        style="margin:20px 0; border-top:2px solid var(--gray-50); padding-top:20px; display:flex; justify-content:space-between; align-items:center;">
                        <strong style="font-size:1.1rem;">Toplam Ödenecek</strong>
                        <strong style="font-size:1.6rem; color:var(--primary); font-weight:900;">
                            <?= para_yaz($toplam) ?>
                        </strong>
                    </div>

                    <p style="font-size:0.75rem; color:var(--gray); text-align:center; margin-bottom:15px;">
                        "Siparişi Onayla" butonuna basarak Mesafeli Satış Sözleşmesi'ni ve İptal/İade Koşullarını kabul
                        etmiş olursunuz.
                    </p>

                    <button type="submit" class="buton"
                        style="display:block; width:100%; border:none; background:var(--primary); color:#fff; text-align:center; padding:15px; border-radius:30px; font-weight:800; font-size:1.1rem; cursor:pointer; box-shadow:0 4px 15px rgba(26,86,219,0.3);">Siparişi
                        Onayla <i class="fas fa-check"></i></button>

                </div>
            </div>

        </div>
    </form>
</div>

<?php gorunum('alt'); ?>
