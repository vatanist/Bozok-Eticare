<?php gorunum('ust', ['sayfa_basligi' => 'Favorilerim']); ?>

<div class="wishlist-page" style="padding:40px 0;">
    <div class="client-layout" style="display:grid; grid-template-columns: 280px 1fr; gap:40px;">

        <!-- Sidebar -->
        <div class="client-sidebar-container">
            <?php gorunum('hesap-sidebar', ['aktif_sayfa' => 'wishlist', 'kullanici' => $kullanici]); ?>
        </div>

        <!-- İçerik -->
        <div class="client-main-content">
            <div style="margin-bottom:30px;">
                <h1 style="font-weight:800; margin-bottom:10px;">Favorilerim</h1>
                <p style="color:var(--gray);">Beğendiğin ve daha sonra satın almak istediğin ürünler burada listelenir.
                </p>
            </div>

            <?php mesaj_goster('wishlist'); ?>

            <?php if (empty($wishlist)): ?>
                <div
                    style="text-align:center; padding:80px 20px; background:#fff; border:1px solid #e5e7eb; border-radius:16px;">
                    <div
                        style="width:80px; height:80px; background:var(--danger)15; color:var(--danger); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:2rem;">
                        <i class="far fa-heart"></i>
                    </div>
                    <h3 style="font-weight:800; margin-bottom:10px;">Favori listeniz henüz boş</h3>
                    <p style="color:var(--gray); margin-bottom:25px;">Hemen ürünlere göz atıp beğendiklerini listenize
                        ekleyebilirsiniz.</p>
                    <a href="<?= BASE_URL ?>/urunler.php" class="buton"
                        style="background:var(--primary); color:#fff; padding:12px 30px; border-radius:30px; text-decoration:none; font-weight:700;">Ürünleri
                        Keşfet</a>
                </div>
            <?php else: ?>
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:25px;">
                    <?php foreach ($wishlist as $item): ?>
                        <div style="position:relative; group">
                            <?php
                            // Ürün kartı verisini hazırla
                            $urun = [
                                'id' => $item['product_id'],
                                'name' => $item['name'],
                                'slug' => $item['slug'],
                                'price' => $item['price'],
                                'discount_price' => $item['discount_price'],
                                'image' => $item['image'],
                                'stock' => $item['stock']
                            ];
                            gorunum('urun-kart', ['urun' => $urun]);
                            ?>

                            <!-- Favorilerden Çıkar Butonu (Üstte) -->
                            <form action="<?= BASE_URL ?>/client/wishlist.php" method="POST"
                                style="position:absolute; top:10px; right:10px; z-index:5;">
                                <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                                <button type="submit"
                                    style="width:32px; height:32px; border-radius:50%; background:#fff; border:1px solid #eee; color:var(--danger); cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 5px rgba(0,0,0,0.1); transition:0.3s;"
                                    onmouseover="this.style.background='var(--danger)'; this.style.color='#fff';"
                                    onmouseout="this.style.background='#fff'; this.style.color='var(--danger)';"
                                    title="Favorilerden Çıkar">
                                    <i class="fas fa-trash-alt" style="font-size:0.8rem;"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php gorunum('alt'); ?>
