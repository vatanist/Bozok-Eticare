<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Çerez Yönetimi';
$adminPage = 'cookie_management';

// ===================== BAŞLANGIÇ: YETKİ KONTROLÜ =====================
if (class_exists('Auth') && !Auth::can('manage_settings')) {
    http_response_code(403);
    die('Bu işlem için ayar yönetim yetkisi gereklidir.');
}
// ===================== BİTİŞ: YETKİ KONTROLÜ =====================

// ===================== BAŞLANGIÇ: AYAR KAYDI =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dogrula_csrf();

    $tamIpSakla = !empty($_POST['tam_ip_sakla']);
    $saklamaGunu = (int) ($_POST['kayit_saklama_gunu'] ?? 180);
    if ($saklamaGunu < 1) {
        $saklamaGunu = 1;
    }
    if ($saklamaGunu > 3650) {
        $saklamaGunu = 3650;
    }

    option_set('tam_ip_sakla', $tamIpSakla, 'gizlilik');
    option_set('kayit_saklama_gunu', $saklamaGunu, 'gizlilik');

    mesaj('cerez_admin', 'Çerez/KVKK ayarları güncellendi.', 'success');
    git('/admin/cerez-yonetimi.php');
}
// ===================== BİTİŞ: AYAR KAYDI =====================

$tamIpSaklaAktif = (bool) option_get('tam_ip_sakla', false, 'gizlilik');
$saklamaGunuAktif = (int) option_get('kayit_saklama_gunu', 180, 'gizlilik');

$ozet = class_exists('CerezYonetimi') ? CerezYonetimi::ozetIstatistikGetir() : ['kabul' => 0, 'reddet' => 0, 'tercih' => 0, 'toplam' => 0];
$kayitlar = class_exists('CerezYonetimi') ? CerezYonetimi::sonKayitlariGetir(50) : [];

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <h1><i class="fas fa-cookie-bite" style="color:var(--admin-primary)"></i> Çerez Yönetimi</h1>
</div>

<?php mesaj_goster('cerez_admin'); ?>

<div class="admin-stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(34, 197, 94, 0.1); color:#22c55e"><i class="fas fa-check"></i></div>
        <div class="stat-info"><span class="stat-label">Kabul</span><span class="stat-value"><?= number_format((int) $ozet['kabul']) ?></span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(239, 68, 68, 0.1); color:#ef4444"><i class="fas fa-ban"></i></div>
        <div class="stat-info"><span class="stat-label">Reddet</span><span class="stat-value"><?= number_format((int) $ozet['reddet']) ?></span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59, 130, 246, 0.1); color:#3b82f6"><i class="fas fa-sliders-h"></i></div>
        <div class="stat-info"><span class="stat-label">Özelleştirilmiş Tercih</span><span class="stat-value"><?= number_format((int) $ozet['tercih']) ?></span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(168, 85, 247, 0.1); color:#a855f7"><i class="fas fa-list"></i></div>
        <div class="stat-info"><span class="stat-label">Toplam Kayıt</span><span class="stat-value"><?= number_format((int) $ozet['toplam']) ?></span></div>
    </div>
</div>

<div class="admin-card" style="margin-top:20px;">
    <h3><i class="fas fa-shield-alt"></i> KVKK/GDPR Ayarları</h3>
    <form method="POST">
        <?= csrf_kod() ?>
        <div class="admin-form-grid" style="grid-template-columns:1fr 1fr; gap:16px; margin-top:15px;">
            <div>
                <label style="display:block;font-weight:600;margin-bottom:6px;">Kayıt Saklama Süresi (gün)</label>
                <input type="number" name="kayit_saklama_gunu" min="1" max="3650" value="<?= (int) $saklamaGunuAktif ?>" class="admin-input" required>
            </div>
            <div style="display:flex;align-items:flex-end;">
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <input type="checkbox" name="tam_ip_sakla" value="1" <?= $tamIpSaklaAktif ? 'checked' : '' ?>>
                    Tam IP sakla (varsayılan kapalı - KVKK için maskeli önerilir)
                </label>
            </div>
        </div>
        <button type="submit" class="admin-btn" style="margin-top:12px;"><i class="fas fa-save"></i> Ayarları Kaydet</button>
    </form>
</div>

<div class="admin-card" style="margin-top:20px;">
    <h3><i class="fas fa-history"></i> Son Tercih Kayıtları</h3>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Anonim ID</th>
                    <th>Kullanıcı</th>
                    <th>IP</th>
                    <th>Tarayıcı</th>
                    <th>Karar</th>
                    <th>Analitik</th>
                    <th>Pazarlama</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kayitlar)): ?>
                    <tr><td colspan="9">Henüz tercih kaydı yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($kayitlar as $k): ?>
                        <tr>
                            <td><?= (int) $k['id'] ?></td>
                            <td><small><?= e($k['anonim_id']) ?></small></td>
                            <td><?= $k['user_id'] ? ('#' . (int) $k['user_id']) : '-' ?></td>
                            <td><small><?= e($k['ip_adresi']) ?></small></td>
                            <td><small title="<?= e($k['user_agent']) ?>"><?= e(kirp((string) $k['user_agent'], 35)) ?></small></td>
                            <td><?= e($k['karar']) ?></td>
                            <td><?= !empty($k['analitik_izin']) ? 'Evet' : 'Hayır' ?></td>
                            <td><?= !empty($k['pazarlama_izin']) ? 'Evet' : 'Hayır' ?></td>
                            <td><?= e($k['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
