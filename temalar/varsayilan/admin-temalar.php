<?php gorunum('ortak/ust', ['sayfa_basligi' => $sayfa_basligi]); ?>

<div class="admin-themes-page" style="padding: 30px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <div>
            <h1 style="font-weight: 900; color: var(--dark); margin: 0; font-size: 2.2rem;">Görünüm & Temalar</h1>
            <p style="color: var(--gray); margin: 5px 0 0;">Mağazanızın tasarımını tek tıkla değiştirin.</p>
        </div>
        <button class="buton"
            style="background: var(--primary); color: #fff; padding: 12px 25px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer;">
            <i class="fas fa-upload"></i> Yeni Tema Yükle
        </button>
    </div>

    <div class="theme-grid"
        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 30px;">
        <?php foreach ($temalar as $t): ?>
            <div class="theme-card"
                style="background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; overflow: hidden; transition: 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: relative;">

                <!-- Tema Önizleme -->
                <div style="aspect-ratio: 16/10; background: #f8fafc; overflow: hidden; position: relative;">
                    <img src="<?= $t['screenshot'] ?>" alt="<?= $t['name'] ?>"
                        style="width: 100%; height: 100%; object-fit: cover; transition: 0.5s;"
                        onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';"
                        onerror="this.src='https://placehold.co/600x400/f8fafc/6366f1?text=Tema+Onizleme';">

                    <?php if ($t['active']): ?>
                        <div
                            style="position: absolute; top: 20px; right: 20px; background: var(--success); color: #fff; padding: 8px 20px; border-radius: 30px; font-weight: 800; font-size: 0.8rem; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <i class="fas fa-check-circle"></i> AKTİF
                        </div>
                    <?php endif; ?>
                </div>

                <div style="padding: 25px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="margin: 0; font-weight: 800; color: var(--dark); font-size: 1.3rem;">
                                <?= temiz($t['name']) ?>
                            </h3>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">
                                Sürüm: <span style="font-weight: 700; color: var(--dark);">
                                    <?= $t['version'] ?>
                                </span> |
                                Tasarlayan: <span style="font-weight: 700; color: var(--primary);">
                                    <?= $t['author'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6; margin-bottom: 25px;">
                        <?= temiz($t['description']) ?>
                    </p>

                    <div style="display: flex; gap: 12px;">
                        <?php if (!$t['active']): ?>
                            <button class="buton tema-etkinlestir" data-code="<?= $t['code'] ?>"
                                style="flex: 1; background: var(--primary); color: #fff; border: none; padding: 12px; border-radius: 12px; font-weight: 800; cursor: pointer;">
                                Etkinleştir
                            </button>
                        <?php endif; ?>
                        <button class="buton"
                            style="flex: 1; background: #f1f5f9; color: var(--dark); border: none; padding: 12px; border-radius: 12px; font-weight: 800; cursor: pointer;">
                            Canlı Önizleme
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.tema-etkinlestir').forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            fetch('<?= BASE_URL ?>/ajax/extension-toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `type=theme&code=${code}`
            }).then(r => r.json()).then(res => {
                if (res.success) location.reload();
                else alert('Hata: ' + res.error);
            });
        });
    });
</script>

<?php gorunum('ortak/alt'); ?>