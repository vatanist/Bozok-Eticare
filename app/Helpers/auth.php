<?php
/**
 * Bozok E-Ticaret — Kimlik Doğrulama Yardımcıları
 *
 * Kullanıcı oturumu kontrolü, yetkilendirme, brute-force koruması.
 *
 * @package App\Helpers
 */

// ── Çekirdek Fonksiyonlar ──

function giris_yapilmis_mi()
{
    return isset($_SESSION['user_id']);
}

function yonetici_mi()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function giris_zorunlu()
{
    if (!giris_yapilmis_mi()) {
        mesaj('hata', 'Bu sayfayı görmek için giriş yapmalısınız.', 'error');
        git('/hesabim/giris');
    }
}

function yonetici_zorunlu()
{
    if (!yonetici_mi()) {
        git('/admin/login.php');
    }
}

function aktif_kullanici()
{
    if (!giris_yapilmis_mi())
        return null;
    return Database::fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

/**
 * Brute-force Koruması: Giriş denemelerini kontrol eder.
 */
function checkLoginRateLimit($key, $maxAttempts = 5, $lockoutTime = 15)
{
    $attempts = $_SESSION['login_attempts'][$key] ?? 0;
    $lockTime = $_SESSION['login_lockout'][$key] ?? 0;

    if ($lockTime > time()) {
        $remaining = ceil(($lockTime - time()) / 60);
        return ['locked' => true, 'wait_minutes' => $remaining];
    }

    if ($attempts >= $maxAttempts) {
        unset($_SESSION['login_attempts'][$key]);
        return ['locked' => false, 'remaining' => $maxAttempts];
    }

    return ['locked' => false, 'remaining' => $maxAttempts - $attempts];
}

/**
 * Hatalı giriş denemesini kaydeder.
 */
function recordFailedLogin($key)
{
    $_SESSION['login_attempts'][$key] = ($_SESSION['login_attempts'][$key] ?? 0) + 1;
    if ($_SESSION['login_attempts'][$key] >= 5) {
        $_SESSION['login_lockout'][$key] = time() + (15 * 60);
    }
}

/**
 * Başarılı giriş sonrası limitleri temizler.
 */
function clearLoginRateLimit($key)
{
    unset($_SESSION['login_attempts'][$key]);
    unset($_SESSION['login_lockout'][$key]);
}

// ── Uyumluluk Alias'ları ──

/** @deprecated v2.0'da kaldırılacak. giris_yapilmis_mi() kullanın. */
function isLoggedIn()
{
    return giris_yapilmis_mi();
}

/** @deprecated v2.0'da kaldırılacak. yonetici_mi() kullanın. */
function isAdmin()
{
    return yonetici_mi();
}

/** @deprecated v2.0'da kaldırılacak. aktif_kullanici() kullanın. */
function currentUser()
{
    return aktif_kullanici();
}

/** @deprecated v2.0'da kaldırılacak. yonetici_zorunlu() kullanın. */
function requireAdmin()
{
    return yonetici_zorunlu();
}

/** @deprecated v2.0'da kaldırılacak. giris_zorunlu() kullanın. */
function requireLogin()
{
    return giris_zorunlu();
}
