<?php gorunum('ust', ['sayfa_basligi' => $sayfa_basligi]); ?>

<div class="checkout-result" style="padding:100px 0; text-align:center;">
    <div
        style="max-width:600px; margin:0 auto; background:#fff; padding:60px; border-radius:30px; border:1px solid #e5e7eb; box-shadow:0 20px 50px rgba(0,0,0,0.05); position:relative; overflow:hidden;">

        <?php if ($basarili): ?>
            <!-- Başarı Durumu -->
            <div style="position:relative; z-index:2;">
                <div
                    style="width:100px; height:100px; background:var(--accent-light); color:var(--accent); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px; font-size:3rem; animation: bounceIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                    <i class="fas fa-check"></i>
                </div>
                <h1 style="font-weight:900; color:var(--dark); margin-bottom:15px; font-size:2rem;">Siparişiniz Alındı! 🎉
                </h1>
                <p style="color:var(--gray); font-size:1.1rem; line-height:1.6; margin-bottom:40px;">Ödemeniz başarıyla
                    onaylandı. Siparişiniz hazırlık aşamasına geçmiştir. Bizi tercih ettiğiniz için teşekkür ederiz.</p>

                <div style="display:flex; flex-direction:column; gap:15px;">
                    <a href="<?= BASE_URL ?>/client/order-detail.php?id=<?= $order_id ?>" class="buton"
                        style="background:var(--primary); color:#fff; padding:15px 30px; border-radius:30px; font-weight:800; text-decoration:none; display:block; transition:0.3s; box-shadow:0 10px 20px rgba(26,86,219,0.2);">Siparişimi
                        Takip Et</a>
                    <a href="<?= BASE_URL ?>/"
                        style="color:var(--gray); text-decoration:none; font-weight:700; font-size:0.9rem;">Ana Sayfaya
                        Dön</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Hata Durumu -->
            <div style="position:relative; z-index:2;">
                <div
                    style="width:100px; height:100px; background:#fee2e2; color:var(--danger); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px; font-size:3rem;">
                    <i class="fas fa-times"></i>
                </div>
                <h1 style="font-weight:900; color:var(--dark); margin-bottom:15px; font-size:2rem;">Ödeme Tamamlanamadı</h1>
                <p style="color:var(--gray); font-size:1.1rem; line-height:1.6; margin-bottom:40px;">Maalesef ödeme işlemi
                    sırasında bir hata oluştu. Kart bilgilerini kontrol edip tekrar denemeyi deneyebilirsiniz.</p>

                <div style="display:flex; flex-direction:column; gap:15px;">
                    <a href="<?= BASE_URL ?>/payment.php?id=<?= $order_id ?>" class="buton"
                        style="background:var(--danger); color:#fff; padding:15px 30px; border-radius:30px; font-weight:800; text-decoration:none; display:block; transition:0.3s; box-shadow:0 10px 20px rgba(220,38,38,0.2);">Tekrar
                        Dene</a>
                    <a href="<?= BASE_URL ?>/client/order-detail.php?id=<?= $order_id ?>"
                        style="color:var(--gray); text-decoration:none; font-weight:700; font-size:0.9rem;">Vazgeç ve
                        Siparişe Dön</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Süs Arkaplan -->
        <div
            style="position:absolute; top:-50px; right:-50px; width:200px; height:200px; background:var(--primary-light); opacity:0.3; border-radius:50%; z-index:1;">
        </div>
    </div>
</div>

<style>
    @keyframes bounceIn {
        0% {
            transform: scale(0.3);
            opacity: 0;
        }

        50% {
            transform: scale(1.05);
            opacity: 1;
        }

        70% {
            transform: scale(0.9);
        }

        100% {
            transform: scale(1);
        }
    }
</style>

<?php gorunum('alt'); ?>
