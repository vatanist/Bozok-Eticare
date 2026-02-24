<?php
/**
 * Partial: Sayfa formu — hem modal (yeni) hem inline (düzenle) için
 * $editPage dışarıdan inject edilir (null veya array).
 */
$_ep = $editPage ?? [];
function _pv(string $key, string $default = ''): string {
    global $_ep;
    return htmlspecialchars((string)($_ep[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}
?>
<div class="form-group">
    <label>Sayfa Başlığı *</label>
    <input type="text" name="title" id="newTitle" class="form-control"
        value="<?= _pv('title') ?>" required placeholder="Örn: Hakkımızda">
</div>

<div class="form-group">
    <label>URL (Slug) *
        <small style="color:var(--admin-gray);font-weight:400"> — örn: hakkimizda → /hakkimizda</small>
    </label>
    <input type="text" name="slug" id="newSlug" class="form-control"
        value="<?= _pv('slug') ?>" required
        pattern="[a-z0-9\-]+" title="Sadece küçük harf, rakam ve tire">
</div>

<div class="form-row">
    <div class="form-group">
        <label>Meta Başlık <small style="color:var(--admin-gray)">(boş=sayfa başlığı)</small></label>
        <input type="text" name="meta_title" class="form-control"
            value="<?= _pv('meta_title') ?>" placeholder="Tarayıcı sekmesi başlığı">
    </div>
    <div class="form-group">
        <label>Sıra</label>
        <input type="number" name="sort_order" class="form-control"
            value="<?= _pv('sort_order', '0') ?>" min="0" style="max-width:100px">
    </div>
</div>

<div class="form-group">
    <label>Meta Açıklama <small style="color:var(--admin-gray)">(max 160 karakter)</small></label>
    <textarea name="meta_description" class="form-control" rows="2" maxlength="160"
        placeholder="Google arama sonuçlarında görünecek açıklama"><?= _pv('meta_description') ?></textarea>
</div>

<div class="form-group">
    <label>Sayfa İçeriği</label>
    <textarea name="content" class="page-editor form-control"><?= _pv('content') ?></textarea>
</div>

<div class="form-row" style="gap:20px;align-items:center">
    <div class="form-group" style="margin:0">
        <label>Durum</label>
        <select name="status" class="form-control">
            <option value="1" <?= ($_ep['status'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= ($_ep['status'] ?? 1) == 0 ? 'selected' : '' ?>>Pasif</option>
        </select>
    </div>
    <div class="form-group" style="margin:0;padding-top:24px">
        <label style="display:flex;align-items:center;gap:8px;font-weight:400;cursor:pointer">
            <input type="checkbox" name="show_in_footer"
                <?= ($_ep['show_in_footer'] ?? 1) ? 'checked' : '' ?>>
            Footer'da göster
        </label>
    </div>
</div>
