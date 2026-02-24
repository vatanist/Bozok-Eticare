<?php gorunum('ust', ['sayfa_basligi' => $kategori['name']]); ?>

<div class="category-page" style="display:grid; grid-template-columns: 280px 1fr; gap:30px;">

    <!-- Filtre Paneli -->
    <aside class="sidebar">
        <div class="filter-box"
            style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; position:sticky; top:100px;">
            <h3
                style="font-size:1.1rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid #e5e7eb; padding-bottom:10px;">
                Filtrele</h3>

            <!-- Alt Kategoriler -->
            <?php if (!empty($alt_kategoriler)): ?>
                <div class="filter-group" style="margin-bottom:25px;">
                    <label style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:12px;">Alt
                        Kategoriler</label>
                    <ul style="list-style:none; padding:0;">
                        <?php foreach ($alt_kategoriler as $ak): ?>
                            <li style="margin-bottom:8px;">
                                <a href="<?= BASE_URL ?>/kategori.php?slug=<?= $ak['slug'] ?>"
                                    style="color:var(--dark-500); font-size:0.9rem; display:flex; justify-content:space-between;">
                                    <?= temiz($ak['name']) ?>
                                    <span style="color:var(--gray-light); font-size:0.8rem;">(
                                        <?= $ak['product_count'] ?>)
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Fiyat Aralığı -->
            <div class="filter-group">
                <label style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:12px;">Fiyat
                    Aralığı</label>
                <form action="" method="GET" style="display:flex; gap:10px; align-items:center;">
                    <input type="hidden" name="slug" value="<?= $kategori['slug'] ?>">
                    <input type="number" name="min" placeholder="En Az"
                        style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:8px; font-size:0.85rem;">
                    <span style="color:var(--gray-light);">-</span>
                    <input type="number" name="max" placeholder="En Çok"
                        style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:8px; font-size:0.85rem;">
                    <button type="submit"
                        style="background:var(--primary); color:#fff; border:none; width:35px; height:35px; border-radius:8px; cursor:pointer;"><i
                            class="fas fa-chevron-right"></i></button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Ürün Listesi -->
    <section class="main-content">
        <div class="category-header"
            style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; background:#fff; padding:15px 25px; border-radius:12px; border:1px solid #e5e7eb;">
            <div>
                <h1 style="font-size:1.4rem; font-weight:800; margin:0;">
                    <?= temiz($kategori['name']) ?>
                </h1>
                <small style="color:var(--gray-light);">
                    <?= count($urunler) ?> ürün listeleniyor
                </small>
            </div>
            <div class="sort-box">
                <select
                    style="padding:8px 15px; border:1px solid #e5e7eb; border-radius:8px; font-size:0.9rem; color:var(--dark-600); outline:none;">
                    <option value="new">En Yeniler</option>
                    <option value="price_asc">Fiyat (Düşükten Yükseğe)</option>
                    <option value="price_desc">Fiyat (Yüksekten Düşüğe)</option>
                    <option value="name_asc">İsim (A-Z)</option>
                </select>
            </div>
        </div>

        <?php if (empty($urunler)): ?>
            <div style="background:#fff; padding:100px; border-radius:12px; border:1px solid #e5e7eb; text-align:center;">
                <i class="fas fa-search fa-4x" style="color:var(--gray-light); margin-bottom:20px;"></i>
                <h2 style="color:var(--dark-600);">Üzgünüz, ürün bulunamadı.</h2>
                <p style="color:var(--gray);">Bu kategoride henüz ürün eklenmemiş veya kriterlerinize uygun sonuç
                    bulunmuyor.</p>
                <a href="<?= BASE_URL ?>" class="buton"
                    style="display:inline-block; margin-top:20px; background:var(--primary); color:#fff; padding:12px 30px; border-radius:30px;">Alışverişe
                    Başla</a>
            </div>
        <?php else: ?>
            <div class="product-grid"
                style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:20px;">
                <?php foreach ($urunler as $u): ?>
                    <div class="product-card"
                        style="background:#fff; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; transition:all 0.3s; position:relative;">
                        <a href="<?= BASE_URL ?>/urun-detay.php?slug=<?= $u['slug'] ?>" style="display:block;">
                            <img src="<?= resim_linki($u['image']) ?>" alt="<?= temiz($u['name']) ?>"
                                style="width:100%; height:200px; object-fit:cover;">
                        </a>
                        <div style="padding:15px;">
                            <h3 style="font-size:0.95rem; margin:0 0 10px 0; height:40px; overflow:hidden;">
                                <a href="<?= BASE_URL ?>/urun-detay.php?slug=<?= $u['slug'] ?>" style="color:var(--dark);">
                                    <?= temiz($u['name']) ?>
                                </a>
                            </h3>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div style="font-size:1.1rem; font-weight:800; color:var(--primary);">
                                    <?= para_yaz($u['discount_price'] ?: $u['price']) ?>
                                </div>
                                <a href="<?= BASE_URL ?>/sepet.php?ekle=<?= $u['id'] ?>" style="color:var(--gray);"><i
                                        class="fas fa-shopping-basket"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php gorunum('alt'); ?>
