<?php gorunum('ortak/ust', ['sayfa_basligi' => $sayfa_basligi]); ?>

<div class="admin-plugins-page" style="padding: 30px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <div>
            <h1 style="font-weight: 900; color: var(--dark); margin: 0; font-size: 2.2rem;">CMS Modülleri</h1>
            <p style="color: var(--gray); margin: 5px 0 0;">Yazılımın çekirdek özelliklerini buradan yönetin.</p>
        </div>
        <button class="buton"
            style="background: var(--primary); color: #fff; padding: 12px 25px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer;">
            <i class="fas fa-plus"></i> Yeni Eklenti Yükle
        </button>
    </div>

    <div class="plugin-grid"
        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px;">
        <?php foreach ($moduller as $m): ?>
            <div class="plugin-card"
                style="background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 25px; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; flex-direction: column; position: relative; overflow: hidden;">

                <!-- Durum Şeridi -->
                <div
                    style="position: absolute; top: 0; left: 0; width: 5px; height: 100%; background: <?= $m['active'] ? 'var(--success)' : '#cbd5e0' ?>;">
                </div>

                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div
                        style="width: 60px; height: 60px; background: #f8fafc; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--primary); border: 1px solid #edf2f7;">
                        <i class="fas <?= $m['category'] == 'odeme' ? 'fa-credit-card' : 'fa-puzzle-piece' ?>"></i>
                    </div>
                    <div style="flex: 1;">
                        <h3 style="margin: 0; font-weight: 800; color: var(--dark); font-size: 1.1rem;">
                            <?= temiz($m['name']) ?>
                        </h3>
                        <div style="font-size: 0.8rem; color: var(--gray); margin-top: 3px;">
                            Sürüm: <span style="font-weight: 700; color: var(--dark);">
                                <?= $m['version'] ?>
                            </span> |
                            Yazar: <span style="font-weight: 700; color: var(--primary);">
                                <?= $m['author'] ?>
                            </span>
                        </div>
                    </div>
                </div>

                <p style="font-size: 0.9rem; color: #64748b; line-height: 1.6; margin-bottom: 25px; flex: 1;">
                    <?= temiz($m['description']) ?>
                </p>

                <div
                    style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; pt: 20px; padding-top: 20px;">
                    <div
                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; color: <?= $m['active'] ? 'var(--success)' : 'var(--gray)' ?>;">
                        <i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 5px;"></i>
                        <?= $m['active'] ? 'Aktif' : 'Pasif' ?>
                    </div>
                    <div style="display: flex; gap: 12px; margin-top: 20px;">
                        <button class="buton eklenti-toggle" data-code="<?= $m['code'] ?>"
                            data-status="<?= $m['active'] ? 0 : 1 ?>"
                            style="flex: 1; background: <?= $m['active'] ? '#f1f5f9' : 'var(--primary)' ?>; color: <?= $m['active'] ? 'var(--dark)' : '#fff' ?>; border: none; padding: 12px; border-radius: 12px; font-weight: 800; cursor: pointer;">
                            <i class="fas fa-fw <?= $m['active'] ? 'fa-pause' : 'fa-play' ?>"></i>
                            <?= $m['active'] ? 'Durdur' : 'Etkinleştir' ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.eklenti-toggle').forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            const status = this.getAttribute('data-status');

            fetch('<?= BASE_URL ?>/ajax/extension-toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `type=module&code=${code}&status=${status}`
            }).then(r => r.json()).then(res => {
                if (res.success) location.reload();
                else alert('Hata: ' + res.error);
            });
        });
    });
</script>

<?php gorunum('ortak/alt'); ?>