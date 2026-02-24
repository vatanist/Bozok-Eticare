<?php gorunum('ust', ['sayfa_basligi' => 'Giriş Yap / Kayıt Ol']); ?>

<div class="auth-page" style="padding:50px 0;">
    <div
        style="max-width:900px; margin:0 auto; background:#fff; border-radius:20px; border:1px solid #e5e7eb; display:grid; grid-template-columns: 1fr 1fr; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.05);">

        <!-- Giriş Yap -->
        <div style="padding:50px; border-right:1px solid #f3f4f6;">
            <h2 style="font-weight:800; margin-bottom:10px; color:var(--dark);">Hoş Geldin!</h2>
            <p style="color:var(--gray); margin-bottom:30px;">Hesabına giriş yaparak siparişlerini yönetebilirsin.</p>

            <?php mesaj_goster('giris_hata'); ?>

            <form action="<?= BASE_URL ?>/client/login.php" method="POST">
                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">E-Posta
                        Adresi</label>
                    <input type="email" name="email" placeholder="ornek@mail.com"
                        style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none; transition:border-color 0.3s;"
                        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e5e7eb'"
                        required>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Şifre</label>
                    <input type="password" name="password" placeholder="••••••••"
                        style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none; transition:border-color 0.3s;"
                        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e5e7eb'"
                        required>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                    <label
                        style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.85rem; color:var(--gray);">
                        <input type="checkbox" name="remember" style="width:16px; height:16px;"> Beni Hatırla
                    </label>
                    <a href="#" style="font-size:0.85rem; color:var(--primary); font-weight:600;">Şifremi Unuttum</a>
                </div>

                <button type="submit" class="buton"
                    style="display:block; width:100%; background:var(--primary); color:#fff; border:none; padding:15px; border-radius:30px; font-weight:800; font-size:1rem; cursor:pointer; box-shadow:0 4px 15px rgba(26,86,219,0.2);">Giriş
                    Yap <i class="fas fa-sign-in-alt"></i></button>
            </form>
        </div>

        <!-- Kayıt Ol -->
        <div style="padding:50px; background:var(--gray-50);">
            <h2 style="font-weight:800; margin-bottom:10px; color:var(--dark);">Yeni Misin?</h2>
            <p style="color:var(--gray); margin-bottom:30px;">Hemen kayıt ol, avantajlı dünyamıza katıl!</p>

            <form action="<?= BASE_URL ?>/client/register.php" method="POST">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:20px;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; font-size:0.85rem;">Ad</label>
                        <input type="text" name="first_name"
                            style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                            required>
                    </div>
                    <div>
                        <label
                            style="display:block; margin-bottom:5px; font-weight:600; font-size:0.85rem;">Soyad</label>
                        <input type="text" name="last_name"
                            style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                            required>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; font-size:0.85rem;">E-Posta</label>
                    <input type="email" name="email"
                        style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                        required>
                </div>

                <div style="margin-bottom:25px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600; font-size:0.85rem;">Şifre
                        Oluştur</label>
                    <input type="password" name="password"
                        style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                        required>
                </div>

                <button type="submit" class="buton"
                    style="display:block; width:100%; background:var(--dark); color:#fff; border:none; padding:15px; border-radius:30px; font-weight:800; font-size:1rem; cursor:pointer;">Hesap
                    Oluştur <i class="fas fa-user-plus"></i></button>
            </form>

            <div style="margin-top:30px; text-align:center;">
                <p style="font-size:0.8rem; color:var(--gray-light);">Kayıt olarak Kullanıcı Sözleşmesi'ni kabul etmiş
                    olursunuz.</p>
            </div>
        </div>

    </div>
</div>

<?php gorunum('alt'); ?>
