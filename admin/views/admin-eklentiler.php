<?php
// ===================== BAŞLANGIÇ: ADMIN EKLENTİLER GÖRÜNÜMÜ =====================
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= temiz($sayfa_basligi ?? 'Eklentiler') ?> - Admin</title>
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body style="background:#f8fafc; margin:0; font-family:Inter, sans-serif;">
    <main style="max-width:1200px; margin:40px auto; padding:0 16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <div>
                <h1 style="margin:0; font-size:2rem;">Eklentiler & Modüller</h1>
                <p style="margin:6px 0 0; color:#64748b;">Admin görünümü tema bağımsız çalışır.</p>
            </div>
            <a href="<?= url('admin/index.php') ?>" style="text-decoration:none; color:#334155;"><i class="fas fa-arrow-left"></i> Panele Dön</a>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:20px;">
            <?php foreach (($moduller ?? []) as $m): ?>
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:16px;">
                    <h3 style="margin:0 0 6px;"><?= temiz($m['name']) ?></h3>
                    <p style="margin:0 0 8px; color:#475569; font-size:13px;">Kod: <?= temiz($m['code']) ?> | Kategori: <?= temiz($m['category']) ?></p>
                    <p style="margin:0 0 8px; color:#475569; font-size:13px;">Sürüm: <?= temiz($m['version']) ?> | Yazar: <?= temiz($m['author']) ?></p>
                    <p style="margin:0 0 12px; color:#64748b; font-size:14px;"><?= temiz($m['description']) ?></p>

                    <button class="eklenti-toggle"
                        data-code="<?= temiz($m['code']) ?>"
                        data-status="<?= !empty($m['active']) ? 0 : 1 ?>"
                        style="border:none; background:<?= !empty($m['active']) ? '#f59e0b' : '#10b981' ?>; color:#fff; border-radius:10px; padding:9px 12px; cursor:pointer; font-weight:700;">
                        <?= !empty($m['active']) ? 'Durdur' : 'Etkinleştir' ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        document.querySelectorAll('.eklenti-toggle').forEach(btn => {
            btn.addEventListener('click', function () {
                const code = this.getAttribute('data-code');
                const status = this.getAttribute('data-status');

                fetch('<?= url('ajax/extension-toggle.php') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `type=module&code=${code}&status=${status}`
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
// ===================== BİTİŞ: ADMIN EKLENTİLER GÖRÜNÜMÜ =====================
?>
