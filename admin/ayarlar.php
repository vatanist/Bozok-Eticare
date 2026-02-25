<?php
$pageTitle = 'Sistem Ayarları';
$adminPage = 'settings';
require_once __DIR__ . '/includes/header.php';

// Kayıt İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = $_POST['setting_group'] ?? 'general';
    $settings = $_POST['s'] ?? [];

    foreach ($settings as $name => $value) {
        // Özel durumlar (Checkbox vb)
        if ($name === 'paytr_test_mode') $value = ($value === 'on' || $value === '1') ? '1' : '0';
        
        Settings::set($name, $value, $group);
    }

    flash('admin_settings', 'Ayarlar başarıyla kaydedildi.', 'success');
    redirect('/admin/ayarlar.php?tab=' . $group);
}

$activeTab = $_GET['tab'] ?? 'general';
$allSettings = Settings::group($activeTab);
?>

<div class="admin-header">
    <h1><i class="fas fa-cog" style="color:var(--admin-primary)"></i> Kurumsal Sistem Ayarları</h1>
    <p>Mağazanızın tüm teknik ve görsel konfigürasyonunu buradan yönetin.</p>
</div>

<?php showFlash('admin_settings'); ?>

<div class="settings-container">
    <div class="settings-sidebar">
        <a href="?tab=general" class="tab-link <?= $activeTab === 'general' ? 'active' : '' ?>">
            <i class="fas fa-globe"></i> Genel Ayarlar
        </a>
        <a href="?tab=shipping" class="tab-link <?= $activeTab === 'shipping' ? 'active' : '' ?>">
            <i class="fas fa-truck"></i> Kargo & Teslimat
        </a>
        <a href="?tab=payment" class="tab-link <?= $activeTab === 'payment' ? 'active' : '' ?>">
            <i class="fas fa-credit-card"></i> Ödeme Yöntemleri
        </a>
        <a href="?tab=social" class="tab-link <?= $activeTab === 'social' ? 'active' : '' ?>">
            <i class="fas fa-share-alt"></i> Sosyal Medya
        </a>
        <a href="?tab=theme" class="tab-link <?= $activeTab === 'theme' ? 'active' : '' ?>">
            <i class="fas fa-palette"></i> Tema & Görünüm
        </a>
    </div>

    <div class="settings-content">
        <form method="POST" class="admin-form">
            <input type="hidden" name="setting_group" value="<?= e($activeTab) ?>">

            <?php if ($activeTab === 'general'): ?>
                <div class="admin-card">
                    <h3>Genel Mağaza Bilgileri</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Site Adı</label>
                            <input type="text" name="s[site_name]" class="form-control" value="<?= e(Settings::get('site_name', 'general', 'Bozok E-Ticaret')) ?>">
                        </div>
                        <div class="form-group">
                            <label>Site E-posta</label>
                            <input type="email" name="s[site_email]" class="form-control" value="<?= e(Settings::get('site_email')) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Site Açıklaması (Meta Description)</label>
                        <textarea name="s[site_description]" class="form-control" rows="3"><?= e(Settings::get('site_description')) ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Destek Hattı / Telefon</label>
                            <input type="text" name="s[site_phone]" class="form-control" value="<?= e(Settings::get('site_phone')) ?>">
                        </div>
                        <div class="form-group">
                            <label>Mağaza Adresi</label>
                            <input type="text" name="s[site_address]" class="form-control" value="<?= e(Settings::get('site_address')) ?>">
                        </div>
                    </div>
                </div>

            <?php elseif ($activeTab === 'shipping'): ?>
                <div class="admin-card">
                    <h3>Kargo ve Lojistik Ayarları</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Sabit Kargo Ücreti (₺)</label>
                            <input type="number" name="s[shipping_cost]" class="form-control" step="0.01" value="<?= e(Settings::get('shipping_cost', 'shipping', 49.90)) ?>">
                        </div>
                        <div class="form-group">
                            <label>Ücretsiz Kargo Alt Limiti (₺)</label>
                            <input type="number" name="s[free_shipping_limit]" class="form-control" step="0.01" value="<?= e(Settings::get('free_shipping_limit', 'shipping', 2000)) ?>">
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Pazaryeri Notu:</strong> Trendyol veya Hepsiburada entegrasyonu aktifse, kargo şablonları oradaki API üzerinden senkronize edilir.
                    </div>
                </div>

            <?php elseif ($activeTab === 'payment'): ?>
                <div class="admin-card">
                    <h3>PayTR Sanal POS</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Merchant ID</label>
                            <input type="text" name="s[paytr_merchant_id]" class="form-control" value="<?= e(Settings::get('paytr_merchant_id', 'payment')) ?>">
                        </div>
                        <div class="form-group">
                            <label>Merchant Key</label>
                            <input type="text" name="s[paytr_merchant_key]" class="form-control" value="<?= e(Settings::get('paytr_merchant_key', 'payment')) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Merchant Salt</label>
                            <input type="text" name="s[paytr_merchant_salt]" class="form-control" value="<?= e(Settings::get('paytr_merchant_salt', 'payment')) ?>">
                        </div>
                        <div class="form-group" style="display:flex;align-items:center;margin-top:20px">
                            <label class="toggle-switch">
                                <input type="checkbox" name="s[paytr_test_mode]" <?= Settings::get('paytr_test_mode', 'payment', '1') === '1' ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                            <span style="margin-left:10px;font-weight:600">Test Modu Aktif</span>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <h3>Havale / EFT Bilgileri</h3>
                    <div class="form-group">
                        <label>Banka Listesi (JSON Formatında)</label>
                        <textarea name="s[bank_list]" class="form-control" rows="5" placeholder='[{"bank":"Ziraat","iban":"..."}]'><?= e(json_encode(Settings::get('bank_list', 'payment', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea>
                        <small>Yeni kurumsal yapıda bankalar JSON olarak saklanır.</small>
                    </div>
                </div>

            <?php elseif ($activeTab === 'social'): ?>
                <div class="admin-card">
                    <h3>Sosyal Medya ve İletişim</h3>
                    <div class="form-row">
                        <div class="form-group"><label><i class="fab fa-instagram"></i> Instagram</label><input type="text" name="s[instagram]" class="form-control" value="<?= e(Settings::get('instagram', 'social')) ?>"></div>
                        <div class="form-group"><label><i class="fab fa-facebook"></i> Facebook</label><input type="text" name="s[facebook]" class="form-control" value="<?= e(Settings::get('facebook', 'social')) ?>"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label><i class="fab fa-twitter"></i> Twitter (X)</label><input type="text" name="s[twitter]" class="form-control" value="<?= e(Settings::get('twitter', 'social')) ?>"></div>
                        <div class="form-group"><label><i class="fab fa-whatsapp"></i> WhatsApp</label><input type="text" name="s[whatsapp]" class="form-control" value="<?= e(Settings::get('whatsapp', 'social')) ?>"></div>
                    </div>
                </div>

            <?php elseif ($activeTab === 'theme'): ?>
                <div class="admin-card">
                    <h3>Tema Seçimi</h3>
                    <div class="theme-grid">
                        <?php
                        $themes = [
                            'default' => ['label' => 'Enterprise Blue', 'p' => '#1a56db'],
                            'dark'    => ['label' => 'Midnight Dark', 'p' => '#0f172a'],
                            'premium' => ['label' => 'Gold Premium', 'p' => '#b45309']
                        ];
                        $current = Settings::get('active_theme', 'theme', 'default');
                        foreach ($themes as $tk => $tv):
                        ?>
                            <label class="theme-option <?= $current === $tk ? 'active' : '' ?>">
                                <input type="radio" name="s[active_theme]" value="<?= $tk ?>" <?= $current === $tk ? 'checked' : '' ?>>
                                <div class="preview" style="background:<?= $tv['p'] ?>"></div>
                                <div class="name"><?= $tv['label'] ?></div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="settings-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.settings-container { display: flex; gap: 30px; margin-top: 20px; }
.settings-sidebar { flex: 0 0 250px; display: flex; flex-direction: column; gap: 8px; }
.settings-content { flex: 1; }

.tab-link {
    display: flex; align-items: center; gap: 12px; padding: 14px 20px;
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    color: #4b5563; text-decoration: none; font-weight: 600; transition: all .2s;
}
.tab-link i { font-size: 1.1rem; color: #9ca3af; }
.tab-link:hover { background: #f9fafb; border-color: #d1d5db; }
.tab-link.active { background: var(--admin-primary); color: #fff; border-color: var(--admin-primary); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
.tab-link.active i { color: #fff; }

.theme-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; }
.theme-option { cursor: pointer; border: 2px solid #e5e7eb; border-radius: 16px; padding: 10px; text-align: center; transition: all .2s; }
.theme-option input { display: none; }
.theme-option .preview { height: 80px; border-radius: 10px; margin-bottom: 10px; }
.theme-option .name { font-size: 0.85rem; font-weight: 700; color: #374151; }
.theme-option:hover { border-color: #d1d5db; }
.theme-option.active { border-color: var(--admin-primary); background: #eff6ff; }

.settings-actions { position: sticky; bottom: 20px; background: rgba(255,255,255,0.8); backdrop-filter: blur(10px); padding: 20px; border-radius: 16px; border: 1px solid #e5e7eb; margin-top: 30px; box-shadow: 0 -10px 30px rgba(0,0,0,0.05); }

.toggle-switch { position: relative; display: inline-block; width: 50px; height: 26px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #22c55e; }
input:checked + .slider:before { transform: translateX(24px); }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
