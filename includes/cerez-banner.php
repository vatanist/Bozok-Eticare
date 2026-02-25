<?php
$__cerez_tercih = class_exists('CerezYonetimi') ? CerezYonetimi::tercihleriOku() : ['karar' => 'bekleniyor'];
$__cerez_banner_goster = class_exists('CerezYonetimi') ? CerezYonetimi::bannerGosterilsinMi() : false;
$__cerez_donus = $_SERVER['REQUEST_URI'] ?? '/';
?>

<?php if ($__cerez_banner_goster): ?>
    <div id="cerez-banner" style="position:fixed;left:20px;right:20px;bottom:20px;z-index:9999;background:#111827;color:#f9fafb;border-radius:14px;padding:18px;box-shadow:0 10px 30px rgba(0,0,0,.35);">
        <form action="<?= url('cerez/tercih') ?>" method="POST" id="cerez-tercih-form">
            <?= csrf_kod() ?>
            <input type="hidden" name="donus" value="<?= e($__cerez_donus) ?>">

            <div style="display:flex;gap:12px;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;">
                <div style="max-width:780px;">
                    <strong style="font-size:16px;display:block;margin-bottom:8px;">Çerez Tercihleriniz</strong>
                    <p style="margin:0 0 10px 0;color:#d1d5db;line-height:1.5;">
                        Zorunlu çerezler her zaman aktiftir. Analitik ve pazarlama çerezlerini kabul edebilir,
                        reddedebilir veya özelleştirebilirsiniz.
                    </p>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:13px;color:#e5e7eb;">
                        <label><input type="checkbox" checked disabled> Zorunlu</label>
                        <label><input type="checkbox" name="analitik" value="1"> Analitik</label>
                        <label><input type="checkbox" name="pazarlama" value="1"> Pazarlama</label>
                        <label><input type="checkbox" name="tercih" value="1"> Tercih</label>
                    </div>
                </div>

                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="submit" name="aksiyon" value="reddet" style="border:1px solid #ef4444;background:transparent;color:#fca5a5;padding:10px 14px;border-radius:8px;cursor:pointer;">Reddet</button>
                    <button type="submit" name="aksiyon" value="tercih_kaydet" style="border:1px solid #9ca3af;background:#1f2937;color:#fff;padding:10px 14px;border-radius:8px;cursor:pointer;">Tercihi Kaydet</button>
                    <button type="submit" name="aksiyon" value="kabul" style="border:1px solid #22c55e;background:#22c55e;color:#052e16;padding:10px 14px;border-radius:8px;cursor:pointer;font-weight:700;">Tümünü Kabul Et</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>
