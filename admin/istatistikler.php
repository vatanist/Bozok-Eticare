<?php
/**
 * Admin — Ziyaretçi Analitiği & Raporlar
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Ziyaretçi Analitiği';
$adminPage = 'analytics';

// ===================== BAŞLANGIÇ: YETKİ KONTROLÜ =====================
if (class_exists('Auth') && !Auth::can('manage_settings')) {
    http_response_code(403);
    die('Bu işlem için analitik görüntüleme yetkisi gereklidir.');
}
// ===================== BİTİŞ: YETKİ KONTROLÜ =====================

$stats = Marketing::getGrowthStats();
$panel = Marketing::analitikPanelVerisiGetir();

$grafik_etiketler = [];
$grafik_goruntuleme = [];
$grafik_sepete_ekleme = [];
foreach ($panel['saatlik12'] as $satir) {
    $grafik_etiketler[] = date('H:i', strtotime((string) $satir['saat']));
    $grafik_goruntuleme[] = (int) ($satir['goruntuleme'] ?? 0);
    $grafik_sepete_ekleme[] = (int) ($satir['sepete_ekleme'] ?? 0);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <h1><i class="fas fa-chart-line" style="color:var(--admin-primary)"></i> Ziyaretçi Analitiği</h1>
</div>

<div class="admin-stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59, 130, 246, 0.1); color:#3b82f6"><i class="fas fa-users"></i></div>
        <div class="stat-info"><span class="stat-label">Toplam Olay</span><span class="stat-value"><?= number_format((int) $panel['toplam_olay']) ?></span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16, 185, 129, 0.1); color:#10b981"><i class="fas fa-user-secret"></i></div>
        <div class="stat-info"><span class="stat-label">Tekil Anonim Ziyaretçi</span><span class="stat-value"><?= number_format((int) $panel['tekil_anonim']) ?></span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245, 158, 11, 0.1); color:#f59e0b"><i class="fas fa-eye"></i></div>
        <div class="stat-info"><span class="stat-label">Son 12 Saat Görüntüleme</span><span class="stat-value"><?= number_format((int) $panel['son12_goruntuleme']) ?></span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(168, 85, 247, 0.1); color:#a855f7"><i class="fas fa-cart-plus"></i></div>
        <div class="stat-info"><span class="stat-label">Son 12 Saat Sepete Ekleme</span><span class="stat-value"><?= number_format((int) $panel['son12_sepete_ekleme']) ?></span></div>
    </div>
</div>

<div class="admin-card" style="margin-top:20px;">
    <h3><i class="fas fa-clock"></i> Son 12 Saat Olay Grafiği</h3>
    <?php if (empty($grafik_etiketler)): ?>
        <p style="color:#6b7280">Son 12 saatte kayıtlı analitik olay bulunmuyor.</p>
    <?php else: ?>
        <canvas id="analitik12saat" height="110"></canvas>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
    <div class="admin-card">
        <h3><i class="fas fa-globe"></i> İl Bazlı Dağılım</h3>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead><tr><th>İl</th><th style="width:120px">Olay</th></tr></thead>
                <tbody>
                <?php if (empty($panel['iller'])): ?>
                    <tr><td colspan="2">Veri yok</td></tr>
                <?php else: ?>
                    <?php foreach ($panel['iller'] as $satir): ?>
                        <tr><td><?= e((string) ($satir['il'] ?? 'Bilinmiyor')) ?></td><td><strong><?= (int) ($satir['adet'] ?? 0) ?></strong></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <h3><i class="fas fa-browser"></i> Tarayıcı Dağılımı</h3>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead><tr><th>Tarayıcı</th><th style="width:120px">Olay</th></tr></thead>
                <tbody>
                <?php if (empty($panel['tarayicilar'])): ?>
                    <tr><td colspan="2">Veri yok</td></tr>
                <?php else: ?>
                    <?php foreach ($panel['tarayicilar'] as $satir): ?>
                        <tr><td><?= e((string) ($satir['tarayici'] ?? 'Diger')) ?></td><td><strong><?= (int) ($satir['adet'] ?? 0) ?></strong></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="admin-card" style="margin-top:20px;">
    <h3><i class="fas fa-history"></i> Son Analitik Olaylar</h3>
    <div class="admin-table-wrapper" style="max-height:430px;overflow:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Olay</th>
                    <th>Sayfa</th>
                    <th>Anonim ID</th>
                    <th>IP</th>
                    <th>İl / İlçe</th>
                    <th>Tarayıcı</th>
                    <th>Zaman</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($panel['son_olaylar'])): ?>
                    <tr><td colspan="7">Henüz olay kaydı yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($panel['son_olaylar'] as $olay): ?>
                        <tr>
                            <td><?= e((string) $olay['event_name']) ?></td>
                            <td><small><?= e(kirp((string) ($olay['page_url'] ?? ''), 40)) ?></small></td>
                            <td><small><?= e((string) ($olay['anonim_id'] ?? '-')) ?></small></td>
                            <td><small><?= e((string) ($olay['ip'] ?? '-')) ?></small></td>
                            <td><small><?= e((string) ($olay['il'] ?? 'Bilinmiyor')) ?> / <?= e((string) ($olay['ilce'] ?? 'Bilinmiyor')) ?></small></td>
                            <td><?= e((string) ($olay['tarayici'] ?? 'Diger')) ?></td>
                            <td><small><?= e((string) ($olay['created_at'] ?? '')) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card" style="margin-top:20px;">
    <h3><i class="fas fa-seedling"></i> Büyüme Özeti</h3>
    <p style="margin:0;color:#6b7280">
        Toplam ziyaret: <strong><?= number_format((int) ($stats['total_visitors'] ?? 0)) ?></strong> ·
        Aktif affiliate: <strong><?= number_format((int) ($stats['total_affiliates'] ?? 0)) ?></strong> ·
        Toplam komisyon: <strong><?= para_yaz((float) ($stats['total_commissions'] ?? 0)) ?></strong>
    </p>
</div>

<?php if (!empty($grafik_etiketler)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = <?= json_encode($grafik_etiketler, JSON_UNESCAPED_UNICODE) ?>;
    const goruntuleme = <?= json_encode($grafik_goruntuleme, JSON_UNESCAPED_UNICODE) ?>;
    const sepeteEkleme = <?= json_encode($grafik_sepete_ekleme, JSON_UNESCAPED_UNICODE) ?>;

    const ctx = document.getElementById('analitik12saat');
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Görüntüleme',
                    data: goruntuleme,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,.15)',
                    fill: true,
                    tension: 0.35
                },
                {
                    label: 'Sepete Ekleme',
                    data: sepeteEkleme,
                    borderColor: '#a855f7',
                    backgroundColor: 'rgba(168,85,247,.12)',
                    fill: true,
                    tension: 0.35
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
})();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
