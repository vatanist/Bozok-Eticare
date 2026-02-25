<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Modül Merkezi';
$adminPage = 'extensions';

// ===================== BAŞLANGIÇ: MODÜL YETKİ KONTROLÜ =====================
if (class_exists('Auth') && !Auth::can('manage_extensions')) {
    http_response_code(403);
    die('Bu işlem için modül yönetim yetkisi gereklidir.');
}
// ===================== BİTİŞ: MODÜL YETKİ KONTROLÜ =====================

// ===================== BAŞLANGIÇ: YARDIMCI FONKSİYONLAR =====================
function modul_kimligi_uret(array $modul): string
{
    return ($modul['type'] ?? 'module') . ':' . ($modul['code'] ?? '');
}

function modul_kimligini_coz(string $kimlik): array
{
    $parcalar = explode(':', $kimlik, 2);
    if (count($parcalar) !== 2) {
        return ['', ''];
    }
    return [$parcalar[0], $parcalar[1]];
}


function modul_tipi_gecerli_mi(string $tip): bool
{
    return in_array($tip, ['module', 'payment', 'shipping', 'marketing'], true);
}

function modul_kodu_gecerli_mi(string $kod): bool
{
    return preg_match('#^[a-zA-Z0-9_-]+$#', $kod) === 1;
}

function modul_ayar_alani_dogrula(array $alan, $deger)
{
    $tip = $alan['type'] ?? 'string';

    if ($tip === 'bool') {
        return in_array((string) $deger, ['1', 'true', 'on'], true);
    }
    if ($tip === 'int') {
        return intval($deger);
    }
    if ($tip === 'float') {
        return floatval($deger);
    }
    if ($tip === 'json') {
        $cozulmus = json_decode((string) $deger, true);
        return is_array($cozulmus) ? $cozulmus : [];
    }

    if ($tip === 'select' && isset($alan['choices']) && is_array($alan['choices'])) {
        return array_key_exists((string) $deger, $alan['choices']) ? (string) $deger : ($alan['default'] ?? '');
    }

    return trim((string) $deger);
}
// ===================== BİTİŞ: YARDIMCI FONKSİYONLAR =====================

// ===================== BAŞLANGIÇ: POST İŞLEMLERİ =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dogrula_csrf();

    $aksiyon = $_POST['aksiyon'] ?? '';

    if ($aksiyon === 'durum_degistir') {
        $kod = trim((string) ($_POST['kod'] ?? ''));
        $tip = trim((string) ($_POST['tip'] ?? 'module'));
        $durum = intval($_POST['durum'] ?? 0) ? 1 : 0;

        if (!modul_tipi_gecerli_mi($tip) || !modul_kodu_gecerli_mi($kod)) {
            mesaj('moduller', 'Geçersiz modül tipi veya kodu.', 'error');
            git('/admin/moduller.php?sekme=kurulu');
        }

        Database::query("UPDATE extensions SET status = ? WHERE code = ? AND type = ?", [$durum, $kod, $tip]);
        mesaj('moduller', 'Modül durumu güncellendi.', 'success');
        git('/admin/moduller.php?sekme=kurulu');
    }

    if ($aksiyon === 'kur') {
        $kod = trim((string) ($_POST['kod'] ?? ''));
        $tip = trim((string) ($_POST['tip'] ?? 'module'));

        if (!modul_tipi_gecerli_mi($tip) || !modul_kodu_gecerli_mi($kod)) {
            mesaj('moduller', 'Geçersiz modül tipi veya kodu.', 'error');
            git('/admin/moduller.php?sekme=kurulabilir');
        }

        Database::query(
            "INSERT INTO extensions (type, code, status, sort_order) VALUES (?, ?, 0, 0)
             ON DUPLICATE KEY UPDATE type = VALUES(type)",
            [$tip, $kod]
        );

        mesaj('moduller', 'Modül sisteme eklendi. Durumunu Kurulu sekmesinden açabilirsiniz.', 'success');
        git('/admin/moduller.php?sekme=kurulabilir');
    }

    if ($aksiyon === 'ayar_kaydet') {
        $modul_kimligi = trim((string) ($_POST['modul_kimligi'] ?? ''));
        [$tip, $kod] = modul_kimligini_coz($modul_kimligi);

        if (!modul_tipi_gecerli_mi($tip) || !modul_kodu_gecerli_mi($kod)) {
            mesaj('moduller', 'Ayar kaydı için modül bilgisi geçersiz.', 'error');
            git('/admin/moduller.php?sekme=ayarlar');
        }

        $kesif = class_exists('ModulSozlesmesi') ? ModulSozlesmesi::modulleriKesfet($bozkurt['modul_yolu']) : [];
        $hedef_modul = null;
        foreach ($kesif as $modul) {
            if (($modul['type'] ?? '') === $tip && ($modul['code'] ?? '') === $kod) {
                $hedef_modul = $modul;
                break;
            }
        }

        if (!$hedef_modul) {
            mesaj('moduller', 'Modül ayar şeması bulunamadı.', 'error');
            git('/admin/moduller.php?sekme=ayarlar');
        }

        $alanlar = $hedef_modul['settings_schema'] ?? [];
        $grup = 'modul_' . $tip . '_' . $kod;

        foreach ($alanlar as $alan) {
            $anahtar = (string) ($alan['key'] ?? '');
            if ($anahtar === '') {
                continue;
            }
            $ham = $_POST['ayarlar'][$anahtar] ?? ($alan['default'] ?? '');
            $deger = modul_ayar_alani_dogrula($alan, $ham);
            option_set($anahtar, $deger, $grup);
        }

        mesaj('moduller', 'Modül ayarları kaydedildi.', 'success');
        git('/admin/moduller.php?sekme=ayarlar&modul=' . urlencode($modul_kimligi));
    }
}
// ===================== BİTİŞ: POST İŞLEMLERİ =====================

$sekme = trim((string) ($_GET['sekme'] ?? 'kurulu'));
$izinli_sekmeler = ['kurulu', 'kurulabilir', 'eksik', 'ayarlar'];
if (!in_array($sekme, $izinli_sekmeler, true)) {
    $sekme = 'kurulu';
}

// ===================== BAŞLANGIÇ: MODÜL KEŞİF + DB DURUM EŞLEME =====================
$kesfedilen_moduller = class_exists('ModulSozlesmesi') ? ModulSozlesmesi::modulleriKesfet($bozkurt['modul_yolu']) : [];
$db_uzantilari = Database::fetchAll("SELECT * FROM extensions WHERE type IN ('module','payment','shipping','marketing') ORDER BY type, code");

$db_harita = [];
foreach ($db_uzantilari as $satir) {
    $db_harita[$satir['type'] . ':' . $satir['code']] = $satir;
}

$kurulu = [];
$kurulabilir = [];
$eksik = [];

foreach ($kesfedilen_moduller as $modul) {
    $kimlik = modul_kimligi_uret($modul);
    $modul['kimlik'] = $kimlik;
    $modul['db'] = $db_harita[$kimlik] ?? null;

    if (!empty($modul['uyarilar'])) {
        $eksik[] = $modul;
    }

    if ($modul['db']) {
        $kurulu[] = $modul;
    } else {
        $kurulabilir[] = $modul;
    }
}

foreach ($db_uzantilari as $satir) {
    $kimlik = $satir['type'] . ':' . $satir['code'];
    $bulundu = false;
    foreach ($kesfedilen_moduller as $modul) {
        if (($modul['type'] ?? '') . ':' . ($modul['code'] ?? '') === $kimlik) {
            $bulundu = true;
            break;
        }
    }

    if (!$bulundu) {
        $eksik[] = [
            'kimlik' => $kimlik,
            'type' => $satir['type'],
            'code' => $satir['code'],
            'name' => strtoupper($satir['code']),
            'version' => '-',
            'author' => '-',
            'description' => 'Veritabanında kayıt var ancak modül klasörü bulunamadı.',
            'metadata_kaynagi' => 'db',
            'uyarilar' => ['Modül dosyaları eksik veya taşınmış.'],
            'db' => $satir,
        ];
    }
}
// ===================== BİTİŞ: MODÜL KEŞİF + DB DURUM EŞLEME =====================

$secilen_modul_kimligi = trim((string) ($_GET['modul'] ?? ''));
$secilen_modul = null;
foreach ($kurulu as $modul) {
    if (($modul['kimlik'] ?? '') === $secilen_modul_kimligi) {
        $secilen_modul = $modul;
        break;
    }
}
if (!$secilen_modul && !empty($kurulu)) {
    $secilen_modul = $kurulu[0];
    $secilen_modul_kimligi = $secilen_modul['kimlik'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header" style="margin-bottom:16px;">
    <h1><i class="fas fa-puzzle-piece" style="color:var(--admin-primary)"></i> Modül Merkezi</h1>
    <p style="color:var(--admin-gray); margin-top:6px;">Tek merkezden modül keşfi, kurulum, doğrulama ve ayar yönetimi.</p>
</div>

<?php mesaj_goster('moduller'); ?>

<div class="admin-card" style="padding:18px; margin-bottom:16px;">
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a class="admin-btn <?= $sekme === 'kurulu' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>" href="<?= BASE_URL ?>/admin/moduller.php?sekme=kurulu">Kurulu</a>
        <a class="admin-btn <?= $sekme === 'kurulabilir' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>" href="<?= BASE_URL ?>/admin/moduller.php?sekme=kurulabilir">Kurulabilir</a>
        <a class="admin-btn <?= $sekme === 'eksik' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>" href="<?= BASE_URL ?>/admin/moduller.php?sekme=eksik">Eksik / Uyarı</a>
        <a class="admin-btn <?= $sekme === 'ayarlar' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>" href="<?= BASE_URL ?>/admin/moduller.php?sekme=ayarlar">Ayarlar</a>
    </div>
</div>

<?php if ($sekme === 'kurulu'): ?>
    <div class="admin-card" style="padding:0;">
        <div class="card-header" style="padding:15px; border-bottom:1px solid #eee; background:#f8f9fa; font-weight:600;">Kurulu Modüller</div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Modül</th>
                    <th>Tip</th>
                    <th>Sürüm</th>
                    <th>Durum</th>
                    <th style="text-align:right; width:120px;">İşlem</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($kurulu)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--admin-gray)">Kurulu modül bulunamadı.</td></tr>
                <?php endif; ?>
                <?php foreach ($kurulu as $modul): ?>
                    <?php $aktif = intval($modul['db']['status'] ?? 0) === 1; ?>
                    <tr>
                        <td>
                            <strong><?= temiz($modul['name']) ?></strong>
                            <div style="font-size:12px; color:var(--admin-gray)"><?= temiz($modul['code']) ?></div>
                        </td>
                        <td><?= temiz($modul['type']) ?></td>
                        <td><?= temiz($modul['version']) ?></td>
                        <td><span class="badge badge-<?= $aktif ? 'success' : 'secondary' ?>"><?= $aktif ? 'Aktif' : 'Pasif' ?></span></td>
                        <td style="text-align:right;">
                            <form method="post" style="display:inline;">
                                <?= csrf_kod() ?>
                                <input type="hidden" name="aksiyon" value="durum_degistir">
                                <input type="hidden" name="kod" value="<?= temiz($modul['code']) ?>">
                                <input type="hidden" name="tip" value="<?= temiz($modul['type']) ?>">
                                <input type="hidden" name="durum" value="<?= $aktif ? 0 : 1 ?>">
                                <button class="admin-btn admin-btn-sm <?= $aktif ? 'admin-btn-secondary' : 'admin-btn-primary' ?>" type="submit">
                                    <?= $aktif ? 'Pasifleştir' : 'Aktifleştir' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($sekme === 'kurulabilir'): ?>
    <div class="admin-card" style="padding:0;">
        <div class="card-header" style="padding:15px; border-bottom:1px solid #eee; background:#f8f9fa; font-weight:600;">Kurulabilir Modüller</div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Modül</th>
                    <th>Tip</th>
                    <th>Kaynak</th>
                    <th>Uyarı</th>
                    <th style="text-align:right; width:120px;">İşlem</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($kurulabilir)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--admin-gray)">Kurulabilir yeni modül yok.</td></tr>
                <?php endif; ?>
                <?php foreach ($kurulabilir as $modul): ?>
                    <tr>
                        <td>
                            <strong><?= temiz($modul['name']) ?></strong>
                            <div style="font-size:12px; color:var(--admin-gray)"><?= temiz($modul['description']) ?></div>
                        </td>
                        <td><?= temiz($modul['type']) ?></td>
                        <td><?= temiz($modul['metadata_kaynagi']) ?></td>
                        <td style="font-size:12px; color:var(--admin-gray)">
                            <?= !empty($modul['uyarilar']) ? temiz(implode(' | ', $modul['uyarilar'])) : '-' ?>
                        </td>
                        <td style="text-align:right;">
                            <form method="post" style="display:inline;">
                                <?= csrf_kod() ?>
                                <input type="hidden" name="aksiyon" value="kur">
                                <input type="hidden" name="kod" value="<?= temiz($modul['code']) ?>">
                                <input type="hidden" name="tip" value="<?= temiz($modul['type']) ?>">
                                <button class="admin-btn admin-btn-sm admin-btn-primary" type="submit">Sisteme Ekle</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($sekme === 'eksik'): ?>
    <div class="admin-card" style="padding:0;">
        <div class="card-header" style="padding:15px; border-bottom:1px solid #eee; background:#fff4e5; font-weight:600;">Eksik / Uyarılı Modüller (Fail-Open)</div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Modül</th>
                    <th>Tip</th>
                    <th>Kaynak</th>
                    <th>Uyarılar</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($eksik)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--admin-gray)">Eksik modül uyarısı yok.</td></tr>
                <?php endif; ?>
                <?php foreach ($eksik as $modul): ?>
                    <tr>
                        <td><strong><?= temiz($modul['name']) ?></strong><div style="font-size:12px; color:var(--admin-gray)"><?= temiz($modul['code']) ?></div></td>
                        <td><?= temiz($modul['type']) ?></td>
                        <td><?= temiz($modul['metadata_kaynagi'] ?? 'bilinmiyor') ?></td>
                        <td style="font-size:12px; color:#b45309;"><?= temiz(implode(' | ', $modul['uyarilar'] ?? ['Bilinmeyen uyarı'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($sekme === 'ayarlar'): ?>
    <div class="admin-card" style="padding:18px; margin-bottom:16px;">
        <form method="get" action="<?= BASE_URL ?>/admin/moduller.php" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
            <input type="hidden" name="sekme" value="ayarlar">
            <div>
                <label style="display:block; margin-bottom:6px; font-weight:600;">Modül seç</label>
                <select name="modul" class="form-control" style="min-width:280px;">
                    <?php foreach ($kurulu as $modul): ?>
                        <?php $kimlik = $modul['kimlik']; ?>
                        <option value="<?= temiz($kimlik) ?>" <?= $secilen_modul_kimligi === $kimlik ? 'selected' : '' ?>>
                            <?= temiz($modul['name']) ?> (<?= temiz($modul['type']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="admin-btn admin-btn-primary">Yükle</button>
        </form>
    </div>

    <div class="admin-card" style="padding:18px;">
        <?php if (!$secilen_modul): ?>
            <p style="color:var(--admin-gray)">Ayarlar için kurulu modül bulunamadı.</p>
        <?php else: ?>
            <?php
            $alanlar = $secilen_modul['settings_schema'] ?? [];
            $grup = 'modul_' . $secilen_modul['type'] . '_' . $secilen_modul['code'];
            ?>
            <h3 style="margin-bottom:12px;"><?= temiz($secilen_modul['name']) ?> - Ayarlar</h3>
            <?php if (empty($alanlar)): ?>
                <p style="color:var(--admin-gray)">Bu modül için settings_schema tanımlı değil.</p>
            <?php else: ?>
                <form method="post">
                    <?= csrf_kod() ?>
                    <input type="hidden" name="aksiyon" value="ayar_kaydet">
                    <input type="hidden" name="modul_kimligi" value="<?= temiz($secilen_modul_kimligi) ?>">

                    <?php foreach ($alanlar as $alan): ?>
                        <?php
                        $anahtar = (string) ($alan['key'] ?? '');
                        if ($anahtar === '') {
                            continue;
                        }
                        $etiket = (string) ($alan['label'] ?? $anahtar);
                        $tip = (string) ($alan['type'] ?? 'string');
                        $varsayilan = $alan['default'] ?? '';
                        $mevcut = option_get($anahtar, $varsayilan, $grup);
                        ?>
                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px;"><?= temiz($etiket) ?></label>
                            <?php if ($tip === 'bool'): ?>
                                <select name="ayarlar[<?= temiz($anahtar) ?>]" class="form-control" style="max-width:260px;">
                                    <option value="1" <?= (string) $mevcut === '1' || $mevcut === true ? 'selected' : '' ?>>Açık</option>
                                    <option value="0" <?= (string) $mevcut === '0' || $mevcut === false ? 'selected' : '' ?>>Kapalı</option>
                                </select>
                            <?php elseif ($tip === 'json'): ?>
                                <textarea name="ayarlar[<?= temiz($anahtar) ?>]" class="form-control" rows="5" style="max-width:760px;"><?= temiz(is_array($mevcut) ? json_encode($mevcut, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : (string) $mevcut) ?></textarea>
                            <?php elseif ($tip === 'select' && isset($alan['choices']) && is_array($alan['choices'])): ?>
                                <select name="ayarlar[<?= temiz($anahtar) ?>]" class="form-control" style="max-width:320px;">
                                    <?php foreach ($alan['choices'] as $secenek_degeri => $secenek_etiketi): ?>
                                        <option value="<?= temiz($secenek_degeri) ?>" <?= (string) $mevcut === (string) $secenek_degeri ? 'selected' : '' ?>><?= temiz($secenek_etiketi) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" name="ayarlar[<?= temiz($anahtar) ?>]" class="form-control" style="max-width:480px;" value="<?= temiz((string) $mevcut) ?>">
                            <?php endif; ?>
                            <?php if (!empty($alan['help'])): ?>
                                <small style="color:var(--admin-gray)"><?= temiz((string) $alan['help']) ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="admin-btn admin-btn-primary">Ayarları Kaydet</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
