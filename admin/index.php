<?php
$pageTitle = 'Genel Bakış';
$adminPage = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$stats = istatistikleri_getir_detayli();
$son_siparisler = Database::fetchAll("SELECT o.*, u.first_name, u.last_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$durumlar = ['pending' => 'Beklemede', 'processing' => 'İşleniyor', 'shipped' => 'Kargoda', 'delivered' => 'Teslim Edildi', 'cancelled' => 'İptal'];
$durum_renkleri = ['pending' => 'yellow', 'processing' => 'blue', 'shipped' => 'purple', 'delivered' => 'green', 'cancelled' => 'red'];
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard-wrapper">
    <!-- Üst Satır: Ziyaretçi ve Hızlı Özet -->
    <div class="stats-grid-top">
        <div class="admin-card stats-card-visitor">
            <div class="card-header">
                <h3>Ziyaretçi Sayıları</h3>
                <i class="fas fa-ellipsis-v"></i>
            </div>
            <div class="visitor-summary">
                <div class="visitor-item">
                    <div class="dot blue"></div>
                    <div>
                        <span>Bugün Tekil</span>
                        <strong>900</strong>
                    </div>
                </div>
                <div class="visitor-item">
                    <div class="dot orange"></div>
                    <div>
                        <span>Bugün Çoğul</span>
                        <strong>1302</strong>
                    </div>
                </div>
            </div>
            <canvas id="visitorChart" style="max-height: 120px;"></canvas>
        </div>

        <div class="admin-card stats-card-realtime">
            <h3>Anlık Ziyaretçi</h3>
            <div class="realtime-display">
                <span class="pulse"></span>
                <strong>42</strong>
            </div>
            <div class="realtime-btns">
                <button class="active">Tüm Ziyaretçiler</button>
                <button>Sadece Üyeler</button>
            </div>
        </div>

        <div class="stats-mini-group">
            <div class="mini-card">
                <div class="icon pink"><i class="fas fa-shopping-basket"></i></div>
                <div>
                    <span>Onay Bekleyen Sipariş</span>
                    <strong><?= $stats['bekleyen_siparis'] ?></strong>
                </div>
            </div>
            <div class="mini-card">
                <div class="icon red"><i class="fas fa-truck"></i></div>
                <div>
                    <span>Gönderilecek Sipariş</span>
                    <strong><?= $stats['gonderilecek_siparis'] ?></strong>
                </div>
            </div>
            <div class="admin-card mini-chart-card">
                <div class="mini-chart-header">
                    <span>Kullanıcı Sayısı</span>
                    <strong><?= $stats['toplam_kullanici'] ?></strong>
                </div>
                <canvas id="userMiniChart" style="max-height: 60px;"></canvas>
            </div>
        </div>
    </div>

    <!-- İkinci Satır: Büyük Rakamlar -->
    <div class="stats-grid-main">
        <div class="admin-stat-premium">
            <div class="icon blue"><i class="fas fa-wallet"></i></div>
            <div>
                <span>Toplam Ciro</span>
                <strong><?= para_yaz($stats['ciro']) ?></strong>
            </div>
        </div>
        <div class="admin-stat-premium">
            <div class="icon orange"><i class="fas fa-chart-line"></i></div>
            <div>
                <span>Toplam Net Kar</span>
                <strong><?= para_yaz($stats['net_kar']) ?></strong>
            </div>
        </div>
        <div class="admin-stat-premium">
            <div class="icon green"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <span>Toplam Sipariş Sayısı</span>
                <strong><?= Database::fetch("SELECT COUNT(id) as c FROM orders WHERE status != 'cancelled'")['c'] ?></strong>
            </div>
        </div>
        <div class="admin-stat-premium">
            <div class="icon red"><i class="fas fa-box-open"></i></div>
            <div>
                <span>Toplam Satılan Ürün</span>
                <strong><?= Database::fetch("SELECT SUM(quantity) as s FROM order_items")['s'] ?? 0 ?></strong>
            </div>
        </div>
    </div>

    <!-- Üçüncü Satır: Büyük Grafik ve Listeler -->
    <div class="stats-grid-charts">
        <div class="admin-card sales-chart-card">
            <div class="card-header">
                <h3>Aylık Satış Toplamları</h3>
                <i class="fas fa-ellipsis-v"></i>
            </div>
            <canvas id="salesMainChart" style="max-height: 300px;"></canvas>
        </div>

        <div class="admin-card latest-sales-summary">
            <h3>Son Satış Toplamları</h3>
            <div class="sales-summary-list">
                <div class="summary-item">
                    <i class="fas fa-calendar-day blue" style="background: #eff6ff; color: #2563eb;"></i>
                    <div>
                        <span>Günün Cirosu</span>
                        <strong>0,00 TL / 0</strong>
                    </div>
                </div>
                <div class="summary-item">
                    <i class="fas fa-calendar-week orange" style="background: #fff7ed; color: #ea580c;"></i>
                    <div>
                        <span>Haftanın Cirosu</span>
                        <strong><?= para_yaz($stats['ciro'] * 0.15) ?> / 1</strong>
                    </div>
                </div>
                <div class="summary-item">
                    <i class="fas fa-calendar-alt green" style="background: #f0fdf4; color: #16a34a;"></i>
                    <div>
                        <span>Şubat Ayının Cirosu</span>
                        <strong><?= para_yaz($stats['ciro'] * 0.4) ?> / 2</strong>
                    </div>
                </div>
                <div class="summary-item">
                    <i class="fas fa-globe purple" style="background: #f5f3ff; color: #7c3aed;"></i>
                    <div>
                        <span>2026 Yılın Cirosu</span>
                        <strong><?= para_yaz($stats['ciro']) ?> / 3</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card recent-orders-card">
            <div class="card-header">
                <h3>Son Siparişler</h3>
                <select class="admin-select-sm" style="border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; padding: 2px 6px;">
                    <option>Onay Bekleyenler</option>
                </select>
            </div>
            <div class="recent-orders-list">
                <?php foreach($son_siparisler as $order): ?>
                <div class="order-item-mini">
                    <div class="order-icon <?= $durum_renkleri[$order['status']] ?>"><i class="fas fa-shopping-bag"></i></div>
                    <div class="order-info">
                        <strong><?= temiz($order['first_name'] . ' ' . $order['last_name']) ?></strong>
                        <span><?= date('d M, Y', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="order-price">
                        <strong><?= para_yaz($order['total']) ?></strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Dördüncü Satır: Alt Kategoriler ve Ziyaretçi Takibi -->
    <div class="stats-grid-bottom">
        <div class="bottom-left">
            <div class="quick-stats-row">
                <div class="admin-stat-simple">
                    <div class="icon small blue" style="background: #eff6ff; color: #2563eb;"><i class="fas fa-cube"></i></div>
                    <div>
                        <strong style="font-size: 16px; display: block;"><?= $stats['toplam_urun'] ?></strong>
                        <span style="font-size: 10px; color: #64748b;">Toplam Ürün Sayısı</span>
                    </div>
                </div>
                <div class="admin-stat-simple">
                    <div class="icon small orange" style="background: #fff7ed; color: #ea580c;"><i class="fas fa-folder"></i></div>
                    <div>
                        <strong style="font-size: 16px; display: block;"><?= $stats['toplam_kategori'] ?></strong>
                        <span style="font-size: 10px; color: #64748b;">Toplam Kategori Sayısı</span>
                    </div>
                </div>
            </div>
            <div class="admin-card payment-methods-card">
                <h3>Ödeme Seçimleri</h3>
                <div class="payment-progress" style="display: flex; flex-direction: column; gap: 15px;">
                    <div class="progress-item">
                        <div class="label" style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                            <span><i class="fas fa-credit-card"></i> Kredi Kartı</span> <strong>8,126.04 TL | 52%</strong>
                        </div>
                        <div class="bar" style="height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                            <div class="fill blue" style="width: 52%; height: 100%; background: #3b82f6;"></div>
                        </div>
                    </div>
                    <div class="progress-item">
                        <div class="label" style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                            <span><i class="fas fa-money-bill-wave"></i> Havale</span> <strong>7.637,40 TL | 48%</strong>
                        </div>
                        <div class="bar" style="height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                            <div class="fill green" style="width: 48%; height: 100%; background: #22c55e;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card visitor-track-card">
            <div class="card-header">
                <h3>Anlık Ziyaretçi Takibi</h3>
                <button class="admin-btn-sm admin-btn-primary" style="background: #2563eb; color: #fff; border: none; padding: 4px 10px; border-radius: 6px; font-size: 11px; cursor: pointer;">Yenile</button>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Kullanıcı</th>
                        <th>URL</th>
                        <th>Sepet</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--admin-gray)">Veri yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Beşinci Satır: Pazaryerleri ve Zaman Çizelgesi -->
    <div class="stats-grid-footer" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="admin-card marketplace-card">
            <h3>Pazaryerleri</h3>
            <div class="marketplace-total" style="margin-bottom: 20px;">
                <strong style="font-size: 24px; font-weight: 800; display: block;">1,765,337.00 TL</strong>
                <span style="font-size: 13px; color: #64748b;">Toplam Ciro</span>
            </div>
            <div class="marketplace-bar" style="height: 8px; display: flex; border-radius: 4px; overflow: hidden; margin-bottom: 20px;">
                <div class="m-segment n11" style="width: 1%; background: #22c55e;"></div>
                <div class="m-segment hb" style="width: 9%; background: #06b6d4;"></div>
                <div class="m-segment ty" style="width: 20%; background: #6366f1;"></div>
                <div class="m-segment cs" style="width: 12%; background: #f59e0b;"></div>
                <div class="m-segment pz" style="width: 7%; background: #ec4899;"></div>
                <div class="m-segment al" style="width: 23%; background: #ef4444;"></div>
                <div class="m-segment ak" style="width: 28%; background: #0f172a;"></div>
            </div>
            <div class="marketplace-list" style="display: flex; flex-direction: column; gap: 8px;">
                <div class="m-item" style="display: flex; justify-content: space-between; font-size: 12px;"><span><i class="dot n11" style="width: 8px; height: 8px; border-radius: 50%; display: inline-block; background: #22c55e;"></i> N11</span> <strong>20,948.00 TL | 1%</strong></div>
                <div class="m-item" style="display: flex; justify-content: space-between; font-size: 12px;"><span><i class="dot hb" style="width: 8px; height: 8px; border-radius: 50%; display: inline-block; background: #06b6d4;"></i> Hepsiburada</span> <strong>166,744.00 TL | 9%</strong></div>
                <div class="m-item" style="display: flex; justify-content: space-between; font-size: 12px;"><span><i class="dot ty" style="width: 8px; height: 8px; border-radius: 50%; display: inline-block; background: #6366f1;"></i> Trendyol</span> <strong>369,862.00 TL | 20%</strong></div>
            </div>
        </div>

        <div class="admin-card pending-tasks-card">
            <h3>Bekleyenler</h3>
            <div class="pending-timeline" style="display: flex; flex-direction: column; gap: 20px; margin-top: 10px;">
                <div class="timeline-item" style="display: flex; gap: 15px; position: relative;">
                    <div class="t-icon orange" style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b; margin-top: 4px; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #f59e0b;"></div>
                    <div class="t-content">
                        <strong style="font-size: 13px; display: block;">Onaylanmamış Siparişler</strong>
                        <p style="font-size: 12px; color: #64748b;">Ödeme toplam tutarı 1,440,536.07 TL olan <strong>215</strong> adet sipariş <a href="#" style="color: #3b82f6; font-weight: 600;">onay bekliyor.</a></p>
                    </div>
                </div>
                <div class="timeline-item" style="display: flex; gap: 15px; position: relative;">
                    <div class="t-icon red" style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444; margin-top: 4px; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #ef4444;"></div>
                    <div class="t-content">
                        <strong style="font-size: 13px; display: block;">Kargolanacak Siparişler</strong>
                        <p style="font-size: 12px; color: #64748b;">Ödeme toplam tutarı 15,763.44 TL olan <strong>4</strong> adet sipariş <a href="#" style="color: #3b82f6; font-weight: 600;">gönderilmeyi bekliyor.</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-wrapper { display: flex; flex-direction: column; gap: 20px; }
.stats-grid-top { display: grid; grid-template-columns: 1fr 280px 320px; gap: 20px; }
.stats-grid-main { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.stats-grid-charts { display: grid; grid-template-columns: 1fr 300px 320px; gap: 20px; }
.stats-grid-bottom { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }

.visitor-summary { display: flex; gap: 20px; margin-bottom: 10px; }
.visitor-item { display: flex; align-items: center; gap: 8px; }
.visitor-item .dot { width: 10px; height: 10px; border-radius: 50%; }
.dot.blue { background: #3b82f6; } .dot.orange { background: #f97316; }
.visitor-item span { font-size: 11px; color: #64748b; }
.visitor-item strong { font-size: 14px; display: block; }

.stats-card-realtime { text-align: center; display: flex; flex-direction: column; justify-content: center; }
.realtime-display { font-size: 32px; font-weight: 800; margin: 15px 0; color: #22c55e; display: flex; align-items: center; justify-content: center; gap: 10px; }
.pulse { width: 12px; height: 12px; background: #22c55e; border-radius: 50%; animation: pulse 1.5s infinite; }
.realtime-btns { display: flex; background: #f1f5f9; border-radius: 8px; padding: 4px; }
.realtime-btns button { flex: 1; border: none; background: none; padding: 6px; font-size: 11px; font-weight: 600; border-radius: 6px; cursor: pointer; }
.realtime-btns button.active { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

.stats-mini-group { display: flex; flex-direction: column; gap: 10px; }
.mini-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; display: flex; align-items: center; gap: 15px; flex: 1; }
.mini-card .icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.icon.pink { background: #fdf2f8; color: #db2777; }
.icon.red { background: #fef2f2; color: #dc2626; }
.mini-card span { font-size: 11px; color: #64748b; display: block; }
.mini-card strong { font-size: 18px; }

.admin-stat-premium { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; }
.admin-stat-premium .icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.admin-stat-premium span { font-size: 12px; color: #64748b; display: block; }
.admin-stat-premium strong { font-size: 18px; font-weight: 800; }

.sales-summary-list { display: flex; flex-direction: column; gap: 15px; }
.summary-item { display: flex; align-items: center; gap: 12px; }
.summary-item i { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.summary-item span { font-size: 11px; color: #64748b; display: block; }

.recent-orders-list { display: flex; flex-direction: column; gap: 12px; }
.order-item-mini { display: flex; align-items: center; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
.order-item-mini:last-child { border-bottom: none; }
.order-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
.order-icon.yellow { background: #fffbeb; color: #d97706; }
.order-icon.blue { background: #eff6ff; color: #2563eb; }
.order-info { flex: 1; }
.order-info strong { font-size: 13px; display: block; }
.order-info span { font-size: 11px; color: #64748b; }

.admin-stat-simple { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 10px; }
.admin-stat-simple .icon.small { width: 32px; height: 32px; border-radius: 6px; font-size: 14px; display: flex; align-items: center; justify-content: center; }

@keyframes pulse { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.2); } 100% { opacity: 1; transform: scale(1); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Visitor Chart
    new Chart(document.getElementById('visitorChart'), {
        type: 'line',
        data: {
            labels: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
            datasets: [{
                data: [100, 120, 150, 180, 200, 250, 220, 300, 350, 400, 380, 420, 500],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            layout: { padding: 0 },
            scales: { x: { display: false }, y: { display: false } }
        }
    });

    // User Mini Chart
    new Chart(document.getElementById('userMiniChart'), {
        type: 'bar',
        data: {
            labels: ['M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D', 'J', 'F'],
            datasets: [{
                data: [10, 15, 25, 30, 35, 45, 50, 60, 65, 75, 80, 84],
                backgroundColor: '#3b82f6',
                borderRadius: 4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            layout: { padding: 0 },
            scales: { x: { display: false }, y: { display: false } }
        }
    });

    // Sales Main Chart
    new Chart(document.getElementById('salesMainChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($stats['grafik_aylar']) ?>,
            datasets: [{
                label: 'Satışlar',
                data: <?= json_encode($stats['grafik_veriler']) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { borderDash: [5, 5], color: '#e2e8f0' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>