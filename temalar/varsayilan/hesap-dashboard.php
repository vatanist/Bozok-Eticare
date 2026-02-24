<?php gorunum('ust', ['sayfa_basligi' => 'Hesabım']); ?>

<div class="client-dashboard" style="padding:40px 0;">
    <div class="client-layout" style="display:grid; grid-template-columns: 280px 1fr; gap:40px;">

        <!-- Sidebar -->
        <div class="client-sidebar-container">
            <?php gorunum('hesap-sidebar', ['aktif_sayfa' => 'dashboard', 'kullanici' => $kullanici]); ?>
        </div>

        <!-- İçerik -->
        <div class="client-main-content">
            <div style="margin-bottom:30px;">
                <h1 style="font-weight:800; margin-bottom:10px;">Hoş Geldin,
                    <?= temiz($kullanici['first_name']) ?>! 👋
                </h1>
                <p style="color:var(--gray);">Hesap özetine ve siparişlerine buradan göz atabilirsin.</p>
            </div>

            <?php mesaj_goster('siparis_onay'); ?>

            <!-- İstatistikler -->
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:20px; margin-bottom:40px;">
                <div
                    style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:25px; display:flex; align-items:center; gap:20px;">
                    <div
                        style="width:60px; height:60px; background:var(--primary-light); color:var(--primary); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div>
                        <div style="font-size:1.5rem; font-weight:800; color:var(--dark);">
                            <?= $siparis_sayisi ?>
                        </div>
                        <div style="font-size:0.85rem; color:var(--gray); font-weight:600;">Toplam Sipariş</div>
                    </div>
                </div>
                <div
                    style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:25px; display:flex; align-items:center; gap:20px;">
                    <div
                        style="width:60px; height:60px; background:#fef3c7; color:#d97706; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div>
                        <div style="font-size:1.5rem; font-weight:800; color:var(--dark);">
                            <?= $favori_sayisi ?>
                        </div>
                        <div style="font-size:0.85rem; color:var(--gray); font-weight:600;">Favoriler</div>
                    </div>
                </div>
                <div
                    style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:25px; display:flex; align-items:center; gap:20px;">
                    <div
                        style="width:60px; height:60px; background:#d1fae5; color:#059669; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <div style="font-size:1.5rem; font-weight:800; color:var(--dark);">
                            <?= $adres_sayisi ?>
                        </div>
                        <div style="font-size:0.85rem; color:var(--gray); font-weight:600;">Kayıtlı Adres</div>
                    </div>
                </div>
            </div>

            <!-- Son Siparişler -->
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:30px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                    <h3 style="font-weight:800; margin:0;">Son Siparişlerim</h3>
                    <a href="<?= BASE_URL ?>/client/orders.php"
                        style="color:var(--primary); font-weight:700; font-size:0.9rem;">Tümünü Gör <i
                            class="fas fa-arrow-right"></i></a>
                </div>

                <?php if (empty($son_siparisler)): ?>
                    <div style="text-align:center; padding:50px 0;">
                        <i class="fas fa-box-open fa-3x"
                            style="color:var(--gray-light); margin-bottom:15px; display:block;"></i>
                        <p style="color:var(--gray);">Henüz bir siparişiniz bulunmuyor.</p>
                        <a href="<?= BASE_URL ?>/urunler.php" class="buton"
                            style="display:inline-block; margin-top:10px; background:var(--primary); color:#fff; padding:10px 25px; border-radius:30px; font-size:0.9rem;">Alışverişe
                            Başla</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr
                                    style="text-align:left; border-bottom:2px solid #f3f4f6; color:var(--gray); font-size:0.85rem; text-transform:uppercase;">
                                    <th style="padding:15px;">Sipariş No</th>
                                    <th style="padding:15px;">Tarih</th>
                                    <th style="padding:15px;">Tutar</th>
                                    <th style="padding:15px;">Durum</th>
                                    <th style="padding:15px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($son_siparisler as $s): ?>
                                    <tr style="border-bottom:1px solid #f3f4f6;">
                                        <td style="padding:15px; font-weight:700; color:var(--dark);">
                                            <?= $s['order_number'] ?>
                                        </td>
                                        <td style="padding:15px; color:var(--gray); font-size:0.9rem;">
                                            <?= date('d.m.Y', strtotime($s['created_at'])) ?>
                                        </td>
                                        <td style="padding:15px; font-weight:800; color:var(--primary);">
                                            <?= para_yaz($s['total']) ?>
                                        </td>
                                        <td style="padding:15px;">
                                            <span
                                                style="padding:5px 12px; border-radius:30px; font-size:0.75rem; font-weight:700; background:var(--gray-50); color:var(--gray);">
                                                <?php
                                                $durumlar = ['pending' => 'Beklemede', 'processing' => 'İşleniyor', 'shipped' => 'Kargoda', 'delivered' => 'Teslim Edildi', 'cancelled' => 'İptal'];
                                                echo $durumlar[$s['status']] ?? $s['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td style="padding:15px; text-align:right;">
                                            <a href="<?= BASE_URL ?>/client/order-detail.php?id=<?= $s['id'] ?>"
                                                style="background:var(--gray-50); color:var(--dark); width:35px; height:35px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; transition:0.3s;"
                                                onmouseover="this.style.background='var(--primary-light)'; this.style.color='var(--primary)'"
                                                onmouseout="this.style.background='var(--gray-50)'; this.style.color='var(--dark)'">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php gorunum('alt'); ?>
