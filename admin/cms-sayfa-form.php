<?php
/**
 * Admin - Kurumsal CMS Sayfa Formu (Ekle / Düzenle)
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

// ===================== BAŞLANGIÇ: CMS YETKİ KONTROLÜ =====================
if (class_exists('Auth') && !Auth::can('manage_cms')) {
    http_response_code(403);
    die('Bu alan için CMS yönetim yetkisi gereklidir.');
}
// ===================== BİTİŞ: CMS YETKİ KONTROLÜ =====================

// ===================== BAŞLANGIÇ: CMS YARDIMCI FONKSİYONLAR =====================
function cms_slug_uret(string $metin): string
{
    $tr = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ç', 'Ç', 'ö', 'Ö', 'ü', 'Ü'];
    $en = ['s', 's', 'i', 'i', 'g', 'g', 'c', 'c', 'o', 'o', 'u', 'u'];
    $metin = str_replace($tr, $en, $metin);
    $metin = strtolower(trim($metin));
    $metin = preg_replace('/[^a-z0-9\s-]/', '', $metin);
    $metin = preg_replace('/[\s-]+/', '-', $metin);
    return trim($metin, '-');
}

function cms_slug_gecerli_mi(string $slug): bool
{
    return preg_match('/^[a-z0-9-]+$/', $slug) === 1;
}

function cms_icerik_temizle(string $icerik): string
{
    $izinli = '<p><a><strong><em><b><i><u><ul><ol><li><h1><h2><h3><h4><blockquote><br><hr><table><thead><tbody><tr><th><td><img>';
    return strip_tags($icerik, $izinli);
}
// ===================== BİTİŞ: CMS YARDIMCI FONKSİYONLAR =====================

$id = intval($_GET['id'] ?? 0);
$duzenleme = $id > 0;

$sayfa = [
    'title' => '',
    'slug' => '',
    'icerik' => '',
    'meta_title' => '',
    'meta_description' => '',
    'canonical_url' => '',
    'sablon' => 'sayfa',
    'durum' => 'taslak',
    'siralama' => 0,
];

if ($duzenleme) {
    $satir = Database::fetch("SELECT * FROM cms_pages WHERE id = ?", [$id]);
    if (!$satir) {
        mesaj('cms', 'Sayfa bulunamadı.', 'error');
        git('/admin/cms-sayfalar.php');
    }
    $sayfa = array_merge($sayfa, $satir);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dogrula_csrf();

    $baslik = trim((string) ($_POST['title'] ?? ''));
    $slug = cms_slug_uret((string) ($_POST['slug'] ?? $baslik));
    $icerik = cms_icerik_temizle((string) ($_POST['icerik'] ?? ''));
    $meta_baslik = trim((string) ($_POST['meta_title'] ?? ''));
    $meta_aciklama = trim((string) ($_POST['meta_description'] ?? ''));
    $canonical_url = trim((string) ($_POST['canonical_url'] ?? ''));
    $sablon = trim((string) ($_POST['sablon'] ?? 'sayfa'));
    $durum = trim((string) ($_POST['durum'] ?? 'taslak'));
    $siralama = intval($_POST['siralama'] ?? 0);

    if ($baslik === '' || $slug === '') {
        mesaj('cms', 'Başlık ve slug zorunludur.', 'error');
    } elseif (!cms_slug_gecerli_mi($slug)) {
        mesaj('cms', 'Slug yalnızca küçük harf, rakam ve tire içerebilir.', 'error');
    } elseif (!in_array($durum, ['taslak', 'yayinda'], true)) {
        mesaj('cms', 'Durum değeri geçersiz.', 'error');
    } elseif ($sablon !== 'sayfa') {
        mesaj('cms', 'Sadece "sayfa" şablonu desteklenmektedir.', 'error');
    } else {
        try {
            if ($duzenleme) {
                Database::query(
                    "UPDATE cms_pages
                     SET title=?, slug=?, icerik=?, meta_title=?, meta_description=?, canonical_url=?, sablon=?, durum=?, siralama=?
                     WHERE id=?",
                    [$baslik, $slug, $icerik, $meta_baslik, $meta_aciklama, $canonical_url, $sablon, $durum, $siralama, $id]
                );

                Database::query(
                    "INSERT INTO cms_page_revisions (page_id, icerik, duzenleyen_user_id) VALUES (?, ?, ?)",
                    [$id, $icerik, intval($_SESSION['user_id'] ?? 0)]
                );

                mesaj('cms', 'Sayfa güncellendi.', 'success');
            } else {
                Database::query(
                    "INSERT INTO cms_pages (title, slug, icerik, meta_title, meta_description, canonical_url, sablon, durum, siralama)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$baslik, $slug, $icerik, $meta_baslik, $meta_aciklama, $canonical_url, $sablon, $durum, $siralama]
                );

                $yeni_id = intval(Database::lastInsertId());
                Database::query(
                    "INSERT INTO cms_page_revisions (page_id, icerik, duzenleyen_user_id) VALUES (?, ?, ?)",
                    [$yeni_id, $icerik, intval($_SESSION['user_id'] ?? 0)]
                );

                mesaj('cms', 'Yeni sayfa oluşturuldu.', 'success');
            }

            git('/admin/cms-sayfalar.php');
        } catch (Throwable $hata) {
            if (strpos($hata->getMessage(), 'Duplicate') !== false) {
                mesaj('cms', 'Bu slug zaten kullanılıyor.', 'error');
            } else {
                mesaj('cms', 'Kayıt sırasında hata oluştu: ' . $hata->getMessage(), 'error');
            }
        }
    }

    $sayfa = [
        'title' => $baslik,
        'slug' => $slug,
        'icerik' => $icerik,
        'meta_title' => $meta_baslik,
        'meta_description' => $meta_aciklama,
        'canonical_url' => $canonical_url,
        'sablon' => $sablon,
        'durum' => $durum,
        'siralama' => $siralama,
    ];
}

$adminPage = 'pages';
$pageTitle = $duzenleme ? 'CMS Sayfa Düzenle' : 'CMS Sayfa Ekle';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
    <div>
        <h1><i class="fas fa-file-signature" style="color:var(--admin-primary)"></i> <?= $duzenleme ? 'CMS Sayfa Düzenle' : 'Yeni CMS Sayfa' ?></h1>
    </div>
    <a href="<?= BASE_URL ?>/admin/cms-sayfalar.php" class="admin-btn admin-btn-secondary">Listeye Dön</a>
</div>

<?php mesaj_goster('cms'); ?>

<div class="admin-card" style="padding:18px;">
    <form method="post">
        <?= csrf_kod() ?>

        <div class="form-group" style="margin-bottom:12px;">
            <label>Başlık</label>
            <input type="text" name="title" class="form-control" required value="<?= temiz($sayfa['title']) ?>">
        </div>

        <div class="form-group" style="margin-bottom:12px;">
            <label>Slug</label>
            <input type="text" name="slug" class="form-control" required value="<?= temiz($sayfa['slug']) ?>" placeholder="ornek-sayfa">
        </div>

        <div class="form-group" style="margin-bottom:12px;">
            <label>İçerik</label>
            <textarea name="icerik" class="form-control" rows="14"><?= temiz($sayfa['icerik']) ?></textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
                <label>Meta Başlık</label>
                <input type="text" name="meta_title" class="form-control" value="<?= temiz($sayfa['meta_title']) ?>">
            </div>
            <div class="form-group">
                <label>Canonical URL</label>
                <input type="url" name="canonical_url" class="form-control" value="<?= temiz($sayfa['canonical_url']) ?>" placeholder="https://site.com/sayfa/ornek">
            </div>
        </div>

        <div class="form-group" style="margin-top:12px;">
            <label>Meta Açıklama</label>
            <textarea name="meta_description" class="form-control" rows="3"><?= temiz($sayfa['meta_description']) ?></textarea>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px; margin-top:12px;">
            <div class="form-group">
                <label>Şablon</label>
                <select name="sablon" class="form-control">
                    <option value="sayfa" <?= $sayfa['sablon'] === 'sayfa' ? 'selected' : '' ?>>sayfa</option>
                </select>
            </div>
            <div class="form-group">
                <label>Durum</label>
                <select name="durum" class="form-control">
                    <option value="taslak" <?= $sayfa['durum'] === 'taslak' ? 'selected' : '' ?>>Taslak</option>
                    <option value="yayinda" <?= $sayfa['durum'] === 'yayinda' ? 'selected' : '' ?>>Yayında</option>
                </select>
            </div>
            <div class="form-group">
                <label>Sıralama</label>
                <input type="number" name="siralama" class="form-control" value="<?= (int) $sayfa['siralama'] ?>">
            </div>
        </div>

        <div style="margin-top:16px; display:flex; gap:10px;">
            <button type="submit" class="admin-btn admin-btn-primary">Kaydet</button>
            <?php if ($duzenleme): ?>
                <a href="<?= BASE_URL ?>/admin/cms-sayfa-onizleme.php?id=<?= (int) $id ?>" target="_blank" class="admin-btn admin-btn-secondary">Önizleme</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
