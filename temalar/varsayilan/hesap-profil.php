<?php gorunum('ust', ['sayfa_basligi' => 'Profil Ayarları']); ?>

<div class="client-profile" style="padding:40px 0;">
    <div class="client-layout" style="display:grid; grid-template-columns: 280px 1fr; gap:40px;">

        <!-- Sidebar -->
        <div class="client-sidebar-container">
            <?php gorunum('hesap-sidebar', ['aktif_sayfa' => 'profile', 'kullanici' => $kullanici]); ?>
        </div>

        <!-- İçerik -->
        <div class="client-main-content">
            <div style="margin-bottom:30px;">
                <h1 style="font-weight:800; margin-bottom:10px;">Profil Ayarları</h1>
                <p style="color:var(--gray);">Kişisel bilgilerini ve güvenlik ayarlarını buradan yönetebilirsin.</p>
            </div>

            <?php mesaj_goster('profile'); ?>

            <div style="display:grid; gap:30px;">

                <!-- Kişisel Bilgiler -->
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:30px;">
                    <h3 style="font-weight:800; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-user-circle" style="color:var(--primary);"></i> Kişisel Bilgiler
                    </h3>
                    <form action="<?= BASE_URL ?>/client/profile.php" method="POST">
                        <input type="hidden" name="action" value="profile">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                            <div>
                                <label
                                    style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Adınız</label>
                                <input type="text" name="first_name" value="<?= temiz($kullanici['first_name']) ?>"
                                    style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                    required>
                            </div>
                            <div>
                                <label
                                    style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Soyadınız</label>
                                <input type="text" name="last_name" value="<?= temiz($kullanici['last_name']) ?>"
                                    style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                    required>
                            </div>
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">E-Posta
                                Adresi</label>
                            <input type="email" name="email" value="<?= temiz($kullanici['email']) ?>"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                        <div style="margin-bottom:25px;">
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Telefon
                                Numarası</label>
                            <input type="tel" name="phone" value="<?= temiz($kullanici['phone']) ?>"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;">
                        </div>
                        <button type="submit" class="buton"
                            style="background:var(--primary); color:#fff; border:none; padding:12px 30px; border-radius:30px; font-weight:700; cursor:pointer;">Bilgileri
                            Güncelle</button>
                    </form>
                </div>

                <!-- Şifre Değiştir -->
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:30px;">
                    <h3 style="font-weight:800; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-lock" style="color:var(--primary);"></i> Şifre Değiştir
                    </h3>
                    <form action="<?= BASE_URL ?>/client/profile.php" method="POST">
                        <input type="hidden" name="action" value="password">
                        <div style="margin-bottom:20px;">
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Mevcut
                                Şifre</label>
                            <input type="password" name="current_password"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:25px;">
                            <div>
                                <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Yeni
                                    Şifre</label>
                                <input type="password" name="new_password"
                                    style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                    minlength="6" required>
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Yeni
                                    Şifre Tekrar</label>
                                <input type="password" name="new_password_confirm"
                                    style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                    required>
                            </div>
                        </div>
                        <button type="submit" class="buton"
                            style="background:var(--dark); color:#fff; border:none; padding:12px 30px; border-radius:30px; font-weight:700; cursor:pointer;">Şifreyi
                            Değiştir</button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<?php gorunum('alt'); ?>
