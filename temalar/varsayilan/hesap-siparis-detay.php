<?php gorunum('ust', ['sayfa_basligi' => 'Sipariş Detayı - ' . $siparis['order_number']]); ?>

<div class="order-detail-page" style="padding:40px 0;">
    <div class="client-layout" style="display:grid; grid-template-columns: 280px 1fr; gap:40px;">

        <!-- Sidebar -->
        <div class="client-sidebar-container">
            <?php gorunum('hesap-sidebar', ['aktif_sayfa' => 'orders', 'kullanici' => $kullanici]); ?>
        </div>

        <!-- İçerik -->
        <div class="client-main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
                <div style="display:flex; align-items:center; gap:15px;">
                    <a href="<?= BASE_URL ?>/client/orders.php"
                        style="width:40px; height:40px; border-radius:50%; border:1px solid #e5e7eb; display:flex; align-items:center; justify-content:center; color:var(--dark); text-decoration:none;"><i
                            class="fas fa-arrow-left"></i></a>
                    <h1 style="font-weight:800; margin:0;">Sipariş #
                        <?= $siparis['order_number'] ?>
                    </h1>
                </div>
                <?php
                $renkler = ['pending' => '#f59e0b', 'processing' => '#3b82f6', 'shipped' => '#8b5cf6', 'delivered' => '#10b981', 'cancelled' => '#ef4444'];
                $metinler = ['pending' => 'Beklemede', 'processing' => 'İşleniyor', 'shipped' => 'Kargoda', 'delivered' => 'Teslim Edildi', 'cancelled' => 'İptal'];
                $renk = $renkler[$siparis['status']] ?? '#6b7280';
                $metin = $metinler[$siparis['status']] ?? $siparis['status'];
                ?>
                <span
                    style="background:<?= $renk ?>; color:#fff; padding:8px 20px; border-radius:30px; font-weight:700; font-size:0.9rem;">
                    <?= $metin ?>
                </span>
            </div>

            <!-- Sipariş Bilgileri -->
            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-bottom:30px;">

                <!-- Ürünler -->
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:30px;">
                    <h3 style="font-weight:800; margin-bottom:25px; display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-box" style="color:var(--primary);"></i> Sipariş İçeriği
                    </h3>
                    <div style="display:grid; gap:20px;">
                        <?php foreach ($urunler as $u): ?>
                            <div
                                style="display:flex; align-items:center; gap:20px; border-bottom:1px solid #f3f4f6; padding-bottom:20px;">
                                <img src="<?= resim_linki($u['product_image']) ?>"
                                    style="width:70px; height:70px; object-fit:cover; border-radius:10px; border:1px solid #f3f4f6;">
                                <div style="flex:1;">
                                    <div style="font-weight:700; color:var(--dark); margin-bottom:5px;">
                                        <?= temiz($u['product_name']) ?>
                                    </div>
                                    <div style="font-size:0.85rem; color:var(--gray); display:flex; gap:15px;">
                                        <span>Adet: <strong>
                                                <?= $u['quantity'] ?>
                                            </strong></span>
                                        <span>Birim: <strong>
                                                <?= para_yaz($u['price']) ?>
                                            </strong></span>
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-weight:800; color:var(--primary);">
                                        <?= para_yaz($u['total']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div
                        style="margin-top:30px; background:var(--gray-50); padding:20px; border-radius:12px; display:grid; gap:10px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:var(--gray);">
                            <span>Ara Toplam</span>
                            <span>
                                <?= para_yaz($siparis['subtotal']) ?>
                            </span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:var(--gray);">
                            <span>KDV (%20)</span>
                            <span>
                                <?= para_yaz($siparis['subtotal'] * 0.20) ?>
                            </span>
                        </div>
                        <?php if ($siparis['shipping_cost'] > 0): ?>
                            <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:var(--gray);">
                                <span>Kargo / Teslimat</span>
                                <span>
                                    <?= para_yaz($siparis['shipping_cost']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ($siparis['discount_amount'] > 0): ?>
                            <div
                                style="display:flex; justify-content:space-between; font-size:0.9rem; color:var(--success); font-weight:600;">
                                <span>İndirim</span>
                                <span>-
                                    <?= para_yaz($siparis['discount_amount']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div
                            style="display:flex; justify-content:space-between; font-size:1.2rem; font-weight:800; color:var(--dark); margin-top:5px; border-top:1px solid #e5e7eb; padding-top:10px;">
                            <span>Toplam Tutar</span>
                            <span style="color:var(--primary);">
                                <?= para_yaz($siparis['total']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detaylar -->
                <div style="display:grid; gap:20px; align-content:start;">

                    <!-- Adres -->
                    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:25px;">
                        <h4 style="font-weight:800; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> Teslimat Adresi
                        </h4>
                        <div style="font-size:0.9rem; color:var(--dark-600); line-height:1.6;">
                            <strong style="display:block; margin-bottom:5px;">
                                <?= temiz($siparis['shipping_first_name'] . ' ' . $siparis['shipping_last_name']) ?>
                            </strong>
                            <?= temiz($siparis['shipping_address']) ?><br>
                            <?= temiz(($siparis['shipping_neighborhood'] ? $siparis['shipping_neighborhood'] . ' Mah. ' : '') . $siparis['shipping_district'] . ' / ' . $siparis['shipping_city']) ?>
                            <div style="margin-top:10px; color:var(--gray);"><i class="fas fa-phone"></i>
                                <?= temiz($siparis['shipping_phone']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Ödeme -->
                    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:25px;">
                        <h4 style="font-weight:800; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-credit-card" style="color:var(--primary);"></i> Ödeme Bilgisi
                        </h4>
                        <div style="margin-bottom:15px;">
                            <small style="color:var(--gray); display:block; margin-bottom:2px;">Ödeme Yöntemi</small>
                            <span style="font-weight:700;">
                                <?= $siparis['payment_method'] === 'kapida_odeme' ? 'Kapıda Ödeme' : ($siparis['payment_method'] === 'havale' ? 'Havale / EFT' : 'Kredi Kartı') ?>
                            </span>
                        </div>
                        <div>
                            <small style="color:var(--gray); display:block; margin-bottom:5px;">Ödeme Durumu</small>
                            <?php
                            $ps_label = ['pending' => 'Bekliyor', 'paid' => 'Ödendi', 'failed' => 'Başarısız', 'refunded' => 'İade Edildi'];
                            $ps_color = ['pending' => '#f59e0b', 'paid' => '#10b981', 'failed' => '#ef4444', 'refunded' => '#8b5cf6'];
                            $ps = $siparis['payment_status'] ?? 'pending';
                            ?>
                            <span
                                style="background:<?= $ps_color[$ps] ?>15; color:<?= $ps_color[$ps] ?>; padding:5px 12px; border-radius:30px; font-size:0.75rem; font-weight:700;">
                                <?= $ps_label[$ps] ?>
                            </span>
                        </div>

                        <?php if ($siparis['payment_method'] === 'havale' && $banka): ?>
                            <div
                                style="margin-top:20px; background:#eff6ff; border:1px solid #dbeafe; padding:15px; border-radius:10px;">
                                <div style="font-size:0.8rem; font-weight:700; color:#1e40af; margin-bottom:5px;">IBAN
                                    Bilgisi</div>
                                <div
                                    style="display:flex; align-items:center; justify-content:space-between; background:#fff; padding:8px 12px; border-radius:6px; border:1px solid #dbeafe;">
                                    <code id="iban"
                                        style="font-family:monospace; font-size:0.85rem; color:#1e40af;"><?= $banka['bank_iban'] ?></code>
                                    <button
                                        onclick="navigator.clipboard.writeText(document.getElementById('iban').innerText); alert('IBAN Kopyalandı');"
                                        style="background:none; border:none; color:#1e40af; cursor:pointer;"><i
                                            class="fas fa-copy"></i></button>
                                </div>
                                <div style="font-size:0.75rem; color:#1e40af; margin-top:8px;">
                                    <strong>Alıcı:</strong>
                                    <?= $banka['bank_account_holder'] ?><br>
                                    <strong>Açıklama:</strong>
                                    <?= $siparis['order_number'] ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

<?php gorunum('alt'); ?>
