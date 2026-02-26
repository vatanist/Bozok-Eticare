<?php
/**
 * Admin — CMS Sayfa Yönetimi
 * Hukuki ve bilgi sayfaları ekle/düzenle/sil
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

// ── POST İŞLEMLERİ ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    // Slug oluştur (UTF-8 Türkçe → ASCII)
    function makeSlug(string $text): string
    {
        $tr = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ç', 'Ç', 'ö', 'Ö', 'ü', 'Ü'];
        $en = ['s', 's', 'i', 'i', 'g', 'g', 'c', 'c', 'o', 'o', 'u', 'u'];
        $text = str_replace($tr, $en, $text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s\-]/', '', $text);
        $text = preg_replace('/[\s\-]+/', '-', trim($text));
        return $text;
    }

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $slug = !empty($_POST['slug']) ? makeSlug(trim($_POST['slug'])) : makeSlug($title);
        $content = $_POST['content'] ?? '';
        $metaTitle = trim($_POST['meta_title'] ?? '') ?: $title;
        $metaDesc = trim($_POST['meta_description'] ?? '');
        $status = intval($_POST['status'] ?? 1);
        $inFooter = isset($_POST['show_in_footer']) ? 1 : 0;
        $sortOrder = intval($_POST['sort_order'] ?? 0);

        if (!$title || !$slug) {
            flash('pages', 'Başlık ve slug zorunludur.', 'error');
        } else {
            try {
                Database::query(
                    "INSERT INTO pages (title, slug, content, meta_title, meta_description, status, show_in_footer, sort_order, is_system)
                     VALUES (?,?,?,?,?,?,?,?,0)",
                    [$title, $slug, $content, $metaTitle, $metaDesc, $status, $inFooter, $sortOrder]
                );
                flash('pages', '"' . e($title) . '" sayfası oluşturuldu.', 'success');
            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), 'Duplicate') !== false) {
                    flash('pages', 'Bu slug zaten kullanılıyor. Farklı bir URL seçin.', 'error');
                } else {
                    flash('pages', 'Hata: ' . $ex->getMessage(), 'error');
                }
            }
        }
    } elseif ($action === 'update') {
        $id = intval($_POST['id']);
        $title = trim($_POST['title'] ?? '');
        $slug = makeSlug(trim($_POST['slug'] ?? ''));
        $content = $_POST['content'] ?? '';
        $metaTitle = trim($_POST['meta_title'] ?? '') ?: $title;
        $metaDesc = trim($_POST['meta_description'] ?? '');
        $status = intval($_POST['status'] ?? 1);
        $inFooter = isset($_POST['show_in_footer']) ? 1 : 0;
        $sortOrder = intval($_POST['sort_order'] ?? 0);

        if (!$id || !$title || !$slug) {
            flash('pages', 'Başlık ve slug zorunludur.', 'error');
        } else {
            try {
                Database::query(
                    "UPDATE pages SET title=?, slug=?, content=?, meta_title=?, meta_description=?,
                     status=?, show_in_footer=?, sort_order=? WHERE id=?",
                    [$title, $slug, $content, $metaTitle, $metaDesc, $status, $inFooter, $sortOrder, $id]
                );
                flash('pages', '"' . e($title) . '" güncellendi.', 'success');
            } catch (Exception $ex) {
                flash('pages', 'Hata: ' . $ex->getMessage(), 'error');
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $chk = Database::fetch("SELECT is_system, title FROM pages WHERE id=?", [$id]);
        if (!$chk) {
            flash('pages', 'Sayfa bulunamadı.', 'error');
        } elseif ($chk['is_system']) {
            flash('pages', '"' . e($chk['title']) . '" sistem sayfasıdır, silinemez.', 'error');
        } else {
            Database::query("DELETE FROM pages WHERE id=?", [$id]);
            flash('pages', '"' . e($chk['title']) . '" silindi.', 'success');
        }
    }

    redirect('/admin/pages.php');
}

// ── VERİ ÇEK ───────────────────────────────────────────────────
$pages = Database::fetchAll("SELECT * FROM pages ORDER BY sort_order, id");

// Düzenleme modunda mı?
$editPage = null;
if (!empty($_GET['edit'])) {
    $editPage = Database::fetch("SELECT * FROM pages WHERE id=?", [intval($_GET['edit'])]);
}

$adminPage = 'pages';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1><i class="fas fa-file-alt" style="color:var(--admin-primary)"></i> Sayfa Yönetimi</h1>
        <small style="color:var(--admin-gray)">CMS sayfaları yönet &mdash; hukuki ve kurumsal sayfalar</small>
    </div>
    <button onclick="document.getElementById('createModal').classList.add('active')"
        class="admin-btn admin-btn-primary">
        <i class="fas fa-plus"></i> Yeni Sayfa
    </button>
</div>

<?php showFlash('pages'); ?>

<!-- Sayfa Listesi -->
<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Başlık</th>
                <th>URL (Slug)</th>
                <th>Footer</th>
                <th>Sıra</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pages as $p): ?>
                <tr>
                    <td>
                        <strong>
                            <?= e($p['title']) ?>
                        </strong>
                        <?php if ($p['is_system']): ?>
                            <span class="admin-badge"
                                style="font-size:.65rem;background:#e0f2fe;color:#0369a1;margin-left:4px">sistem</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/<?= e($p['slug']) ?>" target="_blank"
                            style="color:var(--admin-primary);font-size:.85rem">
                            /
                            <?= e($p['slug']) ?> <i class="fas fa-external-link-alt" style="font-size:.7rem"></i>
                        </a>
                    </td>
                    <td>
                        <?php if ($p['show_in_footer']): ?>
                            <span style="color:#22c55e"><i class="fas fa-check-circle"></i></span>
                        <?php else: ?>
                            <span style="color:#d1d5db"><i class="fas fa-minus-circle"></i></span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--admin-gray)">
                        <?= $p['sort_order'] ?>
                    </td>
                    <td>
                        <?php if ($p['status']): ?>
                            <span class="admin-badge admin-badge-success">Aktif</span>
                        <?php else: ?>
                            <span class="admin-badge" style="background:#f1f5f9;color:#6b7280">Pasif</span>
                        <?php endif; ?>
                    </td>
                    <td style="display:flex;gap:6px">
                        <a href="?edit=<?= $p['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">
                            <i class="fas fa-pen"></i> Düzenle
                        </a>
                        <?php if (!$p['is_system']): ?>
                            <form method="POST" onsubmit="return confirm('\" <?= e(addslashes($p['title'])) ?>\" silinsin mi?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button class="admin-btn admin-btn-danger admin-btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="admin-btn admin-btn-sm" style="opacity:.4;cursor:not-allowed"
                                title="Sistem sayfası silinemez">
                                <i class="fas fa-lock"></i>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($pages)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:var(--admin-gray)">
                        Henüz sayfa yok. <a href="/admin/migrations/run-pages.php">Migration'ı çalıştır</a>.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ── DÜZENLEME FORMU (inline, edit modunda) ─────────────────── -->
<?php if ($editPage): ?>
    <div class="admin-card" style="margin-top:24px">
        <h3 style="margin-bottom:16px">
            <i class="fas fa-pen" style="color:var(--admin-primary)"></i>
            Düzenle: <em>
                <?= e($editPage['title']) ?>
            </em>
        </h3>
        <form method="POST" class="admin-form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editPage['id'] ?>">
            <?php include __DIR__ . '/includes/_page-form.php'; ?>
            <div style="display:flex;gap:10px;margin-top:16px">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <a href="pages.php" class="admin-btn admin-btn-outline">İptal</a>
                <a href="<?= BASE_URL ?>/<?= e($editPage['slug']) ?>" target="_blank" class="admin-btn admin-btn-outline">
                    <i class="fas fa-eye"></i> Önizle
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- ── YENİ SAYFA MODAL ───────────────────────────────────────── -->
<div id="createModal" class="admin-modal-bg" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="admin-modal" style="max-width:720px">
        <div class="admin-modal-header">
            <h3><i class="fas fa-plus"></i> Yeni Sayfa Oluştur</h3>
            <button class="admin-modal-close"
                onclick="document.getElementById('createModal').classList.remove('active')">&times;</button>
        </div>
        <form method="POST" class="admin-form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">
            <div class="admin-modal-body">
                <?php
                // Modal içinde boş editPage ile form partial'ını kullan
                $editPage = null;
                include __DIR__ . '/includes/_page-form.php';
                ?>
            </div>
            <div class="admin-modal-footer">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="fas fa-save"></i> Oluştur
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summernote editör -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-tr-TR.min.js"></script>
<script>
    $(document).ready(function () {
        const summerOpts = {
            lang: 'tr-TR',
            height: 320,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'table', 'hr']],
                ['view', ['codeview', 'fullscreen']]
            ],
            callbacks: {
                onInit: function () {
                    // Modal içi tutarlı yükseklik
                    $(this).next('.note-editor').find('.note-editable').css('min-height', '280px');
                }
            }
        };
        $('.page-editor').summernote(summerOpts);

        // Başlık → slug otomatik doldur (sadece boşken)
        $('#newTitle').on('input', function () {
            const slugEl = $('#newSlug');
            if (!slugEl.data('manual')) {
                const slug = $(this).val()
                    .toLowerCase()
                    .replace(/[şŞ]/g, 's').replace(/[ıİ]/g, 'i')
                    .replace(/[ğĞ]/g, 'g').replace(/[çÇ]/g, 'c')
                    .replace(/[öÖ]/g, 'o').replace(/[üÜ]/g, 'u')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/[\s]+/g, '-').trim();
                slugEl.val(slug);
            }
        });
        $('#newSlug').on('input', function () { $(this).data('manual', true); });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
