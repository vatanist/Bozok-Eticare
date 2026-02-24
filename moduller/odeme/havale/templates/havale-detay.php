<?php
/**
 * Havale Modülü - Ödeme Ekranı Şablonu
 */
?>
<div class="havale-container" style="padding: 30px; background: #fff; border-radius: 20px; border: 1px solid #e5e7eb;">
    <h2 style="font-weight: 800; margin-bottom: 25px; color: var(--dark);">Havale / EFT Bilgileri</h2>
    <p style="color: var(--gray); margin-bottom: 30px; font-size: 0.95rem;">Lütfen aşağıdaki banka hesaplarından birine
        sipariş tutarını <strong>Sipariş No</strong> açıklama kısmına yazarak gönderin.</p>

    <div class="banka-grid"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <?php foreach ($bankalar as $b): ?>
            <div class="banka-card"
                style="border: 2px solid #f1f5f9; padding: 25px; border-radius: 15px; position: relative; transition: 0.3s;"
                onmouseover="this.style.borderColor='var(--primary-light)';" onmouseout="this.style.borderColor='#f1f5f9';">
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 20px;">
                    <div
                        style="width: 50px; height: 50px; background: #f8fafc; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--primary);">
                        <i class="fas fa-university"></i>
                    </div>
                    <strong style="font-size: 1.1rem; color: var(--dark);">
                        <?= $b['ad'] ?>
                    </strong>
                </div>

                <div style="font-size: 0.85rem; display: grid; gap: 8px;">
                    <div style="display: flex; justify-content: space-between;"> <span
                            style="color: var(--gray);">Alıcı:</span> <strong>
                            <?= $b['alici'] ?>
                        </strong> </div>
                    <div style="display: flex; justify-content: space-between;"> <span
                            style="color: var(--gray);">Şube:</span> <strong>
                            <?= $b['sube'] ?>
                        </strong> </div>
                    <div style="display: flex; justify-content: space-between;"> <span style="color: var(--gray);">Hesap
                            No:</span> <strong>
                            <?= $b['hesap_no'] ?>
                        </strong> </div>
                    <div
                        style="background: #f8fafc; padding: 10px; border-radius: 8px; margin-top: 10px; border: 1px dashed #cbd5e1; font-family: monospace; font-size: 0.9rem; text-align: center;">
                        <?= $b['iban'] ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 35px; text-align: center;">
        <a href="<?= BASE_URL ?>/client/orders.php" class="buton"
            style="background: var(--primary); color: #fff; padding: 15px 40px; border-radius: 30px; font-weight: 800; text-decoration: none;">Ödemeyi
            Yaptım, Siparişlerime Git <i class="fas fa-arrow-right"></i></a>
    </div>
</div>