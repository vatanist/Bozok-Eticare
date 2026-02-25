<?php
gorunum('ust', ['sayfa_basligi' => 'V-Commerce - Premium Alışveriş Deneyimi']);
$aktif_kullanici = aktif_kullanici();
?>

<div class="home-layout" style="display:flex; gap:30px; margin-bottom:60px;">

    <!-- Sol Menü: Kategoriler (Sidebar) -->
    <aside class="home-sidebar" style="width:280px; flex-shrink:0;">
        <div
            style="background:#fff; border:1px solid #e5e7eb; border-radius:20px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.03);">
            <div
                style="background:var(--primary); color:#fff; padding:18px 25px; font-weight:800; display:flex; align-items:center; gap:12px;">
                <i class="fas fa-bars"></i> KATEGORİLER
            </div>
            <nav>
                <ul style="list-style:none; padding:10px 0; margin:0;">
                    <?php foreach ($kategoriler as $kat): ?>
                        <li style="border-bottom:1px solid #f9fafb;">
                            <a href="<?= BASE_URL ?>/kategori/<?= $kat['slug'] ?>"
                                style="display:flex; align-items:center; justify-content:space-between; padding:12px 25px; color:var(--dark); text-decoration:none; font-weight:600; font-size:0.9rem; transition:0.2s;"
                                onmouseover="this.style.background='var(--primary-light)'; this.style.color='var(--primary)';"
                                onmouseout="this.style.background='transparent'; this.style.color='var(--dark)';">
                                <span><?= temiz($kat['name']) ?></span>
                                <i class="fas fa-chevron-right" style="font-size:0.7rem; opacity:0.3;"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div style="padding:15px 25px;">
                    <a href="<?= url('urunler') ?>"
                        style="color:var(--primary); font-size:0.85rem; font-weight:700; text-decoration:none;">Tüm
                        Kategoriler <i class="fas fa-arrow-right" style="margin-left:5px;"></i></a>
                </div>
            </nav>
        </div>

        <!-- Küçük Mini Banner (Sidebar Altı) -->
        <div
            style="margin-top:30px; background:linear-gradient(135deg, #f59e0b, #d97706); border-radius:20px; padding:30px; color:#fff; position:relative; overflow:hidden;">
            <div style="position:relative; z-index:2;">
                <h4 style="font-weight:900; margin-bottom:10px;">%20 İNDİRİM</h4>
                <p style="font-size:0.8rem; margin-bottom:20px; opacity:0.9;">İlk alışverişine özel "WELCOME20" kodunu
                    kullan!</p>
                <a href="<?= url('urunler') ?>"
                    style="background:#fff; color:var(--warning); padding:8px 20px; border-radius:30px; text-decoration:none; font-size:0.8rem; font-weight:800;">FIRSATI
                    YAKALA</a>
            </div>
            <i class="fas fa-percentage"
                style="position:absolute; bottom:-20px; right:-20px; font-size:100px; opacity:0.1; transform:rotate(-15deg);"></i>
        </div>
    </aside>

    <!-- Ana İçerik Alanı -->
    <main class="home-content" style="flex:1; min-width:0;">

        <!-- Premium Hero Slider -->
        <section class="home-slider"
            style="height:450px; border-radius:30px; overflow:hidden; position:relative; box-shadow:0 20px 50px rgba(0,0,0,0.1); margin-bottom:40px;">
            <div class="home-slide active"
                style="background-image: url('<?= BASE_URL ?>/assets/images/hero-banner.png'); background-size:cover; background-position:center; height:100%; display:flex; align-items:center; padding:0 60px;">
                <div
                    style="max-width:550px; background:rgba(255,255,255,0.1); backdrop-filter:blur(15px); padding:45px; border-radius:30px; border:1px solid rgba(255,255,255,0.2); color:#fff;">
                    <div
                        style="background:var(--secondary); display:inline-block; padding:5px 15px; border-radius:20px; font-size:0.75rem; font-weight:800; margin-bottom:20px; letter-spacing:1px;">
                        LİMİTLİ STOK</div>
                    <h2 style="font-size:3rem; font-weight:900; margin-bottom:15px; line-height:1.1;">Geleceğin
                        Teknolojisi, Bugün Evinizde.</h2>
                    <p style="font-size:1.1rem; opacity:0.9; margin-bottom:30px; line-height:1.6;">V-Commerce ile seçili
                        elektronik ürünlerde vadesiz 12 taksit ve aynı gün kargo avantajlarını kaçırmayın.</p>
                    <a href="<?= url('urunler') ?>" class="buton"
                        style="background:#fff; color:var(--dark); padding:15px 40px; border-radius:40px; text-decoration:none; font-weight:800; font-size:1rem; box-shadow:0 10px 25px rgba(0,0,0,0.2); transition:0.3s;"
                        onmouseover="this.style.transform='translateY(-3px)';"
                        onmouseout="this.style.transform='translateY(0)';">Keşfetmeye Başla</a>
                </div>
            </div>
        </section>

        <!-- Avantajlar Bandı -->
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:20px; margin-bottom:50px;">
            <div
                style="background:#fff; padding:25px; border-radius:24px; display:flex; align-items:center; gap:20px; border:1px solid #f1f1f1;">
                <div
                    style="width:50px; height:50px; background:var(--primary-light); color:var(--primary); border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <div>
                    <h5 style="margin:0; font-weight:800;">Ücretsiz Kargo</h5>
                    <p style="margin:0; font-size:0.8rem; color:var(--gray);">1.000 TL ve üzeri her yerde</p>
                </div>
            </div>
            <div
                style="background:#fff; padding:25px; border-radius:24px; display:flex; align-items:center; gap:20px; border:1px solid #f1f1f1;">
                <div
                    style="width:50px; height:50px; background:var(--accent-light); color:var(--accent); border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                    <i class="fas fa-shield-heart"></i>
                </div>
                <div>
                    <h5 style="margin:0; font-weight:800;">Güvenli Alışveriş</h5>
                    <p style="margin:0; font-size:0.8rem; color:var(--gray);">256-bit SSL şifreleme</p>
                </div>
            </div>
            <div
                style="background:#fff; padding:25px; border-radius:24px; display:flex; align-items:center; gap:20px; border:1px solid #f1f1f1;">
                <div
                    style="width:50px; height:50px; background:var(--secondary-dark)15; color:var(--secondary-dark); border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                    <i class="fas fa-arrows-rotate"></i>
                </div>
                <div>
                    <h5 style="margin:0; font-weight:800;">Kolay İade</h5>
                    <p style="margin:0; font-size:0.8rem; color:var(--gray);">14 gün koşulsuz iade</p>
                </div>
            </div>
        </div>

        <!-- Öne Çıkan Ürünler (Grid) -->
        <section style="margin-bottom:60px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:30px;">
                <div>
                    <h2 style="font-weight:900; font-size:1.8rem; margin:0; color:var(--dark);">Sizin İçin Seçtiklerimiz
                    </h2>
                    <p style="color:var(--gray); margin:5px 0 0;">En çok tercih edilen popüler ürünler</p>
                </div>
                <a href="<?= url('urunler') ?>"
                    style="color:var(--primary); font-weight:700; text-decoration:none; padding:10px 20px; border:2px solid var(--primary-light); border-radius:15px; transition:0.3s;"
                    onmouseover="this.style.background='var(--primary-light)';"
                    onmouseout="this.style.background='transparent';">Tümünü Gör</a>
            </div>

            <div class="product-grid"
                style="display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:25px;">
                <?php foreach ($one_cikanlar as $u): ?>
                    <?php gorunum('urun-kart', ['urun' => $u]); ?>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Orta Banner: Kategorilere Göre Keşfet -->
        <section style="margin-bottom:60px;">
            <h2 style="font-weight:900; font-size:1.5rem; margin-bottom:25px; text-align:center;">Kategorileri Keşfet
            </h2>
            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:20px;">
                <?php foreach (array_slice($kategoriler, 0, 4) as $index => $k):
                    $renkler = ['#6366f1', '#ec4899', '#f59e0b', '#10b981'];
                    ?>
                    <a href="<?= BASE_URL ?>/kategori/<?= $k['slug'] ?>"
                        style="background:<?= $renkler[$index % 4] ?>; color:#fff; border-radius:24px; padding:30px; text-decoration:none; text-align:center; transition:0.3s; box-shadow:0 10px 20px <?= $renkler[$index % 4] ?>33;"
                        onmouseover="this.style.transform='scale(1.03)';" onmouseout="this.style.transform='scale(1)';">
                        <i class="fas fa-shapes" style="font-size:2rem; margin-bottom:15px; display:block;"></i>
                        <span style="font-weight:800; font-size:1rem; display:block;"><?= temiz($k['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- En Yeniler Section -->
        <section style="margin-bottom:60px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:30px;">
                <div>
                    <h2 style="font-weight:900; font-size:1.8rem; margin:0; color:var(--dark);">En Yeni Gelenler</h2>
                    <p style="color:var(--gray); margin:5px 0 0;">Mağazamıza henüz giriş yapan taze ürünler</p>
                </div>
            </div>
            <div class="product-grid"
                style="display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:25px;">
                <?php foreach ($en_yeniler as $u): ?>
                    <?php gorunum('urun-kart', ['urun' => $u]); ?>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Markalar Bandı -->
        <section
            style="background:#fff; border-radius:30px; padding:40px; border:1px solid #f1f1f1; text-align:center;">
            <p
                style="color:var(--gray); font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:2px; margin-bottom:30px;">
                DÜNYA MARKALARI V-COMMERCE'DE</p>
            <div
                style="display:flex; justify-content:space-around; align-items:center; opacity:0.5; filter:grayscale(1);">
                <i class="fab fa-apple" style="font-size:2.5rem;"></i>
                <i class="fab fa-google" style="font-size:2rem;"></i>
                <i class="fab fa-amazon" style="font-size:2rem;"></i>
                <i class="fab fa-microsoft" style="font-size:2rem;"></i>
                <i class="fab fa-samsung" style="font-size:2.5rem;"></i>
            </div>
        </section>

    </main>
</div>

<?php gorunum('alt'); ?>