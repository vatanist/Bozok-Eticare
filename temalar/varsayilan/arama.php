<?php gorunum('ust', ['sayfa_basligi' => ($sorgu ? 'Arama: ' . $sorgu : 'Arama')]); ?>

<div class="search-page" style="padding:40px 0;">
    <div class="container">

        <!-- Breadcrumb -->
        <div style="margin-bottom:30px;">
            <ul
                style="display:flex; align-items:center; list-style:none; padding:0; margin:0; gap:10px; font-size:0.9rem; color:var(--gray);">
                <li><a href="<?= BASE_URL ?>/" style="color:var(--gray); text-decoration:none;">Ana Sayfa</a></li>
                <li><i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></li>
                <li style="color:var(--dark); font-weight:700;">Arama
                    <?= $sorgu ? ': ' . temiz($sorgu) : '' ?>
                </li>
            </ul>
        </div>

        <div style="margin-bottom:40px;">
            <?php if ($sorgu): ?>
                <h1 style="font-weight:800; margin-bottom:10px;">"
                    <?= temiz($sorgu) ?>" için sonuçlar
                </h1>
                <p style="color:var(--gray);">
                    <?= count($urunler) ?> ürün listeleniyor.
                </p>
            <?php else: ?>
                <h1 style="font-weight:800; margin-bottom:10px;">Ürün Arayın</h1>
            <?php endif; ?>
        </div>

        <?php if (empty($urunler)): ?>
            <div
                style="text-align:center; padding:100px 20px; background:#fff; border:1px solid #e5e7eb; border-radius:24px;">
                <div
                    style="width:100px; height:100px; background:var(--gray-50); color:var(--gray-light); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 25px; font-size:2.5rem;">
                    <i class="fas fa-search-minus"></i>
                </div>
                <h3 style="font-weight:800; margin-bottom:15px; color:var(--dark);">Henüz bir sonuç bulamadık</h3>
                <p style="color:var(--gray); margin-bottom:30px; max-width:400px; margin-left:auto; margin-right:auto;">
                    <?= $sorgu ? '"' . temiz($sorgu) . '" kelimesine uygun ürün bulamadık. Lütfen farklı anahtar kelimeler deneyin.' : 'Arama kutusuna ürün adı veya kategorisi yazarak aramaya başlayabilirsiniz.' ?>
                </p>
                <a href="<?= BASE_URL ?>/urunler.php" class="buton"
                    style="background:var(--primary); color:#fff; padding:14px 40px; border-radius:30px; text-decoration:none; font-weight:700; display:inline-block;">Tüm
                    Ürünleri Gör</a>
            </div>
        <?php else: ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:30px;">
                <?php foreach ($urunler as $u): ?>
                    <?php gorunum('urun-kart', ['urun' => $u]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php gorunum('alt'); ?>
