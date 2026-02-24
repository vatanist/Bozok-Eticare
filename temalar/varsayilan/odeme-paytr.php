<?php gorunum('ust', ['sayfa_basligi' => $sayfa_basligi]); ?>

<div class="paytr-container" style="padding:50px 0;">
    <div style="max-width:900px; margin:0 auto;">

        <div
            style="background:#fff; border:1px solid #e5e7eb; border-radius:24px; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.05);">

            <!-- Bilgi Çubuğu -->
            <div
                style="padding:25px 40px; background:#f9fafb; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="font-weight:800; margin:0; font-size:1.4rem;"><i class="fas fa-shield-alt"
                            style="color:var(--accent);"></i> Güvenli Ödeme</h2>
                    <p style="margin:5px 0 0; font-size:0.85rem; color:var(--gray);">Sipariş No: <strong>#
                            <?= $order['order_number'] ?>
                        </strong></p>
                </div>
                <div style="text-align:right;">
                    <span style="display:block; font-size:0.85rem; color:var(--gray); font-weight:600;">Ödenecek
                        Tutar</span>
                    <strong style="font-size:1.8rem; color:var(--primary); font-weight:900;">
                        <?= para_yaz($order['total']) ?>
                    </strong>
                </div>
            </div>

            <!-- PayTR Iframe -->
            <div style="min-height:600px; padding:20px; position:relative;">
                <div id="payment-loader"
                    style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#fff; z-index:5;">
                    <div
                        style="width:40px; height:40px; border:4px solid var(--primary-light); border-top-color:var(--primary); border-radius:50%; animation: spin 1s linear infinite;">
                    </div>
                    <p style="margin-top:15px; font-weight:600; color:var(--gray);">PayTR Güvenli Ödeme Sayfası
                        Yükleniyor...</p>
                </div>

                <iframe src="https://www.paytr.com/odeme/guvenli/<?= $token ?>" id="paytriframe" frameborder="0"
                    scrolling="no" style="width: 100%; height: 600px; visibility:hidden;"
                    onload="this.style.visibility='visible'; document.getElementById('payment-loader').style.display='none';"></iframe>
            </div>

            <!-- Alt Bilgi -->
            <div style="padding:20px 40px; background:#fff; border-top:1px solid #f3f4f6; text-align:center;">
                <div style="display:flex; justify-content:center; gap:30px; margin-bottom:15px; opacity:0.6;">
                    <i class="fab fa-cc-visa fa-2x"></i>
                    <i class="fab fa-cc-mastercard fa-2x"></i>
                    <i class="fab fa-cc-stripe fa-2x"></i>
                </div>
                <p style="font-size:0.8rem; color:var(--gray-light);">Ödemeniz 256-bit SSL şifreleme ile %100 güvenli
                    bir şekilde işlenmektedir. Kart bilgileriniz V-Commerce tarafından kaydedilmez.</p>
            </div>
        </div>

        <div style="margin-top:30px; text-align:center;">
            <a href="<?= BASE_URL ?>/client/order-detail.php?id=<?= $order['id'] ?>"
                style="color:var(--gray); text-decoration:none; font-weight:600; font-size:0.9rem; transition:0.2s;"
                onmouseover="this.style.color='var(--danger)';" onmouseout="this.style.color='var(--gray)';">
                <i class="fas fa-times-circle"></i> Ödemeyi İptal Et ve Siparişe Dön
            </a>
        </div>
    </div>
</div>

<script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
<script>iFrameResize({}, '#paytriframe');</script>

<style>
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<?php gorunum('alt'); ?>
