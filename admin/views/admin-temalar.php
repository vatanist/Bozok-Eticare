<?php
// ===================== BAŞLANGIÇ: ADMIN TEMALAR GÖRÜNÜMÜ =====================
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= temiz($sayfa_basligi ?? 'Temalar') ?> - Admin</title>
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body style="background:#f8fafc; margin:0; font-family:Inter, sans-serif;">
    <main style="max-width:1200px; margin:40px auto; padding:0 16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <div>
                <h1 style="margin:0; font-size:2rem;">Görünüm & Temalar</h1>
                <p style="margin:6px 0 0; color:#64748b;">Tema yönetimi (admin görünümü temadan bağımsız)</p>
            </div>
            <a href="<?= url('admin/index.php') ?>" style="text-decoration:none; color:#334155;"><i class="fas fa-arrow-left"></i> Panele Dön</a>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:20px;">
            <?php foreach (($temalar ?? []) as $t): ?>
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">
                    <img src="<?= temiz($t['screenshot']) ?>" alt="<?= temiz($t['name']) ?>" style="width:100%; height:190px; object-fit:cover; background:#f1f5f9;">
                    <div style="padding:16px;">
                        <h3 style="margin:0 0 8px;"><?= temiz($t['name']) ?></h3>
                        <p style="margin:0 0 6px; color:#475569; font-size:13px;">Sürüm: <?= temiz($t['version']) ?> | Yazar: <?= temiz($t['author']) ?></p>
                        <p style="margin:0 0 8px; color:#475569; font-size:13px;">Metadata: <?= temiz($t['metadata_kaynagi'] ?? 'yok') ?></p>

                        <?php if (!empty($t['dogrulama_hatalari'])): ?>
                            <div style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:10px; padding:8px; margin-bottom:8px; font-size:12px;">
                                <strong>Hata:</strong>
                                <ul style="margin:6px 0 0 18px; padding:0;">
                                    <?php foreach ($t['dogrulama_hatalari'] as $hata): ?>
                                        <li><?= temiz($hata) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($t['dogrulama_uyarilari'])): ?>
                            <div style="background:#fffbeb; color:#92400e; border:1px solid #fde68a; border-radius:10px; padding:8px; margin-bottom:8px; font-size:12px;">
                                <strong>Uyarı:</strong>
                                <ul style="margin:6px 0 0 18px; padding:0;">
                                    <?php foreach ($t['dogrulama_uyarilari'] as $uyari): ?>
                                        <li><?= temiz($uyari) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($t['active'])): ?>
                            <span style="display:inline-block; padding:6px 10px; border-radius:999px; background:#dcfce7; color:#166534; font-size:12px; font-weight:700;">AKTİF</span>
                        <?php else: ?>
                            <button class="tema-etkinlestir" data-code="<?= temiz($t['code']) ?>" style="border:none; background:#2563eb; color:#fff; border-radius:10px; padding:9px 12px; cursor:pointer; font-weight:700;">
                                Etkinleştir
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        document.querySelectorAll('.tema-etkinlestir').forEach(btn => {
            btn.addEventListener('click', function () {
                const code = this.getAttribute('data-code');
                fetch('<?= url('ajax/extension-toggle.php') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `type=theme&code=${code}`
                }).then(r => r.json()).then(res => {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert('Hata: ' + res.error);
                    }
                });
            });
        });
    </script>
</body>

</html>
<?php
// ===================== BİTİŞ: ADMIN TEMALAR GÖRÜNÜMÜ =====================
?>
