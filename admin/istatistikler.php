<?php
/**
 * Admin — Ziyaretçi Analitiği & Raporlar
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Ziyaretçi Analitiği';
$adminPage = 'analytics';

$stats = Marketing::getGrowthStats();

// Son Ziyaretçiler
$recentVisitors = Database::fetchAll("SELECT * FROM visitor_logs ORDER BY created_at DESC LIMIT 50");

// En Çok Gezilen Sayfalar
$topPages = Database::fetchAll("
    SELECT page_url, COUNT(*) as visit_count 
    FROM visitor_logs 
    GROUP BY page_url 
    ORDER BY visit_count DESC LIMIT 10
");

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <h1><i class="fas fa-chart-line" style="color:var(--admin-primary)"></i> Ziyaretçi Analitiği</h1>
</div>

<div class="admin-stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59, 130, 246, 0.1); color:#3b82f6">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Toplam Ziyaret</span>
            <span class="stat-value"><?= number_format($stats['total_visitors']) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16, 185, 129, 0.1); color:#10b981">
            <i class="fas fa-handshake"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Aktif Affiliate</span>
            <span class="stat-value"><?= $stats['total_affiliates'] ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245, 158, 11, 0.1); color:#f59e0b">
            <i class="fas fa-coins"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Toplam Komisyon</span>
            <span class="stat-value"><?= para_yaz($stats['total_commissions']) ?></span>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">
    
    <!-- En Çok Gezilen Sayfalar -->
    <div class="admin-card">
        <h3><i class="fas fa-file-alt"></i> En Çok Gezilen Sayfalar</h3>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sayfa URL</th>
                        <th style="width:100px">Ziyaret</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topPages as $p): ?>
                        <tr>
                            <td><small><?= e($p['page_url']) ?></small></td>
                            <td><strong><?= $p['visit_count'] ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Son Ziyaret Akışı -->
    <div class="admin-card">
        <h3><i class="fas fa-history"></i> Canlı Ziyaret Akışı</h3>
        <div class="admin-table-wrapper" style="max-height:400px;overflow-y:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>IP / Cihaz</th>
                        <th>Sayfa</th>
                        <th>Zaman</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentVisitors as $v): ?>
                        <tr>
                            <td>
                                <small><?= e($v['ip']) ?></small><br>
                                <small style="color:#aaa;font-size:10px" title="<?= e($v['user_agent']) ?>"><?= kirp($v['user_agent'], 30) ?></small>
                            </td>
                            <td><small><?= e($v['page_url']) ?></small></td>
                            <td><small><?= date('H:i:s', strtotime($v['created_at'])) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/header.php'; ?>
