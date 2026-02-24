<?php
$initials = strtoupper(mb_substr($kullanici['first_name'] ?? 'U', 0, 1));
?>
<aside
    style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; position:sticky; top:100px;">
    <!-- Kullanıcı Özeti -->
    <div style="padding:30px; border-bottom:1px solid #f3f4f6; text-align:center;">
        <div
            style="width:80px; height:80px; background:var(--primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:800; margin:0 auto 15px; box-shadow:0 4px 15px rgba(26,86,219,0.3);">
            <?= $initials ?>
        </div>
        <h4 style="margin:0; font-weight:800; color:var(--dark);">
            <?= temiz($kullanici['first_name'] . ' ' . $kullanici['last_name']) ?>
        </h4>
        <p style="margin:5px 0 0; font-size:0.85rem; color:var(--gray);">
            <?= temiz($kullanici['email']) ?>
        </p>
    </div>

    <!-- Menü -->
    <ul style="list-style:none; padding:0; margin:0;">
        <?php
        $menu = [
            'dashboard' => ['link' => '/client/', 'icon' => 'fas fa-home', 'label' => 'Genel Bakış'],
            'orders' => ['link' => '/client/orders.php', 'icon' => 'fas fa-shopping-bag', 'label' => 'Siparişlerim'],
            'profile' => ['link' => '/client/profile.php', 'icon' => 'fas fa-user-cog', 'label' => 'Profil Ayarları'],
            'addresses' => ['link' => '/client/addresses.php', 'icon' => 'fas fa-map-marker-alt', 'label' => 'Adreslerim'],
            'wishlist' => ['link' => '/client/wishlist.php', 'icon' => 'fas fa-heart', 'label' => 'Favorilerim'],
            'logout' => ['link' => '/client/logout.php', 'icon' => 'fas fa-sign-out-alt', 'label' => 'Çıkış Yap', 'danger' => true]
        ];

        foreach ($menu as $key => $m):
            $is_active = ($aktif_sayfa === $key);
            $color = isset($m['danger']) ? 'var(--danger)' : ($is_active ? 'var(--primary)' : 'var(--dark)');
            $bg = $is_active ? 'var(--primary-light)' : 'transparent';
            ?>
            <li>
                <a href="<?= BASE_URL . $m['link'] ?>"
                    style="display:flex; align-items:center; gap:15px; padding:15px 25px; text-decoration:none; color:<?= $color ?>; font-weight:<?= $is_active ? '700' : '600' ?>; background:<?= $bg ?>; border-left:4px solid <?= $is_active ? 'var(--primary)' : 'transparent' ?>; transition:0.3s;">
                    <i class="<?= $m['icon'] ?>" style="width:20px;"></i>
                    <span>
                        <?= $m['label'] ?>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>
