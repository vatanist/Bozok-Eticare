<?php gorunum('ust', ['sayfa_basligi' => 'Siparişlerim']); ?>

<div class="client-orders" style="padding:40px 0;">
    <div class="client-layout" style="display:grid; grid-template-columns: 280px 1fr; gap:40px;">

        <!-- Sidebar -->
        <div class="client-sidebar-container">
            <?php gorunum('hesap-sidebar', ['aktif_sayfa' => 'orders', 'kullanici' => $kullanici]); ?>
        </div>

        <!-- İçerik -->
        <div class="client-main-content">
            <div style="margin-bottom:30px;">
                <h1 style="font-weight:800; margin-bottom:10px;">Siparişlerim</h1>
                <p style="color:var(--gray);">Geçmişten günümüze tüm siparişlerini burada görebilirsin.</p>
            </div>

            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden;">
                <?php if (empty($siparisler)): ?>
                    <div style="text-align:center; padding:80px 20px;">
                        <i class="fas fa-shopping-bag fa-4x"
                            style="color:var(--gray-light); margin-bottom:20px; display:block;"></i>
                        <h2 style="color:var(--dark-600);">Henüz bir siparişin yok.</h2>
                        <p style="color:var(--gray); margin-bottom:20px;">V-Commerce dünyasındaki milyonlarca üründen birini
                            hemen keşfet!</p>
                        <a href="<?= BASE_URL ?>/urunler.php" class="buton"
                            style="display:inline-block; background:var(--primary); color:#fff; padding:12px 30px; border-radius:30px; font-weight:700;">Alışverişe
                            Başla</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; min-width:800px;">
                            <thead>
                                <tr
                                    style="text-align:left; background:var(--gray-50); color:var(--gray); font-size:0.8rem; text-transform:uppercase;">
                                    <th style="padding:20px;">Sipariş Bilgisi</th>
                                    <th style="padding:20px;">Ürünler</th>
                                    <th style="padding:20px;">Tutar</th>
                                    <th style="padding:20px;">Ödeme</th>
                                    <th style="padding:20px;">Durum</th>
                                    <th style="padding:20px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($siparisler as $s):
                                    $ogeler = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$s['id']]);
                                    ?>
                                    <tr style="border-top:1px solid #f3f4f6; transition:background 0.3s;"
                                        onmouseover="this.style.background='#fafafa'"
                                        onmouseout="this.style.background='white'">
                                        <td style="padding:20px;">
                                            <div style="font-weight:800; color:var(--dark); margin-bottom:4px;">
                                                <?= $s['order_number'] ?>
                                            </div>
                                            <div style="font-size:0.8rem; color:var(--gray);">
                                                <?= date('d.m.Y H:i', strtotime($s['created_at'])) ?>
                                            </div>
                                        </td>
                                        <td style="padding:20px;">
                                            <div style="max-width:200px;">
                                                <?php foreach ($ogeler as $o): ?>
                                                    <div
                                                        style="font-size:0.8rem; color:var(--dark-600); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                        •
                                                        <?= temiz($o['product_name']) ?> <span style="color:var(--gray-light);">(x
                                                            <?= $o['quantity'] ?>)
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td style="padding:20px; font-weight:800; color:var(--primary);">
                                            <?= para_yaz($s['total']) ?>
                                        </td>
                                        <td style="padding:20px;">
                                            <div style="font-size:0.85rem; color:var(--dark-600); font-weight:600;">
                                                <?= $s['payment_method'] === 'kapida_odeme' ? 'Kapıda Ödeme' : ($s['payment_method'] === 'havale' ? 'Havale/EFT' : 'PayTR') ?>
                                            </div>
                                            <small style="color:var(--gray); font-size:0.75rem;">
                                                <?= $s['payment_status'] === 'paid' ? '<i class="fas fa-check-circle" style="color:var(--success)"></i> Ödendi' : '<i class="fas fa-clock"></i> Bekliyor' ?>
                                            </small>
                                        </td>
                                        <td style="padding:20px;">
                                            <?php
                                            $renkler = ['pending' => '#6b7280', 'processing' => '#3b82f6', 'shipped' => '#8b5cf6', 'delivered' => '#10b981', 'cancelled' => '#ef4444'];
                                            $metinler = ['pending' => 'Beklemede', 'processing' => 'İşleniyor', 'shipped' => 'Kargoda', 'delivered' => 'Teslim Edildi', 'cancelled' => 'İptal'];
                                            $renk = $renkler[$s['status']] ?? '#6b7280';
                                            $metin = $metinler[$s['status']] ?? $s['status'];
                                            ?>
                                            <span
                                                style="display:inline-block; padding:5px 12px; border-radius:30px; font-size:0.75rem; font-weight:700; background:<?= $renk ?>15; color:<?= $renk ?>;">
                                                <?= $metin ?>
                                            </span>
                                        </td>
                                        <td style="padding:20px; text-align:right;">
                                            <a href="<?= BASE_URL ?>/client/order-detail.php?id=<?= $s['id'] ?>" class="buton"
                                                style="background:var(--dark); color:#fff; padding:8px 15px; border-radius:6px; font-size:0.8rem; text-decoration:none;">Detay
                                                <i class="fas fa-chevron-right"
                                                    style="font-size:0.7rem; margin-left:5px;"></i></a>
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
