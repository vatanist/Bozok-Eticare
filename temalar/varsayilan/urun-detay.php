<?php gorunum('ust', ['sayfa_basligi' => $urun['name']]); ?>

<div class="product-detail">
    <div class="product-top" style="display:grid; grid-template-columns: 1fr 1fr; gap:40px; margin-bottom:50px;">

        <!-- Ürün Galerisi -->
        <div class="product-gallery">
            <div class="main-image"
                style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; margin-bottom:15px;">
                <img id="mainImage" src="<?= resim_linki($urun['image']) ?>" alt="<?= temiz($urun['name']) ?>"
                    style="width:100%; height:500px; object-fit:contain;">
            </div>
            <?php
            $gallery = json_decode($urun['images'] ?? '[]', true);
            if (!empty($gallery)): ?>
                <div class="thumb-grid" style="display:grid; grid-template-columns: repeat(5, 1fr); gap:10px;">
                    <div class="thumb active" onclick="changeImage('<?= resim_linki($urun['image']) ?>', this)"
                        style="border:2px solid var(--primary); border-radius:8px; cursor:pointer; overflow:hidden;">
                        <img src="<?= resim_linki($urun['image']) ?>" style="width:100%; height:80px; object-fit:cover;">
                    </div>
                    <?php foreach ($gallery as $img): ?>
                        <div class="thumb" onclick="changeImage('<?= resim_linki($img) ?>', this)"
                            style="border:2px solid transparent; border-radius:8px; cursor:pointer; overflow:hidden; transition:0.3s;">
                            <img src="<?= resim_linki($img) ?>" style="width:100%; height:80px; object-fit:cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Ürün Bilgileri -->
        <div class="product-info">
            <nav style="font-size:0.85rem; color:var(--gray); margin-bottom:10px;">
                <a href="<?= BASE_URL ?>" style="color:var(--gray);">Anasayfa</a> /
                <a href="<?= BASE_URL ?>/kategori/<?= link_yap($urun['kategori_adi']) ?>"
                    style="color:var(--gray);"><?= temiz($urun['kategori_adi']) ?></a>
            </nav>
            <h1 style="font-size:2rem; font-weight:800; color:var(--dark); margin-bottom:15px;">
                <?= temiz($urun['name']) ?>
            </h1>

            <div class="product-meta"
                style="display:flex; gap:20px; margin-bottom:25px; font-size:0.9rem; color:var(--gray-light);">
                <span><i class="fas fa-barcode"></i> SKU: <?= temiz($urun['sku'] ?: 'V-' . $urun['id']) ?></span>
                <span><i class="fas fa-eye"></i> <?= $urun['view_count'] ?> Görüntülenme</span>
                <span><i class="fas fa-check-circle" style="color:var(--success);"></i> Stokta Var</span>
            </div>

            <div class="price-section"
                style="background:var(--gray-50); padding:25px; border-radius:12px; margin-bottom:30px;">
                <?php if ($urun['discount_price'] > 0): ?>
                    <div style="text-decoration:line-through; color:var(--gray); font-size:1.1rem; margin-bottom:5px;">
                        <?= para_yaz($urun['price']) ?>
                    </div>
                    <div id="livePrice" style="font-size:2.5rem; font-weight:900; color:var(--primary);">
                        <?= para_yaz($urun['discount_price']) ?>
                    </div>
                    <div
                        style="background:var(--danger); color:#fff; display:inline-block; padding:4px 12px; border-radius:20px; font-size:0.85rem; font-weight:700; margin-top:10px;">
                        %<?= round((($urun['price'] - $urun['discount_price']) / $urun['price']) * 100) ?> İNDİRİM</div>
                <?php else: ?>
                    <div id="livePrice" style="font-size:2.5rem; font-weight:900; color:var(--dark);">
                        <?= para_yaz($urun['price']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <form action="<?= BASE_URL ?>/sepet.php?islem=ekle" method="POST" class="add-to-cart-form">
                <input type="hidden" name="urun_id" value="<?= $urun['id'] ?>">

                <?php if (!empty($secenekler)): ?>
                    <div class="options" style="margin-bottom:25px;">
                        <?php foreach ($secenekler as $sid => $s): ?>
                            <div class="option-row" style="margin-bottom:15px;">
                                <label
                                    style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:8px;"><?= temiz($s['ad']) ?></label>
                                <div class="option-selector" style="display:flex; gap:10px; flex-wrap:wrap;">
                                    <?php foreach ($s['degerler'] as $vid => $v): ?>
                                        <label style="cursor:pointer;">
                                            <input type="radio" name="secenek[<?= $sid ?>]" value="<?= $v['option_value_id'] ?>"
                                                style="display:none;" required>
                                            <div class="opt-box"
                                                style="padding:10px 20px; border:1px solid #e5e7eb; border-radius:8px; font-size:0.9rem; transition:0.3s;">
                                                <?= temiz($v['deger_adi']) ?>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="actions" style="display:flex; gap:15px; align-items:center;">
                    <div class="qty-selector"
                        style="display:flex; align-items:center; border:1px solid #e5e7eb; border-radius:30px; padding:5px 15px; background:#fff;">
                        <button type="button" onclick="qtyChange(-1)"
                            style="background:none; border:none; color:var(--gray); width:30px; height:30px; font-size:1.2rem;">-</button>
                        <input type="number" name="adet" id="qtyInput" value="1" min="1"
                            style="width:50px; text-align:center; border:none; font-weight:700; -moz-appearance: textfield;">
                        <button type="button" onclick="qtyChange(1)"
                            style="background:none; border:none; color:var(--gray); width:30px; height:30px; font-size:1.2rem;">+</button>
                    </div>
                    <button type="submit" class="buton"
                        style="flex:1; background:var(--primary); color:#fff; border:none; padding:15px; border-radius:30px; font-weight:700; font-size:1.1rem; display:flex; align-items:center; justify-content:center; gap:10px;">
                        <i class="fas fa-shopping-basket"></i> SEPETE EKLE
                    </button>
                    <button type="button" class="buton"
                        style="background:var(--gray-50); color:var(--dark); width:50px; height:50px; border-radius:50%; border:1px solid #e5e7eb; display:flex; align-items:center; justify-content:center;">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </form>

            <div class="product-features"
                style="margin-top:30px; display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div style="font-size:0.85rem; color:var(--dark-600);"><i class="fas fa-undo"
                        style="color:var(--primary); margin-right:8px;"></i> 14 Gün Kolay İade</div>
                <div style="font-size:0.85rem; color:var(--dark-600);"><i class="fas fa-shield-alt"
                        style="color:var(--primary); margin-right:8px;"></i> 2 Yıl Garanti</div>
                <div style="font-size:0.85rem; color:var(--dark-600);"><i class="fas fa-shipping-fast"
                        style="color:var(--primary); margin-right:8px;"></i> Ücretsiz Kargo</div>
                <div style="font-size:0.85rem; color:var(--dark-600);"><i class="fas fa-award"
                        style="color:var(--primary); margin-right:8px;"></i> Orijinal Ürün</div>
            </div>
        </div>
    </div>

    <!-- Tab Section -->
    <div class="product-tabs" style="margin-bottom:60px;">
        <div class="tab-header" style="border-bottom:1px solid #e5e7eb; display:flex; gap:30px; margin-bottom:30px;">
            <div class="tab-btn active"
                style="padding:15px 0; font-weight:700; color:var(--primary); border-bottom:2px solid var(--primary); cursor:pointer;">
                Ürün Açıklaması</div>
            <div class="tab-btn" style="padding:15px 0; font-weight:600; color:var(--gray); cursor:pointer;">Teknik
                Özellikler</div>
            <div class="tab-btn" style="padding:15px 0; font-weight:600; color:var(--gray); cursor:pointer;">Yorumlar
                (0)</div>
        </div>
        <div class="tab-content" style="line-height:1.8; color:var(--dark-600); font-size:1.05rem;">
            <?= $urun['description'] ?: 'Bu ürün ile ilgili detaylı açıklama bulunmuyor.' ?>
        </div>
    </div>

    <!-- Benzer Ürünler -->
    <?php if (!empty($benzerler)): ?>
        <section style="margin-bottom:60px;">
            <h2 style="font-weight:800; font-size:1.5rem; margin-bottom:25px;">Benzer Ürünler</h2>
            <div class="product-grid" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:20px;">
                <?php foreach ($benzerler as $b): ?>
                    <div class="product-card"
                        style="background:#fff; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; transition:all 0.3s;">
                        <a href="<?= BASE_URL ?>/urun/<?= $b['slug'] ?>" style="display:block;">
                            <img src="<?= resim_linki($b['image']) ?>" alt="<?= temiz($b['name']) ?>"
                                style="width:100%; height:200px; object-fit:cover;">
                        </a>
                        <div style="padding:15px;">
                            <h3 style="font-size:0.95rem; margin:0 0 10px 0; height:40px; overflow:hidden;">
                                <a href="<?= BASE_URL ?>/urun/<?= $b['slug'] ?>"
                                    style="color:var(--dark);"><?= temiz($b['name']) ?></a>
                            </h3>
                            <div style="font-size:1.1rem; font-weight:800; color:var(--primary);">
                                <?= para_yaz($b['discount_price'] ?: $b['price']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
    function changeImage(src, thumb) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.thumb').forEach(t => t.style.borderColor = 'transparent');
        thumb.style.borderColor = 'var(--primary)';
    }

    function qtyChange(amt) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value) + amt;
        if (val < 1) val = 1;
        input.value = val;
    }

    // Seçenek seçimi efekti
    document.querySelectorAll('.option-selector input').forEach(input => {
        input.addEventListener('change', function () {
            const row = this.closest('.option-row');
            row.querySelectorAll('.opt-box').forEach(b => {
                b.style.borderColor = '#e5e7eb';
                b.style.background = '#fff';
                b.style.color = 'var(--dark)';
            });
            const box = this.nextElementSibling;
            box.style.borderColor = 'var(--primary)';
            box.style.background = 'var(--primary-light)';
            box.style.color = 'var(--primary)';
        });
    });
</script>

<style>
    /* Chrome, Safari, Edge, Opera - OK butonlarını gizleme */
    #qtyInput::-webkit-outer-spin-button,
    #qtyInput::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

<?php gorunum('alt'); ?>