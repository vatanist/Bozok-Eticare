<?php gorunum('ust', ['sayfa_basligi' => $kategori['name']]); ?>

<div class="shoptimizer-category-header"
    style="background: #f8fafc; border-radius: 30px; padding: 60px; text-align: center; margin-bottom: 50px; position: relative; overflow: hidden;">
    <div style="position: relative; z-index: 2;">
        <nav style="font-size: 13px; color: var(--gray); margin-bottom: 15px;">
            <a href="<?= BASE_URL ?>" style="color: inherit; text-decoration: none;">Anasayfa</a> /
            <span>
                <?= temiz($kategori['name']) ?>
            </span>
        </nav>
        <h1 style="font-size: 3.5rem; font-weight: 900; color: var(--dark); margin: 0; letter-spacing: -2px;">
            <?= temiz($kategori['name']) ?>
        </h1>
        <p
            style="color: var(--gray); font-size: 1.1rem; margin-top: 15px; max-width: 600px; margin-left: auto; margin-right: auto;">
            <?= $kategori['description'] ?: 'Bu kategoride en seçkin ürünleri sizler için bir araya getirdik.' ?>
        </p>
    </div>
    <div
        style="position: absolute; right: -50px; top: -50px; width: 300px; height: 300px; background: var(--primary); border-radius: 50%; opacity: 0.05;">
    </div>
</div>

<div class="shoptimizer-category-layout" style="display: grid; grid-template-columns: 280px 1fr; gap: 50px;">

    <!-- Sidebar -->
    <aside class="shoptimizer-sidebar">
        <div style="margin-bottom: 40px;">
            <h4
                style="font-weight: 900; color: var(--dark); margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; font-size: 1.2rem;">
                Kategoriler</h4>
            <ul style="list-style: none; padding: 0; margin: 0; display: grid; gap: 12px;">
                <?php foreach (kategorileri_getir() as $kat): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/kategori.php?slug=<?= link_yap($kat['name']) ?>"
                            style="color: <?= ($kat['id'] == $kategori['id']) ? 'var(--primary)' : 'var(--gray)' ?>; text-decoration: none; font-weight: <?= ($kat['id'] == $kategori['id']) ? '800' : '600' ?>; font-size: 0.95rem; border-left: 3px solid <?= ($kat['id'] == $kategori['id']) ? 'var(--primary)' : 'transparent' ?>; padding-left: 15px; transition: 0.3s;"
                            onmouseover="this.style.color='var(--primary)'"
                            onmouseout="this.style.color='<?= ($kat['id'] == $kategori['id']) ? 'var(--primary)' : 'var(--gray)' ?>'">
                            <?= temiz($kat['name']) ?>
                            <span
                                style="font-size: 11px; float: right; background: #f1f5f9; padding: 2px 8px; border-radius: 10px; color: var(--gray);">
                                <?= $kat['urun_sayisi'] ?? 0 ?>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php hook_calistir('kategori_sidebar'); ?>
    </aside>

    <!-- Ürün Listesi -->
    <main class="shoptimizer-main-content">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: #fff; border: 1px solid #f1f5f9; padding: 15px 25px; border-radius: 15px;">
            <span style="font-size: 14px; color: var(--gray); font-weight: 600;">Toplam <strong
                    style="color: var(--dark);">
                    <?= count($urunler) ?>
                </strong> ürün bulundu.</span>
            <div class="sorting">
                <select
                    style="border: none; background: none; font-weight: 800; font-size: 14px; color: var(--dark); cursor: pointer; outline: none;">
                    <option>Sıralama: En Yeniler</option>
                    <option>Fiyat: Düşükten Yükseğe</option>
                    <option>Fiyat: Yüksekten Düşüğe</option>
                    <option>En Çok Satanlar</option>
                </select>
            </div>
        </div>

        <div class="shoptimizer-products-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px;">
            <?php if (!empty($urunler)): ?>
                <?php foreach ($urunler as $u): ?>
                    <?php gorunum('urun-kart', ['u' => $u]); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div
                    style="grid-column: 1 / -1; text-align: center; padding: 100px 0; background: #f8fafc; border-radius: 30px;">
                    <i class="fas fa-box-open" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h3 style="font-weight: 800; color: #64748b;">Bu kategoride henüz ürün bulunmuyor.</h3>
                    <a href="<?= BASE_URL ?>" style="color: var(--primary); font-weight: 700;">Alışverişe Başla</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php gorunum('alt'); ?>